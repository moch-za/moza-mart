<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate dijaga di route
    }

    public function rules(): array
    {
        return [
            'supplier_id'            => 'required|integer|exists:suppliers,id',
            'notes'                  => 'nullable|string|max:500',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|integer|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_cost'      => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'       => 'Pilih supplier terlebih dahulu.',
            'supplier_id.exists'         => 'Supplier tidak ditemukan.',
            'items.required'             => 'Tambahkan minimal 1 produk.',
            'items.*.product_id.required'=> 'Produk harus dipilih.',
            'items.*.product_id.exists'  => 'Produk tidak ditemukan.',
            'items.*.quantity.required'  => 'Quantity harus diisi.',
            'items.*.quantity.min'       => 'Quantity minimal 1.',
            'items.*.unit_cost.required' => 'Harga beli harus diisi.',
            'items.*.unit_cost.min'      => 'Harga beli tidak boleh negatif.',
        ];
    }
}
