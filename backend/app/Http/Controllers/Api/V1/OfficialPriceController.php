<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfficialPriceResource;
use App\Models\OfficialPrice;
use App\Models\OfficialPriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OfficialPriceController extends Controller
{
    public function index(Request $request) { $items=OfficialPrice::with(['product','unit'])->when($request->product_id,function($q,$v){return $q->where('product_id',$v);})->paginate(min(max((int)$request->input('per_page',15),1),100)); return response()->json(['success'=>true,'data'=>OfficialPriceResource::collection($items)]); }
    public function show(OfficialPrice $officialPrice) { return response()->json(['success'=>true,'data'=>new OfficialPriceResource($officialPrice)]); }
    public function store(Request $request) { $data=$this->validateData($request); $item=DB::transaction(function()use($data,$request){$item=OfficialPrice::create($data); OfficialPriceHistory::create(['official_price_id'=>$item->id,'product_id'=>$item->product_id,'unit_id'=>$item->unit_id,'amount'=>$item->amount,'price'=>$item->price,'new_price'=>$item->price,'changed_by'=>$request->user()->id,'changed_at'=>now()]); return $item;}); return response()->json(['success'=>true,'message'=>'تم اعتماد السعر.','data'=>new OfficialPriceResource($item)],201); }
    public function update(Request $request,OfficialPrice $officialPrice) { $data=$this->validateData($request); $old=$officialPrice->price; DB::transaction(function()use($officialPrice,$data,$old,$request){$officialPrice->update($data); if($old!=$data['price']) OfficialPriceHistory::create(['official_price_id'=>$officialPrice->id,'product_id'=>$officialPrice->product_id,'unit_id'=>$officialPrice->unit_id,'amount'=>$officialPrice->amount,'price'=>$officialPrice->price,'old_price'=>$old,'new_price'=>$data['price'],'changed_by'=>$request->user()->id,'changed_at'=>now()]);}); return response()->json(['success'=>true,'message'=>'تم تحديث السعر الرسمي.','data'=>new OfficialPriceResource($officialPrice)]); }
    public function history(OfficialPrice $officialPrice) { return response()->json(['success'=>true,'data'=>$officialPrice->histories()->latest('changed_at')->paginate(30)]); }
    public function destroy(OfficialPrice $officialPrice) { $officialPrice->delete(); return response()->json(['success'=>true,'message'=>'تم حذف السعر الرسمي.']); }
    protected function validateData(Request $request) { return $request->validate(['product_id'=>'required|exists:products,id','unit_id'=>'required|exists:units,id','amount'=>'required|numeric|gt:0','price'=>'required|numeric|min:0']); }
}
