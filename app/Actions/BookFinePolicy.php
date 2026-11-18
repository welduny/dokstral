<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class BookFinePolicy
{
    public const LATE_FINE_PER_DAY = 2000;

    public const DAMAGE_PERCENTAGES = [
        'baik' => 0,
        'rusak_ringan' => 10,
        'rusak_berat' => 50,
        'hilang' => 100,
    ];

    public function damageFine(string $condition, ?int $bookPrice): int
    {
        if (! array_key_exists($condition, self::DAMAGE_PERCENTAGES)) {
            throw new InvalidArgumentException('Kondisi buku tidak dikenali.');
        }

        $percentage = self::DAMAGE_PERCENTAGES[$condition];
        if ($percentage === 0) {
            return 0;
        }

        if ($bookPrice === null) {
            throw ValidationException::withMessages([
                'harga' => 'Isi harga buku terlebih dahulu sebelum menghitung denda kerusakan.',
            ]);
        }

        return max(1, intdiv(($bookPrice * $percentage) + 50, 100));
    }
}
