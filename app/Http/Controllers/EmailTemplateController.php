<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->can('email-template-view'), 403);
        $templates = EmailTemplate::latest()->paginate(10);
        return view('email_templates.index', compact('templates'));
    }

    public function create()
    {
        abort_if(!auth()->user()->can('email-template-add'), 403);
        return view('email_templates.create');
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('email-template-add'), 403);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        EmailTemplate::create($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        abort_if(!auth()->user()->can('email-template-edit'), 403);
        return view('email_templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        abort_if(!auth()->user()->can('email-template-edit'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $emailTemplate->update($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        abort_if(!auth()->user()->can('email-template-delete'), 403);
        $emailTemplate->delete();
        return redirect()->route('email-templates.index')->with('success', 'Email template deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_if(!auth()->user()->can('email-template-delete'), 403);
        $ids = $request->ids;
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return back()->with('error', 'No email templates selected.');
        }

        EmailTemplate::whereIn('id', $ids)->delete();
        return back()->with('success', count($ids) . ' email templates deleted successfully.');
    }
}
