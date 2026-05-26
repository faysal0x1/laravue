<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Modules\Cache\Traits\HasModelCache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'application_name',
    'phone',
    'email',
    'address',
    'parcel_tracking_prefix',
    'invoice_prefix',
    'copyright',
    'primary_color',
    'text_color',
])]
class GeneralSetting extends Model implements HasMedia
{
    use HasModelCache;
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('light_logo')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
    }
}
