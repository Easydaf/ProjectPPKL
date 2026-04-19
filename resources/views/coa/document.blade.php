<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>CoA {{ $coaNumber }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.45;
            margin: 28px;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        .header {
            border: 1px solid #1e293b;
            padding: 14px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .subtitle {
            color: #334155;
            font-size: 11px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .meta td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            vertical-align: top;
        }

        .meta .label {
            width: 30%;
            font-weight: 700;
            background: #f8fafc;
        }

        .section {
            margin-top: 14px;
        }

        .section h2 {
            font-size: 14px;
            margin-bottom: 8px;
        }

        table.results {
            width: 100%;
            border-collapse: collapse;
        }

        table.results th,
        table.results td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }

        table.results th {
            background: #e2e8f0;
            font-size: 11px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 18px;
            border-top: 1px dashed #64748b;
            padding-top: 10px;
            color: #475569;
            font-size: 10px;
        }

        .sign {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <header class="header">
        <p class="title">Certificate of Analysis (CoA)</p>
        <p class="subtitle">SnackCheck Quality Control Document</p>
    </header>

    <table class="meta">
        <tr>
            <td class="label">Nomor CoA</td>
            <td>{{ $coaNumber }}</td>
            <td class="label">Tanggal Terbit</td>
            <td>{{ $generatedAt->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Batch Number</td>
            <td>{{ $batch->batch_number }}</td>
            <td class="label">Status Keputusan</td>
            <td>{{ $batch->testDecision?->decision_status?->value ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Produk</td>
            <td>{{ $batch->product?->name ?? '-' }}{{ $batch->product?->variant ? ' - '.$batch->product->variant : '' }}</td>
            <td class="label">Qty Sampel</td>
            <td>{{ $batch->sample_quantity ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Produksi</td>
            <td>{{ $batch->production_date?->format('d M Y') ?? '-' }}</td>
            <td class="label">Tanggal Kedaluwarsa</td>
            <td>{{ $batch->expiration_date?->format('d M Y') ?? '-' }}</td>
        </tr>
    </table>

    <section class="section">
        <h2>Hasil Uji Parameter</h2>
        <table class="results">
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Kategori</th>
                    <th>Hasil</th>
                    <th>Batas Min</th>
                    <th>Batas Max</th>
                    <th>Kesimpulan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batch->testResults as $result)
                <tr>
                    <td>{{ $result->parameter?->parameter_name ?? '-' }}</td>
                    <td>{{ $result->parameter?->category?->value ?? '-' }}</td>
                    <td>{{ $result->result_value ?? '-' }}</td>
                    <td>{{ $result->parameter?->min_value ?? '-' }}</td>
                    <td>{{ $result->parameter?->max_value ?? '-' }}</td>
                    <td>{{ $result->is_compliant ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">Tidak ada data hasil uji.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Catatan Keputusan</h2>
        <p>{{ $batch->testDecision?->notes ?? 'Tidak ada catatan.' }}</p>
    </section>

    <section class="sign">
        <p><strong>QC Manager:</strong> {{ $batch->submitter?->name ?? 'QC Manager' }}</p>
        <p><strong>Tindakan Rekomendasi:</strong> {{ $batch->testDecision?->action_recommendation ?? '-' }}</p>
    </section>

    <footer class="footer">
        Dokumen ini dibuat otomatis oleh sistem SnackCheck menggunakan DOMPDF.
    </footer>
</body>

</html>