<?php

namespace App\Http\Controllers;

use App\Services\PulseVueMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PulseDashboardController extends Controller
{
    public function __invoke(Request $request, PulseVueMetricsService $metrics): Response
    {
        $period = $request->string('period')->toString();
        if (! in_array($period, ['1_hour', '6_hours', '24_hours', '7_days'], true)) {
            $period = '1_hour';
        }

        return Inertia::render('Pulse/Index', [
            'metrics' => $metrics->gather($period),
            'period' => $period,
        ]);
    }
}
