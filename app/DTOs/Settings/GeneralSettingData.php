<?php

namespace App\DTOs\Settings;

use App\Models\GeneralSetting;

class GeneralSettingData
{
    public function __construct(
        public ?string $application_name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $parcel_tracking_prefix,
        public ?string $invoice_prefix,
        public ?string $copyright,
        public ?string $primary_color,
        public ?string $text_color,
        public ?string $logo_url,
        public ?string $light_logo_url,
        public ?string $favicon_url,
    ) {}

    public static function fromModel(GeneralSetting $setting): self
    {
        return new self(
            application_name: $setting->application_name,
            phone: $setting->phone,
            email: $setting->email,
            address: $setting->address,
            parcel_tracking_prefix: $setting->parcel_tracking_prefix,
            invoice_prefix: $setting->invoice_prefix,
            copyright: $setting->copyright,
            primary_color: $setting->primary_color,
            text_color: $setting->text_color,
            logo_url: $setting->exists ? ($setting->getFirstMediaUrl('logo') ?: null) : null,
            light_logo_url: $setting->exists ? ($setting->getFirstMediaUrl('light_logo') ?: null) : null,
            favicon_url: $setting->exists ? ($setting->getFirstMediaUrl('favicon') ?: null) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'application_name' => $this->application_name ?? '',
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'address' => $this->address ?? '',
            'parcel_tracking_prefix' => $this->parcel_tracking_prefix ?? '',
            'invoice_prefix' => $this->invoice_prefix ?? '',
            'copyright' => $this->copyright ?? '',
            'primary_color' => $this->primary_color ?? '',
            'text_color' => $this->text_color ?? '',
            'logo_url' => $this->logo_url,
            'light_logo_url' => $this->light_logo_url,
            'favicon_url' => $this->favicon_url,
        ];
    }
}
