<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SnackCheck Journey 3 Playground</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: #111827;
            --panel-soft: #1f2937;
            --line: #334155;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --brand: #38bdf8;
            --ok: #22c55e;
            --warn: #f59e0b;
            --err: #ef4444;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0b1222, var(--bg) 45%);
            color: var(--text);
        }

        .wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 24px;
            display: grid;
            gap: 16px;
        }

        .panel {
            background: linear-gradient(180deg, rgba(17, 24, 39, 0.98), rgba(17, 24, 39, 0.92));
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        p {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .grid {
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 980px) {
            .grid {
                grid-template-columns: 1.35fr 1fr;
            }
        }

        .row {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(12, 1fr);
        }

        .col-6 {
            grid-column: span 12;
        }

        .col-12 {
            grid-column: span 12;
        }

        @media (min-width: 740px) {
            .col-6 {
                grid-column: span 6;
            }
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #cbd5e1;
            margin-bottom: 7px;
        }

        input,
        select,
        textarea,
        button {
            width: 100%;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: var(--panel-soft);
            color: var(--text);
            padding: 10px 12px;
            font: inherit;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            cursor: pointer;
            background: #0b3b56;
            border-color: #0f5f8c;
            font-weight: 700;
            transition: 180ms ease;
        }

        button:hover {
            transform: translateY(-1px);
            background: #0f5f8c;
        }

        .btn-ok {
            background: #174a29;
            border-color: #2f7d4c;
        }

        .btn-ok:hover {
            background: #2f7d4c;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #1e4d67;
            background: #0f2533;
            color: #bfe9ff;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            margin-top: 8px;
        }

        .note {
            font-size: 13px;
            line-height: 1.5;
            color: #cbd5e1;
            border: 1px dashed #3f4f66;
            border-radius: 10px;
            padding: 10px;
            margin-top: 12px;
            background: rgba(15, 23, 42, 0.45);
        }

        .status {
            margin-top: 12px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            border: 1px solid transparent;
        }

        .status.ok {
            border-color: rgba(34, 197, 94, 0.45);
            background: rgba(34, 197, 94, 0.16);
            color: #bbf7d0;
        }

        .status.warn {
            border-color: rgba(245, 158, 11, 0.45);
            background: rgba(245, 158, 11, 0.16);
            color: #fde68a;
        }

        .status.err {
            border-color: rgba(239, 68, 68, 0.45);
            background: rgba(239, 68, 68, 0.18);
            color: #fecaca;
        }

        .code {
            margin-top: 12px;
            border: 1px solid #374151;
            border-radius: 10px;
            background: #030712;
            color: #c7d2fe;
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
            line-height: 1.5;
            padding: 12px;
            white-space: pre-wrap;
            max-height: 320px;
            overflow: auto;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #374151;
            border-radius: 10px;
            margin-top: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th,
        td {
            border-bottom: 1px solid #253040;
            padding: 10px;
            font-size: 13px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #121b2b;
            color: #bfdbfe;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .mono {
            font-family: Consolas, Monaco, monospace;
        }

        a.link {
            color: #7dd3fc;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <main class="wrap">
        <section class="panel">
            <h1>SnackCheck Journey 3 Playground</h1>
            <p>Halaman ini dibuat untuk eksplorasi user story 3.1 sampai 3.5 tanpa harus menyiapkan UI terpisah dulu.</p>
            <span class="chip">US 3.1 Submit</span>
            <span class="chip">US 3.2 Detail</span>
            <span class="chip">US 3.3 Keputusan</span>
            <span class="chip">US 3.4 CoA</span>
            <span class="chip">US 3.5 Notifikasi</span>
            <div class="note">
                Batch default diisi dari batch terbaru di database.
                @if ($batch)
                Saat ini: <strong class="mono">{{ $batch->batch_number }}</strong> (ID <strong class="mono">{{ $batch->id }}</strong>).
                @else
                Belum ada batch. Jalankan seed terlebih dulu.
                @endif
            </div>
        </section>

        <div class="grid">
            <section class="panel">
                <h2>Kontrol Demo</h2>
                <p>Pilih batch lalu jalankan aksi endpoint sesuai user story.</p>

                <div class="row" style="margin-top: 14px;">
                    <div class="col-6">
                        <label for="batch_id">Batch ID</label>
                        <input id="batch_id" type="number" min="1" value="{{ $batch?->id ?? 1 }}">
                    </div>
                    <div class="col-6">
                        <label for="batch_number_hint">Batch Number (hint)</label>
                        <input id="batch_number_hint" type="text" value="{{ $batch?->batch_number ?? 'BATCH-001' }}" readonly>
                    </div>
                </div>

                <div class="row" style="margin-top: 12px;">
                    <div class="col-6">
                        <button id="btn_submit_test" class="btn-ok">US 3.1 - Submit Hasil Uji</button>
                    </div>
                    <div class="col-6">
                        <button id="btn_get_detail">US 3.2 - Lihat Detail Batch</button>
                    </div>
                    <div class="col-12" style="margin-top: 10px;">
                        <button id="btn_request_retest" class="btn-ok" style="background-color: #f59e0b; border-color: #d97706;">CR-Epic - Request Re-Test (Khusus Batch Gagal)</button>
                    </div>
                </div>

                <hr style="border-color:#334155; margin: 18px 0;">

                <h3>US 3.3, 3.4, 3.5 - Keputusan Akhir QC</h3>
                <p class="muted">Keputusan <span class="mono">lulus</span> akan memicu CoA (US 3.4). Semua keputusan memicu notifikasi sesuai service (US 3.5).</p>
                @php
                $decisionValues = ['lulus', 'tidak_lulus', 'ditahan', 'uji_ulang'];
                @endphp

                <div class="row" style="margin-top: 12px;">
                    <div class="col-6">
                        <label for="keputusan_akhir">Keputusan Akhir</label>
                        <select id="keputusan_akhir">
                            @foreach ($decisionValues as $decision)
                            <option value="{{ $decision }}">{{ $decision }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="tindakan_rekomendasi">Tindakan Rekomendasi</label>
                        <select id="tindakan_rekomendasi">
                            <option value="">(kosong)</option>
                            <option value="disposal">disposal</option>
                            <option value="rework">rework</option>
                            <option value="hold">hold</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="catatan">Catatan</label>
                        <textarea id="catatan" placeholder="Contoh: kadar air melewati batas, perlu tindakan rework."></textarea>
                    </div>
                    <div class="col-12">
                        <button id="btn_decide">Simpan Keputusan Final (PUT /batches/{id}/review)</button>
                    </div>
                </div>

                <div id="status_box" class="status warn">Belum ada aksi dijalankan.</div>
            </section>

            <section class="panel">
                <h2>Output API</h2>
                <p>Response endpoint akan tampil di bawah untuk bantu debugging cepat.</p>
                <pre id="response_box" class="code">Belum ada response.</pre>

                <h3 style="margin-top:16px;">Ringkasan Detail Batch (US 3.2)</h3>
                <div id="detail_summary" class="note">Klik tombol "US 3.2 - Lihat Detail Batch" untuk memuat data.</div>
                <div id="test_result_table" class="table-wrap" style="display:none;"></div>

                <div class="note" style="margin-top: 12px;">
                    Untuk verifikasi notifikasi dan CoA:
                    <br>1) Cek log di <strong class="mono">storage/logs/laravel.log</strong>
                    <br>2) Cek file CoA di <strong class="mono">storage/app/public/coa</strong>
                    <br>3) Jika perlu URL publik file CoA, jalankan <strong class="mono">php artisan storage:link</strong>
                </div>
            </section>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const batchInput = document.getElementById('batch_id');
        const decisionInput = document.getElementById('keputusan_akhir');
        const recommendationInput = document.getElementById('tindakan_rekomendasi');
        const notesInput = document.getElementById('catatan');
        const statusBox = document.getElementById('status_box');
        const responseBox = document.getElementById('response_box');
        const detailSummary = document.getElementById('detail_summary');
        const tableContainer = document.getElementById('test_result_table');

        const setStatus = (message, type = 'warn') => {
            statusBox.className = `status ${type}`;
            statusBox.textContent = message;
        };

        const showResponse = (label, payload, statusCode = null) => {
            const prefix = statusCode === null ? label : `${label} (HTTP ${statusCode})`;
            responseBox.textContent = `${prefix}\n\n${JSON.stringify(payload, null, 2)}`;
        };

        const getBatchId = () => {
            const value = Number(batchInput.value);
            if (!Number.isInteger(value) || value <= 0) {
                setStatus('Batch ID harus angka lebih dari 0.', 'err');
                throw new Error('invalid_batch_id');
            }
            return value;
        };

        const requestJson = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {})
                }
            });

            let payload = null;
            try {
                payload = await response.json();
            } catch (error) {
                payload = {
                    message: 'Response bukan JSON valid.'
                };
            }

            return {
                response,
                payload
            };
        };

        const renderDetail = (detailPayload) => {
            const detailData = detailPayload?.data || null;
            if (!detailData) {
                detailSummary.textContent = 'Data detail tidak ditemukan di response.';
                tableContainer.style.display = 'none';
                tableContainer.innerHTML = '';
                return;
            }

            const batch = detailData.batch || {};
            const docs = detailData.documents || {};
            const decision = detailData.test_decision || {};

            const coaPath = docs.coa_document ? `<a class="link" href="/storage/${docs.coa_document}" target="_blank">${docs.coa_document}</a>` : '-';

            detailSummary.innerHTML = `
                <strong>Batch:</strong> ${batch.batch_number || '-'}
                <br><strong>Status:</strong> ${batch.status || '-'}
                <br><strong>Produk:</strong> ${(batch.product || '-')}${batch.variant ? ` (${batch.variant})` : ''}
                <br><strong>Tanggal Produksi:</strong> ${batch.production_date || '-'}
                <br><strong>Tanggal Kedaluwarsa:</strong> ${batch.expiration_date || '-'}
                <br><strong>Keputusan Akhir:</strong> ${decision.decision_status || 'belum ada'}
                <br><strong>Nomor CoA:</strong> ${docs.coa_number || '-'}
                <br><strong>Path CoA:</strong> ${coaPath}
            `;

            const rows = detailData.test_results || [];
            if (!rows.length) {
                tableContainer.style.display = 'none';
                tableContainer.innerHTML = '';
                return;
            }

            const htmlRows = rows.map((row) => `
                <tr>
                    <td>${row.parameter || '-'}</td>
                    <td>${row.category || '-'}</td>
                    <td>${row.result_value ?? '-'}</td>
                    <td>${row.standard_min ?? '-'}</td>
                    <td>${row.standard_max ?? '-'}</td>
                    <td>${row.indicator || '-'}</td>
                    <td>${row.attachment_path || '-'}</td>
                </tr>
            `).join('');

            tableContainer.innerHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Kategori</th>
                            <th>Hasil</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Indikator</th>
                            <th>Lampiran</th>
                        </tr>
                    </thead>
                    <tbody>${htmlRows}</tbody>
                </table>
            `;
            tableContainer.style.display = 'block';
        };

        document.getElementById('btn_submit_test').addEventListener('click', async () => {
            try {
                const batchId = getBatchId();
                setStatus('Mengirim submit hasil uji...', 'warn');

                const {
                    response,
                    payload
                } = await requestJson(`/batches/${batchId}/submit-test`, {
                    method: 'POST'
                });

                showResponse('US 3.1 - Submit Hasil Uji', payload, response.status);
                setStatus(payload.message || 'Submit selesai.', response.ok ? 'ok' : 'err');
            } catch (error) {
                if (error.message !== 'invalid_batch_id') {
                    setStatus('Gagal memanggil endpoint submit hasil uji.', 'err');
                }
            }
        });

        document.getElementById('btn_get_detail').addEventListener('click', async () => {
            try {
                const batchId = getBatchId();
                setStatus('Mengambil detail batch...', 'warn');

                const {
                    response,
                    payload
                } = await requestJson(`/batches/${batchId}/review`, {
                    method: 'GET'
                });

                showResponse('US 3.2 - Detail Hasil Uji', payload, response.status);
                if (response.ok) {
                    renderDetail(payload);
                }

                setStatus(payload.message || 'Ambil detail selesai.', response.ok ? 'ok' : 'err');
            } catch (error) {
                if (error.message !== 'invalid_batch_id') {
                    setStatus('Gagal memanggil endpoint detail batch.', 'err');
                }
            }
        });

        document.getElementById('btn_decide').addEventListener('click', async () => {
            try {
                const batchId = getBatchId();
                const body = {
                    keputusan_akhir: decisionInput.value,
                    tindakan_rekomendasi: recommendationInput.value || null,
                    catatan: notesInput.value || null,
                };

                setStatus('Menyimpan keputusan akhir QC...', 'warn');

                const {
                    response,
                    payload
                } = await requestJson(`/batches/${batchId}/review`, {
                    method: 'PUT',
                    body: JSON.stringify(body)
                });

                showResponse('US 3.3/3.4/3.5 - Simpan Keputusan', payload, response.status);
                setStatus(payload.message || 'Simpan keputusan selesai.', response.ok ? 'ok' : 'err');

                if (response.ok) {
                    const detail = await requestJson(`/batches/${batchId}/review`, {
                        method: 'GET'
                    });
                    if (detail.response.ok) {
                        renderDetail(detail.payload);
                    }
                }
            } catch (error) {
                if (error.message !== 'invalid_batch_id') {
                    setStatus('Gagal memanggil endpoint keputusan akhir.', 'err');
                }
            }
        });

        document.getElementById('btn_request_retest').addEventListener('click', async () => {
            try {
                const batchId = getBatchId();
                setStatus('Mengajukan Request Re-test...', 'warn');
                const { response, payload } = await requestJson(`/batches/${batchId}/request-retest`, { method: 'POST' });
                showResponse('CR-Epic - Request Re-Test', payload, response.status);
                setStatus(payload.message || 'Request Re-test selesai.', response.ok ? 'ok' : 'err');
            } catch (error) {
                if (error.message !== 'invalid_batch_id') setStatus('Gagal memanggil endpoint re-test.', 'err');
            }
        });
    </script>
</body>

</html>