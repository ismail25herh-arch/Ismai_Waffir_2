<?php
namespace App\Http\Requests;
class VerifyOtpRequest extends ApiRequest
{
    public function rules() { return ['phone_number'=>'required|string|max:30','otp'=>'required|digits:6','name'=>'nullable|string|max:120','purpose'=>'sometimes|in:login,forgot_password']; }
    public function messages() { return ['phone_number.required'=>'رقم الجوال مطلوب.','otp.required'=>'رمز التحقق مطلوب.','otp.digits'=>'رمز التحقق يجب أن يتكون من 6 أرقام.']; }
}
