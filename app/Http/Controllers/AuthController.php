<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $defaultRoute = 'profile.edit';

            if ($user->hasRole('admin')) {
                $defaultRoute = 'leads.index';
            } elseif ($user->can('dashboard-access')) {
                $defaultRoute = 'dashboard';
            } elseif ($user->can('lead-view')) {
                $defaultRoute = 'leads.index';
            } elseif ($user->can('campaign-view')) {
                $defaultRoute = 'campaign-leads.index';
            } elseif ($user->can('invoice-section')) {
                $defaultRoute = 'invoices.index';
            } elseif ($user->can('invoice-or-section')) {
                $defaultRoute = 'or-invoices.index';
            } elseif ($user->can('vendor-section')) {
                $defaultRoute = 'vendor_leads.kyc';
            } elseif ($user->can('client-po-access')) {
                $defaultRoute = 'manage_po.client_po.create';
            } elseif ($user->can('vendor-po-access')) {
                $defaultRoute = 'manage_po.vendor_po';
            } elseif ($user->can('email-template-view')) {
                $defaultRoute = 'email-templates.index';
            }

            $intended = session()->pull('url.intended', route($defaultRoute));
            
            // If they intended to go to dashboard but don't have access, send them to their default route
            if (str_ends_with($intended, '/dashboard') && !($user->hasRole('admin') || $user->can('dashboard-access'))) {
                $intended = route($defaultRoute);
            }

            return redirect()->to($intended);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
