<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $batch?->batch_number ? 'Review Batch ' . $batch->batch_number : 'SnackCheck Review' }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <style>
        :root {
            color-scheme: dark;
            --page-bg: #020617;
            --page-panel: rgba(15, 23, 42, 0.72);
            --page-panel-strong: rgba(2, 6, 23, 0.92);
            --page-border: rgba(148, 163, 184, 0.14);
            --page-accent: #22d3ee;
            --page-accent-2: #38bdf8;
            --page-text: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--page-text);
            background:
                radial-gradient(circle at top left, rgba(34, 211, 238, 0.18), transparent 30%),
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.16), transparent 24%),
                linear-gradient(180deg, #020617 0%, #0f172a 55%, #020617 100%);
            text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .snack-shell {
            position: relative;
            isolation: isolate;
        }

        .snack-shell::before,
        .snack-shell::after {
            content: '';
            position: fixed;
            z-index: -20;
            width: 28rem;
            height: 28rem;
            border-radius: 9999px;
            filter: blur(70px);
            opacity: 0.35;
            pointer-events: none;
            animation: snack-float 18s ease-in-out infinite;
        }

        .snack-shell::before {
            top: -8rem;
            left: -6rem;
            background: rgba(34, 211, 238, 0.28);
        }

        .snack-shell::after {
            right: -6rem;
            bottom: -10rem;
            background: rgba(59, 130, 246, 0.22);
            animation-delay: -9s;
        }

        .snack-panel,
        .snack-panel-strong,
        header,
        article,
        aside section {
            border: 1px solid var(--page-border);
            backdrop-filter: blur(22px);
            box-shadow: 0 20px 70px rgba(2, 6, 23, 0.45);
        }

        header,
        article,
        aside section {
            background: var(--page-panel);
        }

        article,
        aside section {
            position: relative;
            overflow: hidden;
        }

        article::after,
        aside section::after,
        header::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.06), transparent 45%);
            opacity: 0.65;
        }

        main {
            position: relative;
            max-width: 80rem;
            margin: 0 auto;
            padding: 2rem 1rem 2.5rem;
        }

        main>* {
            position: relative;
            z-index: 1;
        }

        header {
            border-radius: 1.5rem;
            padding: 1.5rem;
        }

        header h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
        }

        header p,
        article p,
        aside p,
        dd,
        dt,
        label {
            margin: 0;
        }

        .snack-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(15, 23, 42, 0.52);
            padding: 0.5rem 0.85rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #bfdbfe;
        }

        .snack-grid {
            display: grid;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 1024px) {
            .snack-grid {
                grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.9fr);
            }
        }

        .snack-card-grid {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 640px) {
            .snack-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .snack-card-grid>div,
        .snack-note,
        .snack-form,
        .snack-audit-item,
        .snack-empty-state {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 1rem;
        }

        .snack-card-grid>div,
        .snack-empty-state {
            padding: 1rem;
        }

        .snack-form {
            padding: 1.25rem;
        }

        .snack-note {
            padding: 1rem;
        }

        .snack-audit-item {
            padding: 0.9rem 1rem;
        }

        .snack-button,
        button,
        a[role="button"] {
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease, border-color 180ms ease;
        }

        button:hover,
        a[role="button"]:hover,
        .snack-button:hover {
            transform: translateY(-1px);
        }

        select,
        textarea {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.28);
            background: rgba(248, 250, 252, 0.98);
            color: #0f172a;
            padding: 0.9rem 1rem;
            font: inherit;
            outline: none;
        }

        select:focus,
        textarea:focus {
            border-color: rgba(34, 211, 238, 0.9);
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.12);
        }

        button:disabled,
        select:disabled,
        textarea:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .snack-fade-in {
            animation: snack-rise 700ms ease both;
            animation-delay: var(--snack-delay, 0ms);
        }

        @keyframes snack-rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes snack-float {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(0, 18px, 0) scale(1.06);
            }
        }

        @media (max-width: 767px) {
            main {
                padding-inline: 0.75rem;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.documentElement;
            const shell = document.querySelector('[data-snack-shell]');
            const revealItems = document.querySelectorAll('[data-snack-reveal]');
            const decisionSelect = document.querySelector('[data-decision-select]');
            const recommendationField = document.querySelector('[data-recommendation-field]');
            const statusBadge = document.querySelector('[data-status-badge]');

            if (shell) {
                root.classList.add('scroll-smooth');
            }

            revealItems.forEach((element, index) => {
                element.style.setProperty('--snack-delay', `${90 * (index + 1)}ms`);
                element.classList.add('snack-fade-in');
            });

            if (decisionSelect && recommendationField) {
                const syncRecommendationState = () => {
                    const isRejected = decisionSelect.value === 'tidak_lulus';

                    recommendationField.toggleAttribute('required', isRejected);

                    if (statusBadge) {
                        statusBadge.textContent = decisionSelect.value ? decisionSelect.value.replaceAll('_', ' ') : 'Belum ada keputusan';
                    }
                };

                decisionSelect.addEventListener('change', syncRecommendationState);
                syncRecommendationState();
            }
        });
    </script>
    @endif
