<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('general-setting.edit') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'application_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'parcel_tracking_prefix' => ['nullable', 'string', 'max:50'],
            'invoice_prefix' => ['nullable', 'string', 'max:50'],
            'copyright' => ['nullable', 'string', 'max:1000'],
            'logo' => Rule::when($this->hasFile('logo'), ['file', 'max:4096', 'mimes:jpg,jpeg,png,webp,svg,ico']),
            'light_logo' => Rule::when($this->hasFile('light_logo'), ['file', 'max:4096', 'mimes:jpg,jpeg,png,webp,svg,ico']),
            'favicon' => Rule::when($this->hasFile('favicon'), ['file', 'max:1024', 'mimes:jpg,jpeg,png,webp,svg,ico']),
            'primary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'text_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
        ];
    }
}
