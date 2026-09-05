<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Http\Resources\BasicResource;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class StoreController extends Controller
{
    public function index(Request $request) { $items=Store::where('is_active',true)->with('location')->when($request->location_id,function($q,$v){return $q->where('location_id',$v);})->when($request->q,function($q,$v){return $q->where('name','like','%'.$v.'%');})->paginate(min(max((int)$request->input('per_page',15),1),100)); return response()->json(['success'=>true,'data'=>BasicResource::collection($items)]); }
    public function show($id) { return response()->json(['success'=>true,'data'=>new BasicResource(Store::with('location')->where('is_active',true)->findOrFail($id))]); }
    public function store(StoreRequest $request) { $data=$request->validated(); $data['slug']=$data['slug']??Str::slug($data['name']); $item=Store::create($data); return response()->json(['success'=>true,'message'=>'تم إنشاء المتجر.','data'=>new BasicResource($item)],201); }
    public function update(StoreRequest $request,Store $store) { $store->update($request->validated()); return response()->json(['success'=>true,'message'=>'تم تحديث المتجر.','data'=>new BasicResource($store)]); }
    public function destroy(Store $store) { $store->delete(); return response()->json(['success'=>true,'message'=>'تم حذف المتجر.']); }
}
