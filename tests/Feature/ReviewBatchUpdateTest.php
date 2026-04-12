<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class ReviewBatchUpdateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_qc_manager_can_submit_final_review_decision(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $user = User::factory()->qcManager()->create();

        $batch = Batch::query()->create([
            'batch_number' => 'BATCH-001',
            'user_id' => $user->id,
            'status' => 'menunggu_review',
        ]);

        $response = $this->actingAs($user)->patchJson(route('batches.review.update', [
            'batch_id' => $batch->id,
        ]), [
            'keputusan_akhir' => 'lulus',
            'catatan_rekomendasi' => 'Release ke gudang.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.batch_id', $batch->id)
            ->assertJsonPath('data.status', 'lulus');

        $this->assertDatabaseHas('test_decisions', [
            'batch_id' => $batch->id,
            'decision_status' => 'lulus',
        ]);

        $this->assertModelExists(AuditTrail::query()->where('record_id', $batch->id)->first());
    }

    public function test_catatan_rekomendasi_required_when_decision_tidak_lulus(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $user = User::factory()->qcManager()->create();

        $batch = Batch::query()->create([
            'batch_number' => 'BATCH-002',
            'user_id' => $user->id,
            'status' => 'menunggu_review',
        ]);

        $response = $this->actingAs($user)->patchJson(route('batches.review.update', [
            'batch_id' => $batch->id,
        ]), [
            'keputusan_akhir' => 'tidak_lulus',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['catatan_rekomendasi']);

        $this->assertDatabaseCount('test_decisions', 0);
        $this->assertDatabaseCount('audit_trails', 0);
    }

    public function test_non_qc_manager_cannot_submit_review(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $user = User::factory()->create([
            'role' => 'analis_lab',
        ]);

        $batch = Batch::query()->create([
            'batch_number' => 'BATCH-003',
            'user_id' => $user->id,
            'status' => 'menunggu_review',
        ]);

        $response = $this->actingAs($user)->patchJson(route('batches.review.update', [
            'batch_id' => $batch->id,
        ]), [
            'keputusan_akhir' => 'lulus',
            'catatan_rekomendasi' => 'Tidak berwenang.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('test_decisions', 0);
        $this->assertDatabaseCount('audit_trails', 0);
    }
}
