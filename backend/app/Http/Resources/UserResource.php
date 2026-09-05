<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return ['id'=>$this->id,'name'=>$this->name,'email'=>$this->email,'phone_number'=>$this->phone_number,
            'location_id'=>$this->location_id,'role'=>(int)$this->role,'is_active'=>(bool)$this->is_active,'created_at'=>optional($this->created_at)->toISOString()];
    }
}
