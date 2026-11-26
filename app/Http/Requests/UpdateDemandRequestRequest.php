<?php

namespace App\Http\Requests;

use App\Models\DemandRequest;

class UpdateDemandRequestRequest extends StoreDemandRequestRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $demand = $this->route('demand');
        if (! $demand instanceof DemandRequest) {
            return false;
        }

        $user = $this->user();

        return $user !== null && $user->can('update', $demand);
    }
}
