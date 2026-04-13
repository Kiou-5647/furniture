<?php

namespace App\Builders\Booking;

use App\Enums\DesignServiceType;
use Illuminate\Database\Eloquent\Builder;

class DesignServiceBuilder extends Builder
{
    public function consultations(): self
    {
        return $this->where('type', DesignServiceType::Consultation);
    }

    public function customBuilds(): self
    {
        return $this->where('type', DesignServiceType::CustomBuild);
    }

    public function byType(string $type): self
    {
        return $this->where('type', $type);
    }

    public function scheduleBlocking(): self
    {
        return $this->where('is_schedule_blocking', true);
    }

    public function nonScheduleBlocking(): self
    {
        return $this->where('is_schedule_blocking', false);
    }

    public function search(string $search): self
    {
        return $this->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
        });
    }
}
