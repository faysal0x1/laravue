<?php

declare(strict_types=1);

namespace App\Repositories\SocialLinks;

use App\Models\SocialLink;
use App\Repositories\BaseRepository;

class SocialLinkRepository extends BaseRepository implements SocialLinkRepositoryInterface
{
    public function __construct(SocialLink $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'link'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'name', 'position', 'status', 'created_at'];
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->newQuery()->whereIn('id', $ids)->delete();
    }
}
