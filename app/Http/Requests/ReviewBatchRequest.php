<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DecisionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'qc_manager';
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'keputusan_akhir.required' => 'Parameter keputusan_akhir wajib diisi.',
            'keputusan_akhir.in' => 'Nilai keputusan_akhir harus salah satu dari: lulus, tidak_lulus, ditahan, uji_ulang.',
            'tindakan_rekomendasi.required' => 'tindakan_rekomendasi wajib diisi ketika keputusan_akhir bernilai tidak_lulus.',
            'tindakan_rekomendasi.in' => 'tindakan_rekomendasi hanya boleh disposal, rework, atau hold.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $decision = $this->input('keputusan_akhir');

        if (!is_string($decision)) {
            return;
        }

        $normalizedDecision = str_replace([' ', '-'], '_', strtolower(trim($decision)));

        if ($normalizedDecision === 'perlu_uji_ulang') {
            $normalizedDecision = DecisionStatus::UjiUlang->value;
        }

        $this->merge([
            'keputusan_akhir' => $normalizedDecision,
            'tindakan_rekomendasi' => is_string($this->input('tindakan_rekomendasi'))
                ? strtolower(trim($this->input('tindakan_rekomendasi')))
                : $this->input('tindakan_rekomendasi'),
            'catatan' => $this->input('catatan'),
        ]);
    }
}
