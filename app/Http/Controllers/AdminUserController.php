<?php

namespace App\Http\Controllers;

use App\Models\AdminPasswordResetCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        abort_unless(in_array($status, ['all', 'pending', 'active', 'inactive'], true), 404);
        $memberQuery = User::query()->where('role', 'siswa');
        $counts = [
            'all' => (clone $memberQuery)->count(),
            'pending' => (clone $memberQuery)->where('status', 'pending')->count(),
            'active' => (clone $memberQuery)->where('status', 'active')->count(),
            'inactive' => (clone $memberQuery)->where('status', 'inactive')->count(),
        ];

        $users = (clone $memberQuery)
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'status', 'counts'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === 'siswa', 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'siswa', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:30', 'regex:/\A[0-9]+\z/', Rule::unique('users', 'nis')->ignore($user->id)],
            'kelas' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
        ]);
        $data['email'] = Str::lower(trim($data['email']));
        $data['nama'] = $data['name'];
        $data['nis'] = filled($data['nis'] ?? null) ? trim($data['nis']) : null;
        $data['kelas'] = filled($data['kelas'] ?? null) ? trim($data['kelas']) : null;

        DB::transaction(function () use ($user, $data): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $accountChanged = $lockedUser->email !== $data['email']
                || $lockedUser->status !== $data['status'];
            $lockedUser->update($data);

            if ($accountChanged) {
                $lockedUser->adminPasswordResetCode()->delete();
            }
        });

        return redirect()->route('admin.users.edit', $user)->with('success', 'Data akun berhasil diperbarui.');
    }

    public function approve(User $user): RedirectResponse
    {
        abort_unless($user->role === 'siswa', 404);

        $approved = DB::transaction(function () use ($user): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($lockedUser->role === 'siswa', 404);

            if ($lockedUser->status !== 'pending') {
                return false;
            }

            $lockedUser->update(['status' => 'active']);

            return true;
        });

        if (! $approved) {
            return back()->with('error', 'Pendaftaran ini sudah ditinjau sebelumnya.');
        }

        return back()->with('success', 'Akun berhasil disetujui. Pengguna sekarang bisa masuk.');
    }

    public function reject(User $user): RedirectResponse
    {
        abort_unless($user->role === 'siswa', 404);

        $rejected = DB::transaction(function () use ($user): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($lockedUser->role === 'siswa', 404);

            if ($lockedUser->status !== 'pending') {
                return false;
            }

            $lockedUser->update(['status' => 'inactive']);
            $lockedUser->adminPasswordResetCode()->delete();

            return true;
        });

        if (! $rejected) {
            return back()->with('error', 'Pendaftaran ini sudah ditinjau sebelumnya.');
        }

        return back()->with('success', 'Pendaftaran ditolak dan akun tidak dapat digunakan.');
    }

    public function issuePasswordResetCode(Request $request, User $user): RedirectResponse
    {
        $issuedCode = DB::transaction(function () use ($request, $user): ?array {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($lockedUser->role === 'siswa', 404);

            if ($lockedUser->status !== 'active') {
                return null;
            }

            $code = (string) random_int(10000000, 99999999);
            $expiresAt = now()->addMinutes(15);

            AdminPasswordResetCode::query()->updateOrCreate(
                ['user_id' => $lockedUser->id],
                [
                    'issued_by' => $request->user()->id,
                    'code_hash' => Hash::make($code),
                    'expires_at' => $expiresAt,
                    'used_at' => null,
                ],
            );

            return [
                'code' => $code,
                'expires_at' => $expiresAt->format('H:i'),
            ];
        });

        if ($issuedCode === null) {
            return back()->with('error', 'Aktifkan akun terlebih dahulu sebelum membuat kode reset password.');
        }

        return back()->with('issued_reset_code', $issuedCode);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === 'siswa', 404);

        $hasLoanHistory = DB::transaction(function () use ($user): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $hasHistory = DB::table('loans')->where('user_id', $lockedUser->id)->exists()
                || DB::table('borrowings')->where('user_id', $lockedUser->id)->exists();

            if (! $hasHistory) {
                $lockedUser->delete();
            }

            return $hasHistory;
        });

        if ($hasLoanHistory) {
            return back()->with('error', 'Akun tidak dapat dihapus karena memiliki riwayat peminjaman. Nonaktifkan akun untuk mencabut akses.');
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
