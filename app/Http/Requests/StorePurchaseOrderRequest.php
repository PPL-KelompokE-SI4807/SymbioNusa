<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PurchaseOrder::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rfq_contracts_id_rfq_contract' => ['required', 'integer', 'exists:rfq_contracts,id_rfq_contract', 'unique:purchase_orders,rfq_contracts_id_rfq_contract'],
            'jarak_km' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'action' => ['required', 'in:draft,send'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rfq_contracts_id_rfq_contract.required' => 'Kontrak RFQ wajib dipilih.',
            'rfq_contracts_id_rfq_contract.exists' => 'Kontrak RFQ yang dipilih tidak valid.',
            'rfq_contracts_id_rfq_contract.unique' => 'Purchase Order untuk kontrak RFQ ini sudah pernah dibuat sebelumnya.',
            'jarak_km.numeric' => 'Jarak pengiriman harus berupa angka.',
            'jarak_km.min' => 'Jarak pengiriman tidak boleh bernilai negatif.',
            'action.required' => 'Aksi simpan draf atau kirim wajib ditentukan.',
            'action.in' => 'Aksi harus berupa simpan draf atau kirim ke seller.',
        ];
    }
}
