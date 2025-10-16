<?php

namespace App\Http\Requests\Proforma;

use Illuminate\Foundation\Http\FormRequest;

class StoreProformaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // اگر نیاز به سیاست داری، اینجا کنترل کن
    }

    public function rules(): array
    {
        return [
            'items' => ['required','array','min:1'],
            'items.*.title' => ['required','string','max:191'],
            'items.*.description' => ['nullable','string'],
            'items.*.qty' => ['nullable','integer','min:1'],
            'items.*.unit_price' => ['required','integer','min:0'],
            'tax_percent' => ['nullable','integer','min:0','max:100'],
            'period' => ['nullable','in:monthly,quarterly,yearly'], // ⬅️ جدید
            'meta' => ['nullable','array'],
        ];
    }
}
