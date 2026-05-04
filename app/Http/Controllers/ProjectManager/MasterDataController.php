<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Models\Severity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    /**
     * Shared subtle palette for badge presets.
     * We store chosen colors into DB (bg_color/text_color).
     */
    private function badgePreset(?string $key): ?array
    {
        $key = strtoupper(trim((string) $key));
        if ($key === '') {
            return null;
        }

        // 8 subtle enterprise presets.
        return match ($key) {
            'RED' => ['bg_color' => '#FEE2E2', 'text_color' => '#DC2626'],

            // Orange / Yellow / Green / Gray (new swatch keys)
            'ORANGE', 'AMBER' => ['bg_color' => '#FEF3C7', 'text_color' => '#D97706'],
            'YELLOW' => ['bg_color' => '#FEF9C3', 'text_color' => '#CA8A04'],
            'GREEN', 'EMERALD' => ['bg_color' => '#DCFCE7', 'text_color' => '#16A34A'],
            'GRAY', 'GREY', 'SLATE' => ['bg_color' => '#F3F4F6', 'text_color' => '#6B7280'],

            'BLUE' => ['bg_color' => '#DBEAFE', 'text_color' => '#2563EB'],
            'PURPLE' => ['bg_color' => '#EDE9FE', 'text_color' => '#7C3AED'],
            'PINK' => ['bg_color' => '#FCE7F3', 'text_color' => '#DB2777'],

            // Backward compatible old key (not part of new palette)
            'TEAL' => ['bg_color' => '#CCFBF1', 'text_color' => '#0F766E'],
            default => null,
        };
    }

    public function index(): View
    {
        $severities = Severity::query()
            ->orderBy('level')
            ->get();

        $priorities = Priority::query()
            ->orderBy('sla_hours')
            ->get();

        return view('panel.project-manager.master-data.index', compact('severities', 'priorities'));
    }

    public function storeSeverity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
            'color_preset' => ['nullable', 'string', 'max:20'],
        ]);

        $preset = $this->badgePreset($data['color_preset'] ?? null);
        unset($data['color_preset']);
        if ($preset) {
            $data = array_merge($data, $preset);
        }

        Severity::create($data);

        // Clear cached data
        Cache::forget('severities:all');

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Severity berhasil dibuat.');
    }

    public function updateSeverity(Request $request, Severity $severity): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
            'color_preset' => ['nullable', 'string', 'max:20'],
        ]);

        $preset = $this->badgePreset($data['color_preset'] ?? null);
        unset($data['color_preset']);
        if ($preset) {
            $data = array_merge($data, $preset);
        }

        $severity->update($data);

        // Clear cached data
        Cache::forget('severities:all');

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Severity berhasil diupdate.');
    }

    public function destroySeverity(Severity $severity): RedirectResponse
    {
        $severity->delete();

        // Clear cached data
        Cache::forget('severities:all');

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Severity berhasil dihapus.');
    }

    public function storePriority(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'sla_hours' => ['required', 'integer', 'min:0'],
            'color_preset' => ['nullable', 'string', 'max:20'],
        ]);

        $preset = $this->badgePreset($data['color_preset'] ?? null);
        unset($data['color_preset']);
        if ($preset) {
            $data = array_merge($data, $preset);
        }

        Priority::create($data);

        // Clear cached data
        Cache::forget('priorities:all');

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Priority berhasil dibuat.');
    }

    public function updatePriority(Request $request, Priority $priority): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'sla_hours' => ['required', 'integer', 'min:0'],
            'color_preset' => ['nullable', 'string', 'max:20'],
        ]);

        $preset = $this->badgePreset($data['color_preset'] ?? null);
        unset($data['color_preset']);
        if ($preset) {
            $data = array_merge($data, $preset);
        }

        $priority->update($data);

        // Clear cached data
        Cache::forget('priorities:all');

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Priority berhasil diupdate.');
    }

    public function destroyPriority(Priority $priority): RedirectResponse
    {
        $priority->delete();

        // Clear cached data
        Cache::forget('priorities:all');

        return redirect()
            ->route('pm.management', ['tab' => 'master'])
            ->with('status', 'Priority berhasil dihapus.');
    }
}