</head>

<body class="snack-shell min-h-screen bg-slate-950 text-slate-100">
    <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-linear-to-b from-cyan-500/30 via-sky-500/10 to-transparent blur-3xl"></div>
    <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8" data-snack-shell>
        <header class="snack-fade-in flex flex-col gap-4 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-cyan-950/40 backdrop-blur md:flex-row md:items-center md:justify-between" data-snack-reveal>
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">QC Manager Review</p>
                <h1 class="text-3xl font-semibold tracking-tight text-white">
                    {{ $batch?->batch_number ? 'Batch ' . $batch->batch_number : 'SnackCheck Review Center' }}
                </h1>
                <p class="max-w-2xl text-sm leading-6 text-slate-300">
                    {{ $batch?->batch_number
                        ? 'Halaman ini menampilkan hasil review batch dan formulir untuk menyimpan keputusan akhir ke sistem.'
                        : 'Halaman utama aplikasi untuk melihat dan memproses keputusan akhir batch pengujian.' }}
                </p>
            </div>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-full border border-cyan-400/30 bg-cyan-400/10 px-4 py-2 text-sm font-medium text-cyan-100 transition hover:border-cyan-300/60 hover:bg-cyan-400/20">
                Beranda
            </a>
        </header>

        <section class="snack-grid">
            <article class="snack-fade-in rounded-3xl border border-white/10 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/50" data-snack-reveal>
                <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Ringkasan Batch</p>
                        <h2 class="mt-2 text-xl font-semibold text-white">Informasi Pengujian</h2>
                    </div>
                    <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200" data-status-badge>
                        {{ $batch ? ($batch->status instanceof \BackedEnum ? $batch->status->value : $batch->status) : 'Belum ada batch' }}
                    </span>
                </div>

                @if ($batch)
                <dl class="snack-card-grid">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-xs uppercase tracking-[0.3em] text-slate-400">ID Batch</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">{{ $batch->batch_number }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-xs uppercase tracking-[0.3em] text-slate-400">Status Saat Ini</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">{{ $batch->status instanceof \BackedEnum ? $batch->status->value : $batch->status }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-xs uppercase tracking-[0.3em] text-slate-400">Tanggal Produksi</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">{{ optional($batch->production_date)->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-xs uppercase tracking-[0.3em] text-slate-400">Tanggal Kedaluwarsa</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">{{ optional($batch->expiration_date)->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 sm:col-span-2">
                        <dt class="text-xs uppercase tracking-[0.3em] text-slate-400">Jumlah Sampel</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">{{ $batch->sample_quantity ?? '-' }}</dd>
                    </div>
                </dl>

                <div class="snack-note mt-8 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/80">Keputusan Terakhir</p>
                            <p class="mt-2 text-lg font-semibold text-white">
                                {{ $batch->testDecision?->decision_status instanceof \BackedEnum ? $batch->testDecision?->decision_status->value : ($batch->testDecision?->decision_status ?? 'Belum ada keputusan') }}
                            </p>
                        </div>
                        <div class="text-right text-sm text-cyan-100/90">
                            <p>QC Manager dapat mengubah keputusan melalui formulir di sisi kanan.</p>
                        </div>
                    </div>
                    @if ($batch->testDecision?->notes)
                    <p class="mt-4 rounded-xl bg-slate-950/40 p-4 text-sm leading-6 text-slate-200">{{ $batch->testDecision->notes }}</p>
                    @endif
                </div>
                @else
                <div class="snack-empty-state mt-6 rounded-2xl border border-dashed border-white/15 bg-white/5 p-6 text-sm leading-6 text-slate-300">
                    Belum ada batch di database. Setelah batch pertama dibuat, halaman ini akan menampilkan detail review dan form keputusan akhir.
                </div>
                @endif
            </article>

            <aside class="space-y-6">
                <section class="snack-fade-in rounded-3xl border border-white/10 bg-white p-6 text-slate-900 shadow-xl shadow-slate-950/50" data-snack-reveal>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-700">Form Review</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Tentukan Keputusan Akhir</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">PATCH</span>
                    </div>

                    @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Terjadi kesalahan validasi:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if (session('message'))
                    <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                        {{ session('message') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ $batch ? route('batches.review.update', ['batch_id' => $batch->id]) : '#' }}" class="snack-form mt-6 space-y-5">
                        @csrf
                        @method('PATCH')

                        @if (! $batch)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            Form ini akan aktif setelah ada batch yang tersedia.
                        </div>
                        @endif

                        <div>
                            <label for="keputusan_akhir" class="block text-sm font-medium text-slate-700">Keputusan Akhir</label>
                            <select id="keputusan_akhir" name="keputusan_akhir" data-decision-select @disabled(! $batch) class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 disabled:cursor-not-allowed disabled:bg-slate-100">
                                <option value="">Pilih keputusan</option>
                                @foreach ($decisionOptions as $decisionOption)
                                <option value="{{ $decisionOption->value }}" @selected(old('keputusan_akhir')===$decisionOption->value || ($batch?->testDecision?->decision_status instanceof \BackedEnum && $batch->testDecision->decision_status->value === $decisionOption->value))>
                                    {{ str_replace('_', ' ', ucfirst($decisionOption->value)) }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs leading-5 text-slate-500">Jika memilih tidak_lulus, catatan rekomendasi wajib diisi.</p>
                        </div>

                        <div>
                            <label for="catatan_rekomendasi" class="block text-sm font-medium text-slate-700">Catatan Rekomendasi</label>
                            <textarea id="catatan_rekomendasi" name="catatan_rekomendasi" rows="6" data-recommendation-field @disabled(! $batch) class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 disabled:cursor-not-allowed disabled:bg-slate-100" placeholder="Tuliskan alasan, arahan, atau tindak lanjut review">{{ old('catatan_rekomendasi', $batch?->testDecision?->notes) }}</textarea>
                        </div>

                        <button type="submit" @disabled(! $batch) class="snack-button inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:bg-slate-400">
                            Simpan Keputusan Final
                        </button>
                    </form>
                </section>

                <section class="snack-fade-in rounded-3xl border border-white/10 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/50" data-snack-reveal>
                    <h3 class="text-lg font-semibold text-white">Audit Trail Terbaru</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($batch?->auditTrails ?? [] as $auditTrail)
                        <div class="snack-audit-item rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                            <p class="font-semibold text-white">{{ $auditTrail->action }}</p>
                            <p class="mt-1">{{ optional($auditTrail->created_at)->format('d M Y H:i') }}</p>
                            <p class="mt-2 text-xs text-slate-400">Old: {{ json_encode($auditTrail->old_values) }}</p>
                            <p class="mt-1 text-xs text-slate-400">New: {{ json_encode($auditTrail->new_values) }}</p>
                        </div>
                        @empty
                        <p class="text-sm text-slate-400">Belum ada audit trail untuk batch ini.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </section>
    </main>
</body>

</html>