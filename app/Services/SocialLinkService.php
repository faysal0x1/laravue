<?php

namespace App\Services;

use App\DTOs\SocialLink\SocialLinkData;
use App\Models\SocialLink;
use App\Repositories\SocialLinks\SocialLinkRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class SocialLinkService
{
    public function __construct(
        private readonly SocialLinkRepositoryInterface $repository,
    ) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate(
            $request,
            ['id', 'name', 'icon', 'link', 'position', 'status', 'created_at'],
        );
    }

    public function paginateData(Request $request): LengthAwarePaginator
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $this->paginate($request);

        return $paginator->through(
            fn (SocialLink $socialLink) => SocialLinkData::fromModel($socialLink)->toArray(),
        );
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $total = $this->repository->query()->count();
        $active = $this->repository->query()->where('status', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
        ];
    }

    public function create(array $data): SocialLink
    {
        $socialLink = $this->repository->create($data);

        audit_log(
            action: 'social_links.created',
            modelType: SocialLink::class,
            modelId: $socialLink->id,
            newValues: $socialLink->only(['name', 'icon', 'link', 'position', 'status']),
        );

        return $socialLink;
    }

    public function update(SocialLink $socialLink, array $data): SocialLink
    {
        $before = $socialLink->only(['name', 'icon', 'link', 'position', 'status']);

        /** @var SocialLink $updated */
        $updated = $this->repository->update($data, $socialLink->id);

        audit_log(
            action: 'social_links.updated',
            modelType: SocialLink::class,
            modelId: $updated->id,
            oldValues: $before,
            newValues: $updated->only(['name', 'icon', 'link', 'position', 'status']),
        );

        return $updated;
    }

    public function toggleStatus(SocialLink $socialLink): SocialLink
    {
        return $this->update($socialLink, ['status' => ! $socialLink->status]);
    }

    public function delete(SocialLink $socialLink): bool
    {
        $before = $socialLink->only(['name', 'icon', 'link', 'position', 'status']);
        $deleted = $this->repository->delete($socialLink->id);

        if ($deleted) {
            audit_log(
                action: 'social_links.deleted',
                modelType: SocialLink::class,
                modelId: $socialLink->id,
                oldValues: $before,
            );
        }

        return $deleted;
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        $links = SocialLink::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name']);

        $deleted = $this->repository->bulkDelete($ids);

        if ($deleted > 0) {
            audit_log(
                action: 'social_links.bulk-deleted',
                modelType: SocialLink::class,
                oldValues: ['social_links' => $links->toArray()],
                newValues: ['deleted_count' => $deleted, 'ids' => $ids],
            );
        }

        return $deleted;
    }
}
