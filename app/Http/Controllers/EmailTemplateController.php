<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'attachment' => 'nullable|file|max:15360', // 15MB max
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            // Prefix with random string to avoid collisions, separated by '---'
            $fileName = \Illuminate\Support\Str::random(10) . '---' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
            $path = $file->storeAs('email_templates', $fileName, 'public');
            $validated['attachment'] = $path;
        }

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
            'attachment' => 'nullable|file|max:15360', // 15MB max
        ]);

        if ($request->hasFile('attachment')) {
            if ($emailTemplate->attachment && Storage::disk('public')->exists($emailTemplate->attachment)) {
                Storage::disk('public')->delete($emailTemplate->attachment);
            }
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $fileName = \Illuminate\Support\Str::random(10) . '---' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
            $path = $file->storeAs('email_templates', $fileName, 'public');
            $validated['attachment'] = $path;
        }

        $emailTemplate->update($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        abort_if(!auth()->user()->can('email-template-delete'), 403);
        
        if ($emailTemplate->attachment && Storage::disk('public')->exists($emailTemplate->attachment)) {
            Storage::disk('public')->delete($emailTemplate->attachment);
        }
        
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

        $templates = EmailTemplate::whereIn('id', $ids)->get();
        foreach ($templates as $t) {
            if ($t->attachment && Storage::disk('public')->exists($t->attachment)) {
                Storage::disk('public')->delete($t->attachment);
            }
        }

        EmailTemplate::whereIn('id', $ids)->delete();
        return back()->with('success', count($ids) . ' email templates deleted successfully.');
    }

    public function viewAttachment(EmailTemplate $emailTemplate)
    {
        abort_if(!auth()->user()->can('email-template-view'), 403);
        
        if (!$emailTemplate->attachment || !Storage::disk('public')->exists($emailTemplate->attachment)) {
            abort(404, 'Attachment not found.');
        }
        
        $path = Storage::disk('public')->path($emailTemplate->attachment);
        
        $fileName = basename($emailTemplate->attachment);
        $parts = explode('---', $fileName, 2);
        $niceName = count($parts) === 2 ? $parts[1] : $fileName;
        
        return response()->download($path, $niceName);
    }
}
