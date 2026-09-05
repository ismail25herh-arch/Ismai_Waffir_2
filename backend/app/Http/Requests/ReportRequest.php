<?php
namespace App\Http\Requests;
class ReportRequest extends ApiRequest
{
    public function rules() { return ['price_id'=>'required|exists:prices,id','type'=>'required|in:سعر مبالغ فيه,سعر غير صحيح,معلومات غير صحيحة','description'=>'nullable|required_if:type,معلومات غير صحيحة|string|max:5000']; }
}
