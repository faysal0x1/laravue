<?php

namespace App\Repositories\ApiSetting;

use App\Models\ApiSetting;
use App\Repositories\BaseRepository;

class ApiSettingRepository extends BaseRepository implements ApiSettingRepositoryInterface
{
    public function __construct(ApiSetting $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'name', 'is_active', 'created_at'];
    }

    public function activeByName(string $name): ?ApiSetting
    {
        return $this->model->newQuery()
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }
}
