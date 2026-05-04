<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriorityController extends Controller
{
    /**
     * Legacy controller (resource pages were unified into Management hub).
     * Keep endpoints safe by redirecting to the hub tab.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('pm.management', ['tab' => 'master']);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('pm.management', ['tab' => 'master']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'sla_hours' => ['required', 'integer', 'min:0'],
        ]);

        Priority::create($data);

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Priority berhasil dibuat.');
    }

    public function edit(Priority $priority): RedirectResponse
    {
        return redirect()->route('pm.management', ['tab' => 'master']);
    }

    public function update(Request $request, Priority $priority): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'sla_hours' => ['required', 'integer', 'min:0'],
        ]);

        $priority->update($data);

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Priority berhasil diupdate.');
    }

    public function destroy(Priority $priority): RedirectResponse
    {
        $priority->delete();

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Priority berhasil dihapus.');
    }
}
