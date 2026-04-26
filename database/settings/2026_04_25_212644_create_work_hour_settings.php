<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('work_hours.morning_start', 8);
        $this->migrator->add('work_hours.morning_end', 12);
        $this->migrator->add('work_hours.afternoon_start', 13);
        $this->migrator->add('work_hours.afternoon_end', 18);
    }
};
