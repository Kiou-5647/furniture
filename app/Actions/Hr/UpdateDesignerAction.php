<?php

namespace App\Actions\Hr;

use App\Models\Hr\Designer;
use App\Services\Hr\DesignerService;
use Illuminate\Http\UploadedFile;

class UpdateDesignerAction
{
    public function __construct(
        private DesignerService $designerService
    ) {}

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

        if (!empty($availabilities)) {
            $this->designerService->setWeeklySlots($designer, $availabilities);
        }

        return $designer->fresh(['user', 'employee', 'availabilitySlots']);
    }
}
