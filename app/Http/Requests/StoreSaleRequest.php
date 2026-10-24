<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate dijaga di route
    }

    public function rules(): array
    {
        return [
            'notes'                  => 'nullable|string|max:500',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|integer|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'             => 'Tambahkan minimal 1 produk.',
            'items.*.product_id.required'=> 'Produk harus dipilih.',
            'items.*.product_id.exists'  => 'Produk tidak ditemukan.',
            'items.*.quantity.required'  => 'Quantity harus diisi.',
            'items.*.quantity.min'       => 'Quantity minimal 1.',
        ];
    }
}
