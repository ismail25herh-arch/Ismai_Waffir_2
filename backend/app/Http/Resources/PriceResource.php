<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PriceResource extends JsonResource
{
    public function toArray($request) { $ratings=$this->relationLoaded('ratings')?$this->ratings:collect(); return ['id'=>$this->id,'user_id'=>$this->user_id,'product_id'=>$this->product_id,'product_name'=>optional($this->product)->name,'store_id'=>$this->store_id,'store_name'=>optional($this->store)->name,'store_area'=>optional(optional($this->store)->location)->district,'price'=>(float)$this->price,'unit_id'=>$this->unit_id,'unit'=>optional($this->unit)->name,'amount'=>(float)$this->amount,'brand_id'=>$this->brand_id,'brand'=>optional($this->brand)->name,'submitted_by'=>optional($this->user)->name,'submitted_at'=>$this->created_at,'thumbs_up'=>$ratings->where('value',true)->count(),'thumbs_down'=>$ratings->where('value',false)->count(),'total_ratings'=>$ratings->count(),'status'=>'pending']; }
}
