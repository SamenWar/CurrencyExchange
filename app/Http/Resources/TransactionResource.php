<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'from_wallet_id' => $this->from_wallet_id,
            'to_wallet_id' => $this->to_wallet_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'system_fee' => $this->system_fee,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
