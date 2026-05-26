<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GeneralSettingRequest;
use App\Services\GeneralSettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSettingController extends Controller
{
    public function __construct(private readonly GeneralSettingService $service) {}

    public function edit(): Response
    {
        $setting = $this->service->first();

        return Inertia::render('settings/General', [
            'settings' => $this->service->toData($setting),
        ]);
    }

    public function update(GeneralSettingRequest $request): RedirectResponse
    {
        $this->service->update($request->validated());

        return success_route('general-settings.edit', 'General settings updated.');
    }
}
