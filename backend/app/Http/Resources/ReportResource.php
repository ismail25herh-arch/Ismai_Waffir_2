<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ReportResource extends JsonResource
{
    public function toArray($request) { return ['id'=>$this->id,'user_id'=>$this->user_id,'price_id'=>$this->price_id,'product_name'=>optional(optional($this->price)->product)->name,'store_name'=>optional(optional($this->price)->store)->name,'store_area'=>optional(optional(optional($this->price)->store)->location)->district,'user_name'=>optional($this->user)->name,'type'=>$this->type,'description'=>$this->description,'reported_at'=>$this->created_at]; }
}
