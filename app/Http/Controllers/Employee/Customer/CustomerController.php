<?php

namespace App\Http\Controllers\Employee\Customer;

use App\Actions\Customer\CreateCustomerAction;
use App\Actions\Customer\UpsertCustomerAction;
use App\Data\Customer\CreateCustomerData;
use App\Data\Customer\CustomerFilterData;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\Employee\Customer\CustomerResource;
use App\Models\Booking\Booking;
use App\Models\Customer\Customer;
use App\Models\Sales\Order;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController
{
    public function __construct(
        private CustomerService $service
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Customer::class)) {
            return back()->with('error', 'Bạn không có quyền xem danh sách khách hàng.');
        }

        $filter = CustomerFilterData::fromRequest($request);

        return Inertia::render('employee/customers/Index', [
            'customers' => CustomerResource::collection(
                $this->service->getFiltered($filter)
            ),
            'filters' => $filter,
        ]);
    }

    public function show(Customer $customer, Request $request)
    {
        if (!Gate::allows('view', $customer)) {
            return back()->with('error', 'Bạn không có quyền xem chi tiết khách hàng này.');
        }

        // Ensure the customer is loaded with user and orders as expected by the service
        $customer = $this->service->getById($customer->id);

        $orders = $customer->orders()
            ->latest()
            ->paginate($request->query('per_page', 15))
            ->through(fn(Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status->label(),
                'created_at' => $order->created_at?->format('d/m/Y H:i'),
                'can_view' => Gate::allows('view', $order)
            ]);

        $bookings = $customer->bookings()
            ->latest()
            ->paginate($request->query('per_page', 15))
            ->through(fn(Booking $booking) => [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'total_price' => $booking->total_price,
                'status' => $booking->status->label(),
                'start_at' => $booking->start_at?->format('d/m/Y H:i'),
                'can_view' => Gate::allows('view', $booking),
            ]);

        return Inertia::render('employee/customers/Show', [
            'customer' => new CustomerResource($customer),
            'orders' => $orders,
            'bookings' => $bookings,
        ]);
    }

    public function deactivate(Customer $customer)
    {
        if (!Gate::allows('delete', $customer)) {
            return back()->with('error', 'Bạn không có quyền vô hiệu hóa khách hàng này.');
        }

        if (!$customer->user) {
            return back()->with('success', 'Khách hàng này không có tài khoản');
        }

        $customer->user->update(['is_active' => false]);

        return back()->with('success', 'Đã vô hiệu hóa tài khoản khách hàng.');
    }

    public function store(StoreCustomerRequest $request, UpsertCustomerAction $action)
    {
        if (!Gate::allows('create', Customer::class)) {
            return back()->with('error', 'Bạn không có quyền tạo khách hàng mới.');
        }

        $data = CreateCustomerData::fromRequest($request);
        try {
            $action->execute($data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã tạo khách hàng mới thành công.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, UpsertCustomerAction $action)
    {
        if (!Gate::allows('update', $customer)) {
            return back()->with('error', 'Bạn không có quyền cập nhật thông tin khách hàng này.');
        }

        $data = CreateCustomerData::fromRequest($request);
        try {
            $action->execute($data, $customer);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật thông tin khách hàng thành công.');
    }
}
