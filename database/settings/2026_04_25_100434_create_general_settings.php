<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function ($blueprint) {
            $blueprint->add('site_name', 'Leo Depot');
            $blueprint->add('freeship_threshold', 2000000.0);
            $blueprint->add('default_warranty', 12);
        });
    }
};
