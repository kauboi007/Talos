<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\OfficialProfile;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'Login')->name('login.page');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $officials = OfficialProfile::query()->get();

        return inertia('Dashboard', [
            'metrics' => [
                'activeMissions' => ['count' => 0, 'delta' => 'Awaiting mission data'],
                'troopDeployment' => ['count' => $officials->count(), 'label' => 'Military Officials'],
                'threatLevel' => ['level' => 'UNAVAILABLE', 'sectorDetail' => 'Threat data pending'],
                'checkpointStatus' => ['percentage' => 0, 'label' => 'Awaiting checkpoint data'],
            ],
            'deploymentRecords' => [[
                'id' => 'officials',
                'sector' => 'COMMAND',
                'unitDesignation' => 'Military Officials',
                'soldierRecords' => 0,
                'officers' => $officials->count(),
                'status' => 'ACTIVE',
            ]],
            'telemetryNodes' => [],
            'threatEvents' => [],
        ]);
    })->name('dashboard');

    Route::get('/personnel', function () {
        $officials = OfficialProfile::query()->orderBy('rank_val', 'desc')->get();

        return inertia('Personnel', [
            'soldiers' => $officials->map(fn (OfficialProfile $official): array => [
                'id' => $official->service_number,
                'name' => $official->name,
                'rank' => strtoupper($official->rank),
                'rankFull' => ucfirst($official->rank),
                'unit' => $official->branch ?? '--',
                'specialization' => $official->specialization ?? '--',
                'deployStatus' => strtoupper($official->status) === 'ACTIVE' ? 'ACTIVE' : 'STANDBY',
                'lastCp' => '--',
                'avatarUrl' => '',
                'assignedUnit' => $official->designation ?? '--',
                'rationsLevel' => '--',
                'loadoutWgt' => '--',
                'hierarchy' => [],
            ])->values(),
        ]);
    })->name('personnel');

    Route::inertia('/routing', 'Routing')->name('routing');
    Route::inertia('/logs', 'Logs')->name('logs');
    Route::inertia('/settings', 'Settings')->name('settings');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
