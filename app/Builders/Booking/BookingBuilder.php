<?php

namespace App\Builders\Booking;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;

class BookingBuilder extends Builder
{
    public function pending(): self
    {
        return $this->where('status', BookingStatus::PendingDeposit);
    }

    public function pendingConfirmation(): self
    {
        return $this->where('status', BookingStatus::PendingConfirmation);
    }

    public function confirmed(): self
    {
        return $this->where('status', BookingStatus::Confirmed);
    }

    public function completed(): self
    {
        return $this->where('status', BookingStatus::Completed);
    }

    public function cancelled(): self
    {
        return $this->where('status', BookingStatus::Cancelled);
    }

    public function byStatus(BookingStatus $status): self
    {
        return $this->where('status', $status->value);
    }

    public function byDesigner(string $designerId): self
    {
        return $this->where('designer_id', $designerId);
    }

    public function byCustomer(string $customerId): self
    {
        return $this->where('customer_id', $customerId);
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            return $this->where(function ($query) use ($search) {
                $query->whereHas('customer', fn($q) => $q->where('name', 'ilike', "%{$search}%"))
                    ->orWhereHas('customer', fn($q) => $q->where('email', 'ilike', "%{$search}%"))
                    ->orWhereHas('designer', fn($q) => $q->where('full_name', 'ilike', "%{$search}%"));
            });
        });
    }
}
