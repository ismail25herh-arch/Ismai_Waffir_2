<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class OfficialPriceResource extends JsonResource
{
    public function toArray($request) { return ['id'=>$this->id,'product_id'=>$this->product_id,'product_name'=>optional($this->product)->name,'unit_id'=>$this->unit_id,'unit'=>optional($this->unit)->name,'amount'=>(float)$this->amount,'price'=>(float)$this->price,'created_at'=>$this->created_at,'updated_at'=>$this->updated_at]; }
}
