<?php

namespace App\Services;

use App\DTOs\Settings\GeneralSettingData;
use App\Models\GeneralSetting;
use App\Repositories\Settings\GeneralSettingRepositoryInterface;
use Illuminate\Http\UploadedFile;

class GeneralSettingService
{
    public function __construct(
        private readonly GeneralSettingRepositoryInterface $repository,

    ) {}

    public function first(): GeneralSetting
    {
        return $this->repository->query()->with('media')->firstOrNew();
    }

    /**
     * @return array<string, mixed>
     */
    public function toData(GeneralSetting $setting): array
    {
        return GeneralSettingData::fromModel($setting)->toArray();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): GeneralSetting
    {
        $imageFields = ['logo', 'light_logo', 'favicon'];
        $scalarData = array_diff_key($data, array_flip($imageFields));

        $existing = $this->repository->query()->first();

        if ($existing) {
            $before = $existing->only(array_keys($scalarData));

            /** @var GeneralSetting $setting */
            $setting = $this->repository->update($scalarData, $existing->id);

            audit_log(
                action: 'general-settings.updated',
                modelType: GeneralSetting::class,
                modelId: $setting->id,
                oldValues: $before,
                newValues: $setting->only(array_keys($scalarData)),
            );
        } else {
            /** @var GeneralSetting $setting */
            $setting = $this->repository->create($scalarData);

            audit_log(
                action: 'general-settings.created',
                modelType: GeneralSetting::class,
                modelId: $setting->id,
                newValues: $setting->only(array_keys($scalarData)),
            );
        }

        foreach ($imageFields as $collection) {
            if (isset($data[$collection]) && $data[$collection] instanceof UploadedFile) {
                $setting->addMedia($data[$collection])->toMediaCollection($collection);
            }
        }

        return $setting->load('media');
    }
}
