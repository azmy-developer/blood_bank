<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\Client;
use App\Models\BloodType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\models\Token;


class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
//        return $request->all();
        $validator = validator()->make($request->all(), [

            'name' => 'required',
            'email' => 'required|unique:clients,email|email',
            'password' => 'required|confirmed',
            'phone' => 'required|unique:clients,phone',
            'date_of_birth' => 'required',
            'blood_type_id' => 'required',
            'last_donation_date' => 'required|date|after:1/6/2019',
            'city_id' => 'required',

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }
        $request->merge(['password' => bcrypt($request->password)]);
        $client = Client::create($request->all());
        $client->api_token = Str::random(60);
        $client->save();
        return apiResponse('1', 'تم التسجيل بنجاح', [

            'api_token' => $client->api_token,
            'client' => $client

        ]);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $client = Client::where('phone', $request->phone)->first();
        if ($client) {
            if (Hash::check($request->password, $client->password)) {
                return apiResponse('1', 'تم التسجيل بنجاح', [
                    'api_token' => $client->api_token,
                    'client' => $client
                ]);
            } else {
                return apiResponse('0', 'البيانات غير صحيحه');
            }
        } else {
            return apiResponse('0', 'البيانات غير صحيحه');
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function restPassword(Request $request)
    {

        /** @var TYPE_NAME $validator */
        $validator = validator()->make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $client = Client::where('phone', $request->phone)->first();

        if ($client) {
            $code = rand(1111, 9999);

            $update = $client->update(['rest_code_password' => $code]);

            if ($update) {

                Mail::to($client->email)
                    ->bcc("azmy.abuzeid@hotmail.com")
                    ->send(new ResetPassword($client));

                return apiResponse('1', 'برجاء فحص هاتفك', [
                    'code' => $code,
                    'mail_fails' => Mail::failures(),
                    'email' => $client->email,
                ]);
            } else {
                return apiResponse('0', 'حدث خطأ: برجاء المحاوله مره اخري');
            }
        } else {
            return apiResponse('0', 'البيانات غير صحيحه');
        }

    }


    public function newPassword(Request $request)
    {

        /** @var TYPE_NAME $validator */
        $validator = validator()->make($request->all(), [
            'rest_code_password' => 'required',
            'password' => 'required|confirmed',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $client = Client::where('rest_code_password', $request->rest_code_password)->where('rest_code_password', '!=', 0)->where('phone', $request->phone)->first();
        if ($client) {
            $client->password = bcrypt($request->password);
            $client->rest_code_password = null;
            if ($client->save()) {

                return apiResponse('1', 'تم تغيير كلمه المرور بنجاح', [
                    'api_token' => $client->api_token,
                    'client' => $client
                ]);
            } else {
                return apiResponse('0', 'حدث خطأ: برجاء المحاوله مره اخري', []);
            }
        } else {
            return apiResponse('0', 'هذا الكود غير صالح', []);
        }

    }


    public function profile(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'phone' => Rule::unique('clients')->ignore($request->user()->id),
            'email' => Rule::unique('clients')->ignore($request->user()->id),
            'password' => 'confirmed'
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $userLogin = $request->user();
        $userLogin->update($request->all());

        return apiResponse('1', 'تمت التعديل بنجاح', [
            'client' => $request->user()->fresh()->load('City.Governorate','BloodType')
        ]);
//        if ($request->has('password')){
//            $userLogin->password = bcrypt($request->password);
//        }


    }


    public function notificationsSettings(Request $request)
    {

        $rules = [
            'governorates' => 'exists:governorates,id|array',
            'blood_types' => 'exists:blood_types,id|array',
        ];

        // governorates == [1,5,13]
        // blood_types == [1,3]
        $validator = validator()->make($request->all(), $rules);
        if ($validator->fails()) {
            return apiResponse(0, $validator->errors()->first(), $validator->errors());
        }
        if ($request->has('governorates')) {
            //   attach  detach   sync   toggle
            // $arr = [1,2,3,4,7];
            // sync (1,2,4,5,6)
            // 1,2,3,4,5,6,7
            $request->user()->governorates()->sync($request->governorates); // attach - detach() - toggle() - sync
        }
        if ($request->has('blood_types')) {
            $request->user()->bloodtypes()->sync($request->blood_types);
        }
        $data = array(
            'governorates' => $request->user()->governorates()->pluck('governorates.id')->toArray(), // [1,3,4]
            // {name: asda , 'created' : asdasd , id: asdasd}
            // [1,5,13]
            'blood_types' => $request->user()->bloodtypes()->pluck('blood_types.id')->toArray(),
        );
        return apiResponse(1, 'تم  التحديث', $data);


    }



    public function registerToken(Request $request){

        $validator = validator()->make($request->all(), [
            'token' => 'required',
            'platform' =>'required|in:android,ios'

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }


        Token::where('token',$request->token)->delete();
        $token = $request->user()->tokens()->create($request->all());
        return apiResponse('1','تم اضافة التوكين بنجاح',$token);

    }


    public function removeToken(Request $request){

        $validator = validator()->make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }


        Token::where('token',$request->token)->delete();

        return apiResponse('1','تم الحذف بنجاح');

    }

}