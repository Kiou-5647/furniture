<?php

namespace App\Actions\Hr;

use App\Models\Hr\Designer;
use App\Models\Hr\DesignerAvailability;
use Illuminate\Http\UploadedFile;

class UpdateDesignerAction
{
    public function execute(Designer $designer, array $data, ?UploadedFile $avatarFile = null): Designer
    {
        $availabilities = $data['availabilities'] ?? [];
        unset($data['availabilities']);
        unset($data['avatar']);

        $designer->update($data);

        if ($avatarFile instanceof UploadedFile) {
            $designer->clearMediaCollection('avatar');
            $designer->addMedia($avatarFile)->toMediaCollection('avatar');
        }

        // Sync availabilities: delete all and recreate
        $designer->availabilities()->delete();

        foreach ($availabilities as $slot) {
            DesignerAvailability::create([
                'designer_id' => $designer->id,
                'day_of_week' => $slot['day_of_week'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }

        return $designer->fresh(['user', 'employee', 'availabilities']);
    }
}
