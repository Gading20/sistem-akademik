<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $oldData = $user->toArray();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        if ($user->teacher) {
            $teacherData = collect($data)->only([
                'nip', 'nuptk', 'subject_id', 'join_date', 'contract_end_date',
            ])->filter()->toArray();

            if (!empty($teacherData)) {
                $user->teacher->update($teacherData);
            }
        }

        if ($user->student) {
            $studentData = collect($data)->only([
                'nis', 'nisn', 'birth_place', 'birth_date', 'gender', 'religion',
                'address', 'phone',
            ])->filter()->toArray();

            if (!empty($studentData)) {
                $user->student->update($studentData);
            }
        }

        $this->auditLogService->log(
            $user,
            'updated_profile',
            'Profile',
            $user->id,
            $oldData,
            $user->fresh()->toArray()
        );

        return redirect()->route('profile.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword(): View
    {
        return view('profile.index');
    }

    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Regenerasi sesi setelah kredensial berubah (anti session fixation),
        // sekaligus membatalkan token CSRF lama.
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        $this->auditLogService->log(
            $user,
            'changed_password',
            'Profile',
            $user->id
        );

        return redirect()->route('profile.index')
            ->with('success', 'Password berhasil diubah. Sesi lama Anda telah diperbarui.');
    }
}
