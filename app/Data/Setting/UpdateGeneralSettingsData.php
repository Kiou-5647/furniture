<?php

namespace App\Data\Setting;

class UpdateGeneralSettingsData
{
    public function __construct(
        public string $site_name,
        public float $freeship_threshold,
        public int $default_warranty,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            site_name: $request->input('site_name'),
            freeship_threshold: (float) $request->input('freeship_threshold'),
            default_warranty: (int) $request->input('default_warranty'),
        );
    }
}
