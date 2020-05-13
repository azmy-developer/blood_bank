<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloodType;
use App\Models\Category;
use App\Models\City;
use App\Models\Contact;
use App\Models\Governorate;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Request;
use function Psy\debug;

class MainController extends Controller
{

    public function governorates(){

        $Governorates = Governorate::paginate(25);
        if (count($Governorates)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Governorates);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Governorates);
        }
    }

    public function cities(Request $request){

        $Cities = City::where(function ($query) use ($request){
            if ($request->has('governorate_id')){
                $query->where('governorate_id',$request->governorate_id);
            }
        })->get();
        
        if (count($Cities)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Cities);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Cities);
        }
    }

    public function settings(){

        $Settings = Setting::all();
        if (count($Settings)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Settings);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Settings);
        }
    }

    public function blood_types(){

        $BloodType = BloodType::all();

        if (count($BloodType)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$BloodType);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$BloodType);
        }
    }

    public function contact(Request $request){

        $validator = validator()->make($request->all(),[

            'subject' => 'required|min:2',
            'message' => 'required|min:5',

        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }



        $contact = new Contact();
        $contact->subject = $request->subject;
        $contact->message = $request->message;
        $contact->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'contact' => $contact
        ]);


    }

    public function categories(){

        $Categories = Category::paginate(12);

        if (count($Categories)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Categories);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Categories);
        }
    }


    public function posts(Request $request){

            $Posts = Post::where([
                ['title', 'LIKE', '%' . $request->title . '%'],
                ['category_id', 'LIKE', '%' . $request->category_id . '%'],['id', $request->id ],
            ])->get();


        if (count($Posts)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Posts);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Posts);
        }
    }



}
