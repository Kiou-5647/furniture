<?php

namespace App\Actions\Hr;

use App\Models\Hr\Designer;
use Illuminate\Http\UploadedFile;

class UpdateDesignerAction
{
    public function execute(Designer $designer, array $data, ?UploadedFile $avatarFile = null): Designer
    {
        unset($data['availabilities']);
        unset($data['avatar']);

        $designer->update($data);

        if ($avatarFile instanceof UploadedFile) {
            $designer->clearMediaCollection('avatar');
            $designer->addMedia($avatarFile)->toMediaCollection('avatar');
        }

        return $designer->fresh(['user', 'employee', 'availabilitySlots']);
    }
}
