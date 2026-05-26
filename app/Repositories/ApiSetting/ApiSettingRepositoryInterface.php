<?php

namespace App\Repositories\ApiSetting;

use App\Models\ApiSetting;
use App\Repositories\Interfaces\RepositoryInterface;

interface ApiSettingRepositoryInterface extends RepositoryInterface
{
    public function activeByName(string $name): ?ApiSetting;
}
