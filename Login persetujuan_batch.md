Login persetujuan_batch.md
Prompt: "Tolong buatkan fungsi update untuk QC Manager dalam memberikan keputusan akhir (review) terhadap sebuah batch pengujian."
Context File: "ReviewController.php, Batch.php, ReviewBatchRequest.php"
Skills: "panduan best practice Laravel MVC dari skills.md."
Task: "Generate code for the following user story: 'Sebagai QC Manager, saya ingin memberikan Keputusan akhir terhadap setiap batch yang sudah direview agar status produk di sistem dapat mencerminkan kondisi aslinya dan pihak terkait dapat mengambil tindakan.'"
Input: "@parameter Integer batch_id, String keputusan_akhir, String catatan_rekomendasi"
Output: "@return JsonResponse mengembalikan response sukses ubah data atau rincian pesan error"
Rules: "//validation: parameter keputusan_akhir wajib disubmit dan isinya hanya boleh salah satu dari pilihan: Lulus, Tidak Lulus, Ditahan, atau Perlu Uji Ulang. Jika keputusan_akhir bernilai 'Tidak Lulus', maka field catatan_rekomendasi wajib diisi untuk alasan penolakan."
What Changed: "Membuat fungsi controller untuk memvalidasi input review dan memperbarui kolom status serta data riwayat (audit trail) pada tabel batch."
Commit Message: "feat(review): penambahan fitur validasi dan keputusan final batch oleh QC manager"

```php
// app/Http/Controllers/ReviewController.php
public function update(ReviewBatchRequest $request, int $batch_id): JsonResponse
{
    $batch = Batch::query()->find($batch_id);

    if ($batch === null) {
        return response()->json([
            'message' => 'Batch tidak ditemukan.',
        ], 404);
    }

    $validated = $request->validated();

    DB::transaction(function () use ($batch, $validated, $request): void {
        $oldStatus = is_string($batch->status) ? $batch->status : $batch->status?->value;

        $batch->update([
            'status' => $validated['keputusan_akhir'],
        ]);

        TestDecision::query()->updateOrCreate(
            ['batch_id' => $batch->id],
            [
                'user_id' => $request->user()?->id,
                'decision_status' => $validated['keputusan_akhir'],
                'action_recommendation' => $validated['catatan_rekomendasi'] ?? null,
                'notes' => $validated['catatan_rekomendasi'] ?? null,
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
                'status' => $validated['keputusan_akhir'],
                'catatan_rekomendasi' => $validated['catatan_rekomendasi'] ?? null,
            ],
        ]);
    });

    return response()->json([
        'message' => 'Keputusan akhir batch berhasil diperbarui.',
        'data' => [
            'batch_id' => $batch->id,
            'status' => $batch->fresh()?->status,
        ],
    ]);
}

// app/Http/Requests/ReviewBatchRequest.php
public function rules(): array
{
    return [
        'keputusan_akhir' => [
            'required',
            Rule::in(['lulus', 'tidak_lulus', 'ditahan', 'uji_ulang']),
        ],
        'catatan_rekomendasi' => [
            Rule::requiredIf(fn (): bool => $this->input('keputusan_akhir') === 'tidak_lulus'),
            'nullable',
            'string',
            'max:2000',
        ],
    ];
}

// app/Models/Batch.php
protected function casts(): array
{
    return [
        'status' => BatchStatus::class,
    ];
}
```
