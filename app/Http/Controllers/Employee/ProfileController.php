<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $employee = $this->currentEmployee();

        return view('employee.profile', compact('employee'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ($request->filled('password')) {
            if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password lama tidak sesuai.',
                    ], 422);
                }

                throw ValidationException::withMessages([
                    'current_password' => 'Password lama tidak sesuai.',
                ]);
            }

            $user->password = $request->string('password')->toString();
        }

        $user->name = $request->string('name')->toString();
        $user->email = $request->string('email')->toString();
        $user->save();

        return redirect()
            ->route('employee.profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    private function currentEmployee(): Employee
    {
        return auth()->user()->employee;
    }
}