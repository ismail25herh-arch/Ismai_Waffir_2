<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\PriceRequest;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
class PriceController extends Controller
{
    public function index(Request $request) { $items=Price::when($request->product_id,function($q,$v){return $q->where('product_id',$v);})->when($request->store_id,function($q,$v){return $q->where('store_id',$v);})->latest()->paginate(min(max((int)$request->input('per_page',15),1),100)); return response()->json(['success'=>true,'data'=>PriceResource::collection($items)]); }
    public function store(PriceRequest $request) { $data=$request->validated(); $data['user_id']=$request->user()->id; $item=Price::create($data); return response()->json(['success'=>true,'message'=>'تمت إضافة السعر.','data'=>new PriceResource($item)],201); }
    public function show(Price $price) { return response()->json(['success'=>true,'data'=>new PriceResource($price)]); }
    public function update(PriceRequest $request, Price $price) { abort_unless($price->user_id===$request->user()->id||$request->user()->isAdmin(),403); $price->update($request->validated()); return response()->json(['success'=>true,'message'=>'تم تحديث السعر.','data'=>new PriceResource($price)]); }
    public function destroy(Request $request, Price $price) { abort_unless($price->user_id===$request->user()->id||$request->user()->isAdmin(),403); $price->delete(); return response()->json(['success'=>true,'message'=>'تم حذف السعر.']); }
}
