<?php

namespace App\Http\Controllers\Employee\Customer;

use App\Data\Customer\CustomerFilterData;
use App\Http\Resources\Employee\Customer\CustomerResource;
use App\Models\Customer\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class CustomerController
{
    public function __construct(
        private CustomerService $service
    ) {}

    public function index(Request $request): Response
    {
        $filter = CustomerFilterData::fromRequest($request);

        return Inertia::render('employee/customers/Index', [
            'customers' => CustomerResource::collection(
                $this->service->getFiltered($filter)
            ),
            'filters' => $filter,
        ]);
    }

    public function show(Customer $customer): Response
    {
        // Ensure the customer is loaded with user and orders as expected by the service
        $customer = $this->service->getById($customer->id);

        return Inertia::render('employee/customers/Show', [
            'customer' => new CustomerResource($customer),
            'recentOrders' => $customer->user->orders->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function deactivate(Customer $customer)
    {
        $customer->user->update(['is_active' => false]);

        return back()->with('success', 'Đã vô hiệu hóa tài khoản khách hàng.');
    }
}
