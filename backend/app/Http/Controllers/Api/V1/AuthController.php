<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
class AuthController extends Controller
{
    protected $auth;
    public function __construct(AuthService $auth) { $this->auth = $auth; }
    public function requestOtp(OtpRequest $request) {
        $code = $this->auth->requestOtp($request->phone_number, $request->input('purpose', 'login'));
        $data = ['expires_in'=>300];
        if (app()->environment(['local','testing'])) $data['debug_otp'] = $code;
        return response()->json(['success'=>true,'message'=>'تم إرسال رمز التحقق.','data'=>$data]);
    }
    public function verifyOtp(VerifyOtpRequest $request) {
        list($user,$tokens) = $this->auth->verifyOtp($request->phone_number,$request->otp,$request->name,$request->input('purpose','login'));
        return response()->json(['success'=>true,'message'=>'تم تسجيل الدخول بنجاح.','data'=>['user'=>new UserResource($user),'tokens'=>$tokens]]);
    }
    public function me(Request $request) { return response()->json(['success'=>true,'data'=>new UserResource($request->user())]); }
    public function refresh(Request $request) { $this->auth->revokeCurrent($request->user()); return response()->json(['success'=>true,'message'=>'تم تحديث رموز الدخول.','data'=>['tokens'=>$this->auth->issueTokens($request->user())]]); }
    public function logout(Request $request) { $this->auth->revokeCurrent($request->user()); return response()->json(['success'=>true,'message'=>'تم تسجيل الخروج.']); }
    public function login(Request $request) { $data=$request->validate(['email'=>'nullable|email','phone_number'=>'nullable|string|max:30','password'=>'required|string']); if(empty($data['email'])&&empty($data['phone_number'])) throw ValidationException::withMessages(['email'=>['البريد الإلكتروني أو رقم الجوال مطلوب.']]); list($user,$tokens)=$this->auth->passwordLogin($data['email']??$data['phone_number'],$data['password']); return response()->json(['success'=>true,'data'=>['user'=>new UserResource($user),'tokens'=>$tokens]]); }
    public function register(Request $request) { $data=$request->validate(['name'=>'required|string|max:120','email'=>'nullable|email|unique:users,email','phone_number'=>'nullable|string|max:30|unique:users,phone_number','password'=>'required|string|min:8|confirmed']); if(empty($data['email'])&&empty($data['phone_number'])) throw ValidationException::withMessages(['phone_number'=>['رقم الجوال أو البريد الإلكتروني مطلوب.']]); $data['password']=Hash::make($data['password']); $user=User::create($data); return response()->json(['success'=>true,'message'=>'تم إنشاء الحساب.','data'=>['user'=>new UserResource($user),'tokens'=>$this->auth->issueTokens($user)]],201); }
    public function forgotPassword(OtpRequest $request) { $code=$this->auth->requestOtp($request->phone_number,'forgot_password'); $data=['expires_in'=>300]; if(app()->environment(['local','testing']))$data['debug_otp']=$code; return response()->json(['success'=>true,'message'=>'تم إرسال رمز استعادة كلمة المرور.','data'=>$data]); }
    public function resendOtp(OtpRequest $request) { return $this->requestOtp($request); }
    public function adminLogin(Request $request) { $data=$request->validate(['email'=>'nullable|email','phone_number'=>'nullable|string','password'=>'required|string']); list($user,$tokens)=$this->auth->passwordLogin($data['email']??$data['phone_number'],$data['password']); if((int)$user->role===0)return response()->json(['success'=>false,'message'=>'لا تملك صلاحية الإدارة.'],403); return response()->json(['success'=>true,'data'=>['user'=>new UserResource($user),'tokens'=>$tokens]]); }
    public function resetPassword(Request $request) { $data=$request->validate(['phone_number'=>'required|string','otp'=>'required|digits:6','password'=>'required|string|min:8|confirmed']); $this->auth->verifyOtp($data['phone_number'],$data['otp'],null,'forgot_password'); $user=User::where('phone_number',$data['phone_number'])->firstOrFail(); $user->update(['password'=>Hash::make($data['password'])]); return response()->json(['success'=>true,'message'=>'تم إعادة تعيين كلمة المرور.']); }
    public function changePassword(Request $request) { $data=$request->validate(['current_password'=>'required|string','password'=>'required|string|min:8|confirmed']); if(!Hash::check($data['current_password'],$request->user()->password)) throw ValidationException::withMessages(['current_password'=>['كلمة المرور الحالية غير صحيحة.']]); $request->user()->update(['password'=>Hash::make($data['password'])]); return response()->json(['success'=>true,'message'=>'تم تغيير كلمة المرور.']); }
    public function profile(Request $request) { $data=$request->validate(['name'=>'sometimes|string|max:120','email'=>'sometimes|email|unique:users,email,'.$request->user()->id,'phone_number'=>'sometimes|string|max:30|unique:users,phone_number,'.$request->user()->id,'location_id'=>'nullable|exists:locations,id']); $request->user()->update($data); return response()->json(['success'=>true,'message'=>'تم تحديث الملف الشخصي.','data'=>new UserResource($request->user())]); }
}
