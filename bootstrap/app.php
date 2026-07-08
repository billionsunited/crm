<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckOfficeTiming::class,
        ]);

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user->hasRole('admin')) return route('leads.index');
            if ($user->can('dashboard-access')) return route('dashboard');
            if ($user->can('lead-view')) return route('leads.index');
            if ($user->can('campaign-view')) return route('campaign-leads.index');
            if ($user->can('invoice-section')) return route('invoices.index');
            if ($user->can('invoice-or-section')) return route('or-invoices.index');
            if ($user->can('vendor-section')) return route('vendor_leads.kyc');
            if ($user->can('client-po-access')) return route('manage_po.client_po.create');
            if ($user->can('vendor-po-access')) return route('manage_po.vendor_po');
            if ($user->can('email-template-view')) return route('email-templates.index');
            return route('profile.edit');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
