<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
class ProductController extends Controller
{
    public function index(Request $request) { $items=Product::with(['prices','officialPrices.unit'])->when($request->q,function($q,$v){return $q->where('name','like','%'.$v.'%');})->when($request->category,function($q,$v){return $q->where('category',$v);})->orderBy('name')->paginate(min(max((int)$request->input('per_page',15),1),100)); return response()->json(['success'=>true,'data'=>ProductResource::collection($items)]); }
    public function show(Product $product) { return response()->json(['success'=>true,'data'=>new ProductResource($product->load(['prices','officialPrices.unit']))]); }
    public function store(ProductRequest $request) { $item=Product::create($request->validated()); return response()->json(['success'=>true,'message'=>'تم إنشاء المنتج.','data'=>new ProductResource($item)],201); }
    public function update(ProductRequest $request,Product $product) { $product->update($request->validated()); return response()->json(['success'=>true,'message'=>'تم تحديث المنتج.','data'=>new ProductResource($product)]); }
    public function destroy(Product $product) { $product->delete(); return response()->json(['success'=>true,'message'=>'تم حذف المنتج.']); }
}
