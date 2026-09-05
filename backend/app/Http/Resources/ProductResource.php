<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ProductResource extends JsonResource
{
    public function toArray($request) {
        $official = $this->officialPrices->avg('price');
        $real = $this->prices->avg('price');
        $change = $official && $real !== null ? (($real - $official) / $official) * 100 : null;
        return ['id'=>$this->id,'name'=>$this->name,'category'=>$this->category,
            'official_price'=>$official !== null ? (float)$official : null,
            'real_price'=>$real !== null ? (float)$real : null,
            'avg_price'=>$real !== null ? (float)$real : null,
            'unit'=>optional($this->officialPrices->first())->unit ? $this->officialPrices->first()->unit->name : null,
            'prices_count'=>$this->prices->count(),'change_percent'=>$change !== null ? (float)$change : null,
            'is_price_up'=>$change !== null ? $change > 0 : false];
    }
}
