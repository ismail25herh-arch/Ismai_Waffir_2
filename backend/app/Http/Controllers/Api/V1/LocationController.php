<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\BasicResource;
use App\Models\Location;
use Illuminate\Http\Request;
class LocationController extends Controller
{
    public function index(Request $request) { $items=Location::where('is_active',true)->with('sector')->when($request->sector_id,function($q,$v){return $q->where('sector_id',$v);})->when($request->city,function($q,$v){return $q->where('city',$v);})->paginate(min(max((int)$request->input('per_page',15),1),100)); return response()->json(['success'=>true,'data'=>BasicResource::collection($items)]); }
    public function show($id) { return response()->json(['success'=>true,'data'=>new BasicResource(Location::with('sector')->where('is_active',true)->findOrFail($id))]); }
    public function store(Request $request) { $data=$request->validate(['sector_id'=>'required|exists:sectors,id','district'=>'required|string|max:255','name'=>'nullable|string|max:255','address'=>'nullable|string|max:500','city'=>'nullable|string|max:100','latitude'=>'nullable|numeric|between:-90,90','longitude'=>'nullable|numeric|between:-180,180','is_active'=>'sometimes|boolean'],['district.required'=>'الحي مطلوب.']); $item=Location::create($data); return response()->json(['success'=>true,'message'=>'تم إنشاء الموقع.','data'=>new BasicResource($item)],201); }
    public function update(Request $request,Location $location) { $location->update($request->validate(['name'=>'sometimes|string|max:255','address'=>'nullable|string|max:500','city'=>'nullable|string|max:100','is_active'=>'sometimes|boolean'])); return response()->json(['success'=>true,'message'=>'تم تحديث الموقع.','data'=>new BasicResource($location)]); }
    public function destroy(Location $location) { $location->delete(); return response()->json(['success'=>true,'message'=>'تم حذف الموقع.']); }
}
