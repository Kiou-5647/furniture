<?php

namespace App\Http\Controllers\Setting;

use App\Actions\Customer\UpdateCustomerAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerProfileController
{
    /**
     * Show the employee's professional profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = Auth::user();
        $customer = $user->customer;

        return Inertia::render('public/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'customer' => $customer,
            'user' => $user,
            'avatar' => $customer->getFirstMediaUrl('avatar')
        ]);
    }

    public function update(Request $request, UpdateCustomerAction $action)
    {
        $user = Auth::user();
        $customer = $user->customer;
        if (!$customer) {
            return back()->withErrors(['error', 'Không đủ quyền hạn!']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'province_code' => 'nullable|string|max:10',
            'ward_code' => 'nullable|string|max:10',
            'province_name' => 'nullable|string|max:255',
            'ward_name' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:10240',
            'avatar_url' => 'nullable|string',
        ]);
        try {
            $action->execute($customer, $validated);
        } catch (\Exception $e) {
            return back()->withErrors(['error', $e->getMessage()]);
        }

        return back()->with('success');
    }
}
