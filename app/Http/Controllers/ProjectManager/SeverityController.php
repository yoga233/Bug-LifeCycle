<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Severity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeverityController extends Controller
{
    public function index(): View
    {
        $severities = Severity::query()
            ->orderBy('level')
            ->paginate(15);

        // Legacy controller kept for backward compatibility; current UI uses panel.project-manager.*
        return view('panel.project-manager.master-data.index', compact('severities'));
    }

    public function create(): View
    {
        return view('panel.project-manager.master-data.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        Severity::create($data);

        return redirect()
            ->route('pm.severities.index')
            ->with('status', 'Severity berhasil dibuat.');
    }

    public function edit(Severity $severity): View
    {
        return view('panel.project-manager.master-data.index', compact('severity'));
    }

    public function update(Request $request, Severity $severity): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $severity->update($data);

        return redirect()
            ->route('pm.severities.index')
            ->with('status', 'Severity berhasil diupdate.');
    }

    public function destroy(Severity $severity): RedirectResponse
    {
        $severity->delete();

        return redirect()
            ->route('pm.severities.index')
            ->with('status', 'Severity berhasil dihapus.');
    }
}
