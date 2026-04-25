<?php

namespace App\Http\Controllers\Employee\Setting;

use App\Actions\Setting\UpdateGeneralSettingsAction;
use App\Data\Setting\UpdateGeneralSettingsData;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralSettingsController
{
    public function index(GeneralSettings $settings)
    {
        return Inertia::render('employee/settings/General', [
            'settings' => [
                'site_name' => $settings->site_name,
                'freeship_threshold' => $settings->freeship_threshold,
                'default_warranty' => $settings->default_warranty,
            ],
            'labels' => GeneralSettings::labels(),
        ]);
    }

    public function update(Request $request, UpdateGeneralSettingsAction $action)
    {
        $action->execute(UpdateGeneralSettingsData::fromRequest($request));

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
