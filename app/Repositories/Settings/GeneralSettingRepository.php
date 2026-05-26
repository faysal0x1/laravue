<?php

declare(strict_types=1);

namespace App\Repositories\Settings;

use App\Models\GeneralSetting;
use App\Repositories\BaseRepository;

class GeneralSettingRepository extends BaseRepository implements GeneralSettingRepositoryInterface
{
    public function __construct(GeneralSetting $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return [];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'created_at'];
    }
}
