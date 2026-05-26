<?php

namespace App\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Http\Requests\SocialLinks\BulkDestroySocialLinkRequest;
use App\Http\Requests\SocialLinks\StoreSocialLinkRequest;
use App\Http\Requests\SocialLinks\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use App\Services\SocialLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialLinkController extends Controller
{
    public function __construct(private readonly SocialLinkService $service) {}

    private function renderIndex(Request $request): Response
    {
        return Inertia::render('SocialLinks/Index', [
            'socialLinks' => $this->service->paginateData($request),
            'filters' => QueryBuilderHelper::filters($request),
            'stats' => $this->service->stats(),
        ]);
    }

    public function index(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    public function table(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    public function store(StoreSocialLinkRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return success_route('social-link.index', 'Social link created successfully.');
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): RedirectResponse
    {
        $this->service->update($socialLink, $request->validated());

        return success_route('social-link.index', 'Social link updated successfully.');
    }

    public function toggleStatus(SocialLink $socialLink): RedirectResponse
    {
        $this->service->toggleStatus($socialLink);

        return success_route(
            'social-link.index',
            $socialLink->status ? 'Social link set to inactive.' : 'Social link set to active.',
        );
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $this->service->delete($socialLink);

        return success_route('social-link.index', 'Social link deleted successfully.');
    }

    public function bulkDestroy(BulkDestroySocialLinkRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->ids());

        return success_route('social-link.index', 'Selected social links deleted successfully.');
    }
}
