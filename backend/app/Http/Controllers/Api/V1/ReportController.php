<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;
class ReportController extends Controller
{
    public function index(Request $request) { $items=Report::where('user_id',$request->user()->id)->with('price')->latest()->paginate(15); return response()->json(['success'=>true,'data'=>ReportResource::collection($items)]); }
    public function store(ReportRequest $request) { $data=$request->validated(); $data['user_id']=$request->user()->id; $item=Report::create($data); return response()->json(['success'=>true,'message'=>'تم إرسال البلاغ.','data'=>new ReportResource($item)],201); }
    public function show(Request $request, Report $report) { abort_unless($report->user_id===$request->user()->id||$request->user()->isAdmin(),403); return response()->json(['success'=>true,'data'=>new ReportResource($report)]); }
    public function destroy(Request $request, Report $report) { abort_unless($report->user_id===$request->user()->id||$request->user()->isAdmin(),403); $report->delete(); return response()->json(['success'=>true,'message'=>'تم حذف البلاغ.']); }
    public function adminIndex() { $items=Report::with(['user','price'])->latest()->paginate(30); return response()->json(['success'=>true,'data'=>ReportResource::collection($items)]); }
}
