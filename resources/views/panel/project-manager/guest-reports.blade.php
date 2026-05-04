<?php

use Illuminate\View\View;
use App\Models\GuestBugReport;
use App\Models\GuestRateLimit;

/**
 * @var GuestBugReport[] $reports
 * @var array $stats
 * @var string $status
 */
?>

@php
    $statuses = [
        'pending' => 'Menunggu Tinjauan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'expired' => 'Kedaluwarsa',
        'all' => 'Semua',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                {{ __('Antrian Laporan Tamu') }}
            </h2>
            <div class="flex gap-2">
                <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-full">
                    {{ $stats['pending'] }} Menunggu
                </span>
                <span class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">
                    {{ $stats['approved'] }} Disetujui
                </span>
                <span class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full">
                    {{ $stats['rejected'] }} Ditolak
                </span>
                <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                    {{ $stats['today'] }} Hari Ini
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Tabs -->
            <div class="mb-6">
                <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                    @foreach($statuses as $key => $label)
                        <a href="{{ route('pm.guest-reports', ['status' => $key]) }}"
                           class="px-4 py-2 text-sm font-medium transition-colors
                                  {{ $status === $key
                                      ? 'text-blue-600 border-b-2 border-blue-600'
                                      : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Reports Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tiket
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pelapor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Project
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Severity
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu Laporan
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($reports as $report)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-medium text-blue-600">
                                        {{ $report->ticket }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $report->guest_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $report->guest_email }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        IP: {{ $report->ip_address }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $report->project?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->severity)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              style="background-color: {{ $report->severity->bg_color }}20; color: {{ $report->severity->text_color }}">
                                            {{ $report->severity->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($report->queue_status)
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Menunggu
                                            </span>
                                            @break
                                        @case('approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Disetujui
                                            </span>
                                            @break
                                        @case('rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Ditolak
                                            </span>
                                            @break
                                        @case('expired')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Kedaluwarsa
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $report->reported_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($report->queue_status === 'pending')
                                        <div class="flex gap-2 justify-end">
                                            <form method="POST" action="{{ route('pm.guest-reports.approve', $report) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                                    Setuju
                                                </button>
                                            </form>
                                            <button type="button"
                                                    onclick="openRejectModal({{ $report->id }})"
                                                    class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                Tolak
                                            </button>
                                        </div>
                                    @else
                                        <a href="{{ route('pm.guest-reports.show', $report) }}"
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Lihat
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada laporan tamu yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Tolak Laporan
                </h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-500">
                    &times;
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Alasan Penolakan
                    </label>
                    <textarea name="reject_reason" rows="3"
                              class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:border-gray-600"
                              placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="block_guest" value="1" class="rounded dark:bg-gray-700">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            Blokir tamu ini
                        </span>
                    </label>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeRejectModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openRejectModal(reportId) {
                const form = document.getElementById('rejectForm');
                form.action = `/project-manager/guest-reports/${reportId}/reject`;
                document.getElementById('rejectModal').classList.remove('hidden');
            }

            function closeRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
            }
        </script>
    @endpush
</x-app-layout>
