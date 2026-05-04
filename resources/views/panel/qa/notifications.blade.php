@extends('layouts.qa')

@section('title', 'Notifikasi')

@section('content')
    <div class="mt-2 mb-6">
        <x-pm.button-link
            href="{{ route('qa.testing-queue') }}"
            variant="secondary"
            size="sm"
            aria-label="Kembali"
            title="Kembali"
        >
            <x-icon name="arrow-left" class="h-4 w-4" />
            <span>Kembali</span>
        </x-pm.button-link>
    </div>

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="ui-text-caption" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li>
                        <a href="{{ route('qa.testing-queue') }}" class="ui-text-link">
                            QA
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    <li class="ui-text-caption" aria-current="page">Notifikasi</li>
                </ol>
            </nav>

            <h1 class="mt-1 ui-text-page-title">Notifikasi</h1>
            <p class="mt-2 ui-text-body">Lihat pembaruan bug untuk validasi QA secara ringkas dan terstruktur.</p>
        </div>
    </div>

    <section class="space-y-5">
        <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Daftar Notifikasi QA</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    {{ $unreadCount > 0 ? $unreadCount . ' notifikasi menunggu dibaca' : 'Semua notifikasi sudah dibaca' }}
                </p>
            </div>

            <form method="POST" action="{{ route('qa.notifications.markAllRead') }}">
                @csrf
                <x-pm.button type="submit" variant="secondary" color="blue" size="sm" :disabled="$unreadCount === 0">
                    Tandai semua dibaca
                </x-pm.button>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            @forelse($notifications as $n)
                @php
                    $isUnread = ! $n->is_read;

                    [$label, $badgeStatus] = match($n->type) {
                        'BugReported' => ['Dilaporkan', 'Reported'],
                        'BugAssigned' => ['Ditugaskan', 'Assigned'],
                        'BugStatusChanged' => ['Status Diperbarui', 'Testing'],
                        'BugCommented' => ['Dikomentari', 'In Progress'],
                        'BugRejected' => ['Dikembalikan', 'Rejected'],
                        'BugDone' => ['Diselesaikan', 'Resolved'],

                        // Backward compatibility for older notification type values.
                        'Reported' => ['Dilaporkan', 'Reported'],
                        'Assigned' => ['Ditugaskan', 'Assigned'],
                        'In Progress' => ['Dalam Pengerjaan', 'In Progress'],
                        'Testing' => ['Pengujian', 'Testing'],
                        'Resolved' => ['Diselesaikan', 'Resolved'],
                        'Closed' => ['Ditutup', 'Closed'],
                        'Rejected' => ['Dikembalikan', 'Rejected'],
                        default => ['Pembaruan', 'Pembaruan'],
                    };

                    $messageText = strtr((string) $n->message, [
                        'Report baru masuk:' => 'Bug dilaporkan:',
                        'Reported' => 'Dilaporkan',
                        'Assigned' => 'Ditugaskan',
                        'In Progress' => 'Dalam Pengerjaan',
                        'Testing' => 'Pengujian',
                        'Resolved' => 'Diselesaikan',
                        'Closed' => 'Ditutup',
                        'Rejected' => 'Dikembalikan',
                    ]);

                @endphp

                <article class="border-b border-slate-200 px-4 py-4 last:border-b-0">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-slate-900' : 'bg-slate-300' }}"></span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                @if($n->bug)
                                    <span class="rounded border border-slate-200 px-2 py-0.5 font-mono text-slate-600">#{{ $n->related_id }}</span>
                                @endif

                                <x-pm.status-badge :status="$badgeStatus" variant="soft">{{ $label }}</x-pm.status-badge>
                            </div>

                            <p class="mt-2 text-sm {{ $isUnread ? 'font-semibold text-slate-900' : 'font-medium text-slate-800' }}">
                                {{ $messageText }}
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                <span>{{ $n->created_at?->diffForHumans() }}</span>

                                @if($n->bug?->project)
                                    <span>Proyek: {{ $n->bug->project->name }}</span>
                                @endif

                                @if($n->bug?->priority)
                                    <span>Prioritas: {{ $n->bug->priority->level }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex w-full flex-wrap items-center justify-end gap-2">
                        <form method="POST" action="{{ route('qa.notifications.read', $n) }}">
                            @csrf
                            <x-pm.button type="submit" size="sm" :variant="$isUnread ? 'primary' : 'secondary'">
                                {{ $isUnread ? 'Buka & tandai dibaca' : 'Lihat detail' }}
                            </x-pm.button>
                        </form>

                        <form method="POST" action="{{ route('qa.notifications.destroy', $n) }}">
                            @csrf
                            @method('DELETE')
                            <x-pm.button
                                type="submit"
                                variant="secondary"
                                color="red"
                                size="sm"
                                class="w-9 px-0"
                                title="Hapus notifikasi"
                                aria-label="Hapus notifikasi"
                                onclick="return confirm('Hapus notifikasi ini?')"
                            >
                                <x-icon name="trash-2" class="h-4 w-4" />
                            </x-pm.button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm font-medium text-slate-700">Belum ada notifikasi</p>
                    <p class="mt-1 text-xs text-slate-500">Notifikasi QA akan muncul saat ada bug masuk testing atau perubahan status.</p>
                    <x-pm.button-link href="{{ route('qa.notifications') }}" variant="secondary" size="sm" class="mt-3">
                        Refresh
                    </x-pm.button-link>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$notifications" />
    </section>
@endsection
