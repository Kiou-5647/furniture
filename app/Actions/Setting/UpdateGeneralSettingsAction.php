<?php

namespace App\Actions\Setting;

use App\Data\Setting\UpdateGeneralSettingsData;
use App\Settings\GeneralSettings;

class UpdateGeneralSettingsAction
{
    public function execute(UpdateGeneralSettingsData $data): void
    {
        $settings = app(GeneralSettings::class);

        $settings->site_name = $data->site_name;
        $settings->freeship_threshold = $data->freeship_threshold;
        $settings->default_warranty = $data->default_warranty;

        $settings->save();
    }
}
