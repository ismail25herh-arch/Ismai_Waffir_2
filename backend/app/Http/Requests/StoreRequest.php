<?php
namespace App\Http\Requests;
class StoreRequest extends ApiRequest
{
    public function rules() { return ['location_id'=>'required|exists:locations,id','name'=>'required|string|max:255','slug'=>'nullable|string|max:255','address'=>'required|string|max:500','phone'=>'nullable|string|max:30','logo_url'=>'nullable|url|max:2048','is_verified'=>'sometimes|boolean','is_active'=>'sometimes|boolean']; }
}
