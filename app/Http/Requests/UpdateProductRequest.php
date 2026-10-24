<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'    => 'required|integer|exists:categories,id',
            'sku'            => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'name'           => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'minimum_stock'  => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'    => 'Kategori harus dipilih.',
            'category_id.exists'      => 'Kategori tidak ditemukan.',
            'sku.required'            => 'SKU harus diisi.',
            'sku.max'                 => 'SKU maksimal 50 karakter.',
            'sku.unique'              => 'SKU sudah digunakan produk lain.',
            'name.required'           => 'Nama produk harus diisi.',
            'purchase_price.required' => 'Harga beli harus diisi.',
            'purchase_price.min'      => 'Harga beli tidak boleh negatif.',
            'selling_price.required'  => 'Harga jual harus diisi.',
            'selling_price.min'       => 'Harga jual tidak boleh negatif.',
            'stock.min'               => 'Stok tidak boleh negatif.',
            'minimum_stock.min'       => 'Stok minimum tidak boleh negatif.',
        ];
    }
}
