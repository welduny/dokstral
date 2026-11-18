<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('loans')
            ->where('status', 'pending')
            ->whereNotNull('confirmation_token')
            ->orderBy('id')
            ->chunkById(100, function ($loans): void {
                foreach ($loans as $loan) {
                    for ($attempt = 0; $attempt < 1000; $attempt++) {
                        $token = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
                        $tokenExists = DB::table('loans')
                            ->where('confirmation_token', $token)
                            ->where('id', '!=', $loan->id)
                            ->exists();

                        if (!$tokenExists) {
                            DB::table('loans')->where('id', $loan->id)->update([
                                'confirmation_token' => $token,
                            ]);
                            continue 2;
                        }
                    }

                    throw new RuntimeException('Unable to allocate a unique five-digit loan token.');
                }
            });
    }

    public function down(): void
    {
        // Existing five-digit tokens remain valid after rollback.
    }
};