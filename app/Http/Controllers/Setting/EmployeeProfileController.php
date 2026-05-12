<?php

namespace App\Http\Controllers\Setting;

use App\Actions\Hr\UpdateEmployeeAction;
use App\Models\Hr\Department;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeProfileController
{
    /**
     * Show the employee's professional profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = Auth::user();
        $employee = $user->employee;

        return Inertia::render('employee/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'employee' => $employee,
            'user' => $user,
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
            'avatar' => $employee->getFirstMediaUrl('avatar')
        ]);
    }

    /**
     * Update the employee's professional and user information.
     */
    public function update(Request $request, UpdateEmployeeAction $updateEmployee): RedirectResponse
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (!$employee) {
            return back()->withErrors(['error', 'Không đủ quyền hạn!']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:10240',
        ]);

        try {
            $updateEmployee->execute($employee, $validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }
}
