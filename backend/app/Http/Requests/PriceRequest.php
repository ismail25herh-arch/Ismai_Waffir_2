<?php
namespace App\Http\Requests;
class PriceRequest extends ApiRequest
{
    public function rules() { return ['store_id'=>'required|exists:stores,id','product_id'=>'required|exists:products,id','unit_id'=>'required|exists:units,id','brand_id'=>'required|exists:brands,id','amount'=>'required|numeric|gt:0','price'=>'required|numeric|min:0']; }
}
