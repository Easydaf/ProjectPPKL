Login user_story_output.md

Prompt: "Tolong perbaiki dan lengkapi fungsi update untuk QC Manager dalam memberikan keputusan akhir (review) terhadap sebuah batch pengujian. Pastikan kodenya utuh, menyertakan Route, Model yang siap insert, dan logika bisnis sesuai Kriteria Penerimaan agar bisa dilakukan pengujian manual."

Context File: "ReviewController.php, Batch.php, ReviewBatchRequest.php, TestDecision.php, AuditTrail.php, routes/web.php"

Skills: "panduan best practice Laravel MVC dari skills.md, perhatikan penggunaan Dependency Injection, Form Request, routing, dan Mass Assignment protection."

Task: "Generate code for the following user story: 'Sebagai QC Manager, saya ingin memberikan Keputusan akhir terhadap setiap batch yang sudah direview agar status produk di sistem dapat mencerminkan kondisi aslinya dan pihak terkait dapat mengambil tindakan.' Buat kodenya lengkap agar siap dites (runnable)."

Input: "@parameter Integer batch_id, String keputusan_akhir, String tindakan_rekomendasi, String catatan"

Output: "@return JsonResponse, definisi Route di web.php, dan penyesuaian pada Model"

Rules:
//validation: keputusan_akhir wajib diisi (in: lulus, tidak_lulus, ditahan, uji_ulang).
//validation: Jika keputusan_akhir bernilai 'tidak_lulus', maka tindakan_rekomendasi wajib diisi dan hanya boleh berisi salah satu dari: 'disposal', 'rework', atau 'hold'.
//action: Jika keputusan 'lulus', tambahkan pemanggilan method service dummy CoAService::generate($batch->id) setelah transaksi database.
//action: Jika keputusan 'uji_ulang', tambahkan pemanggilan method service dummy NotificationService::sendToAnalis($batch->id).
//config: Buatkan deklarasi Route::put atau Route::post untuk endpoint ini agar bisa dipanggil.
//config: Tambahkan properti $fillable pada model TestDecision dan AuditTrail agar terhindar dari Mass Assignment Exception saat data disimpan.

What Changed: "Memperbaiki controller dengan logika bisnis yang sesuai PRD (Generate CoA & Notifikasi), memperketat aturan validasi, menambahkan endpoint route, dan melengkapi model agar data berhasil masuk ke database saat dites."

Commit Message: "fix(review): melengkapi fungsi persetujuan batch dengan route, model fillable, dan integrasi CoA"

```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/batches/{batch_id}/review', [ReviewController::class, 'show'])
        ->whereNumber('batch_id')
        ->name('batches.review.show');

    Route::put('/batches/{batch_id}/review', [ReviewController::class, 'update'])
        ->whereNumber('batch_id')
        ->name('batches.review.update');

    Route::patch('/batches/{batch_id}/review', [ReviewController::class, 'update'])
        ->whereNumber('batch_id')
        ->name('batches.review.patch');
});

// app/Http/Requests/ReviewBatchRequest.php
public function rules(): array
{
    return [
        'keputusan_akhir' => [
            'required',
            'string',
            Rule::in(array_column(DecisionStatus::cases(), 'value')),
        ],
        'tindakan_rekomendasi' => [
            Rule::requiredIf(fn(): bool => $this->input('keputusan_akhir') === DecisionStatus::TidakLulus->value),
            'nullable',
            'string',
            Rule::in(['disposal', 'rework', 'hold']),
        ],
        'catatan' => [
            'nullable',
            'string',
            'max:2000',
        ],
    ];
}

// app/Http/Controllers/ReviewController.php
public function update(
    ReviewBatchRequest $request,
    int $batch_id,
    CoAService $coAService,
    NotificationService $notificationService,
): JsonResponse {
    $batch = Batch::query()->find($batch_id);

    if ($batch === null) {
        return response()->json([
            'message' => 'Batch tidak ditemukan.',
        ], 404);
    }

    $validated = $request->validated();
    $updatedStatus = null;

    try {
        DB::transaction(function () use ($batch, $validated, $request, &$updatedStatus): void {
            $oldStatus = $batch->status instanceof BackedEnum
                ? $batch->status->value
                : (string) $batch->status;

            $updatedStatus = $validated['keputusan_akhir'];

            $batch->update([
                'status' => $updatedStatus,
            ]);

            TestDecision::query()->updateOrCreate(
                ['batch_id' => $batch->id],
                [
                    'user_id' => $request->user()?->id,
                    'decision_status' => $updatedStatus,
                    'action_recommendation' => $validated['tindakan_rekomendasi'] ?? null,
                    'notes' => $validated['catatan'] ?? null,
                ]
            );

            AuditTrail::query()->create([
                'user_id' => $request->user()?->id,
                'table_name' => 'batches',
                'record_id' => $batch->id,
                'action' => 'final_review_updated',
                'old_values' => [
                    'status' => $oldStatus,
                ],
                'new_values' => [
                    'status' => $updatedStatus,
                    'tindakan_rekomendasi' => $validated['tindakan_rekomendasi'] ?? null,
                    'catatan' => $validated['catatan'] ?? null,
                ],
            ]);
        });

        if ($updatedStatus === DecisionStatus::Lulus->value) {
            $coAPath = $coAService->generate($batch->id);

            TestDecision::query()
                ->where('batch_id', $batch->id)
                ->update(['coa_path' => $coAPath]);
        }

        if ($updatedStatus === DecisionStatus::UjiUlang->value) {
            $notificationService->sendToAnalis($batch->id);
        }
    } catch (Throwable $exception) {
        report($exception);

        return response()->json([
            'message' => 'Terjadi kesalahan saat memperbarui keputusan akhir batch.',
        ], 500);
    }

    return response()->json([
        'message' => 'Keputusan akhir batch berhasil diperbarui.',
        'data' => [
            'batch_id' => $batch->id,
            'status' => $this->normalizeStatusValue($batch->fresh()?->status),
        ],
    ]);
}

// app/Models/TestDecision.php
protected $fillable = [
    'batch_id',
    'user_id',
    'decision_status',
    'action_recommendation',
    'notes',
    'coa_path',
];

// app/Models/AuditTrail.php
protected $fillable = [
    'user_id',
    'table_name',
    'record_id',
    'action',
    'old_values',
    'new_values',
];
```
