<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloodType;
use App\Models\Category;
use App\Models\City;
use App\Models\ClientFavPost;
use App\Models\Contact;
use App\Models\DonationRequest;
use App\Models\Governorate;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


    public function postFav(Request $request){

        $validator = validator()->make($request->all(), [
            'post_id' => 'required|exists:posts,id'

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $toggle = $request->user()->favPosts()->toggle($request->post_id);
        if (count($toggle)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$toggle);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$toggle);
        }
    }


    public function listFavClient(Request $request){

//        DB::enableQueryLog();
        $listPostsFav = $request->user()->favPosts()->latest()->paginate(20);
        //        dd(DB::getQueryLog());

        if (count($listPostsFav)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$listPostsFav);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$listPostsFav);
        }
    }


    public function donationRequestCreate(Request $request){

        $validator = validator()->make($request->all(), [
            'patient_name' => 'required',
            'patient_phone' => 'required:digits:11',
            'hospital_name' => 'required',
            'city_id' => 'required|exists:cities,id',
            'blood_type_id' => 'required|exists:blood_types,id',
            'patient_age' => 'required:digits',
            'num_bags' => 'required:digits',
            'hospital_address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'notes' => 'required',

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        //Create Donation Request
        $donationRequrest = $request->user()->donationRequests()->create($request->all());


        //Get client Ids

        $clientIds = $donationRequrest->cities->governorate->clients()->whereHas('bloodType',function ($query) use ($request){
            $query->where('blood_types.id',$request->blood_type_id);
        })->pluck('clients.id')->toArray();

        if (count($clientIds)){
            //create notification on database
            $notif = $donationRequrest->notification()->create([

                'title'   => "يوجد حاله تبرع بالقرب منك",
                'content' => $donationRequrest->bloodType->name ."محتاج متبرع لفصيله"
            ]);

            // attach clients to this notif
            $notif->clients()->attach($clientIds);

            //get tokens

            $tokens = Token::whereIn('client_id',$clientIds)->where('token','!=',null)->pluck('token')->toArray();

            if (count($tokens)){

                $title   = $notif->title;
                $content = $notif->content;
                $data    = [
                  'dontation_request_id' => $donationRequrest->id
                ];

                $send = notifyByFirebase($title,$content,$tokens,$data);
                info("firebase result : " . $send);

            }


        }
            return apiResponse("1" , "تم الاضافه بنجاح والارسال" , $send);
    }

// get donations and get donation by id and update is_read in notification ......

//    public function getDonations(Request $request){
//
//        $dondations = $request->user()->donationRequests()->where(function ($query) use ($request){
//
//            if ($request->has('id')){
//
//                $query->where('donation_requests.id',$request->id);
//
//            }
//
//        })->get();
//
//
//        if ($request->has('id')) {
//            if ($request->user()->notifications()->where('donation_request_id', $request->id)->first()) {
//
//                $dondation = $request->user()->notifications()->where('donation_request_id', $request->id)->first();
//
//                $update =  $request->user()->notifications()->updateExistingPivot($dondation->id, [
//                    'is_read' => 1
//                ]);
//            }
//            dd($update);
//
//        }
//        if (count($dondations)== 0){
//            return apiResponse('0', 'لا يوجد بيانات',$dondations);
//
//        }else{
//            return apiResponse('1', 'تم نجاح العمليه',$dondations);
//        }
//    }

// function end


    public function getDonations(Request $request){

        $donations = $request->user()->donationRequests()->latest()->paginate(10);

        if (count($donations)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$donations);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$donations);
        }
    }


    public function Donation(Request $request){

        $donation = DonationRequest::with('cities','clients','bloodType')->find($request->id);
        if (!$donation){
            return apiResponse('0', 'لا يوجد بيانات');
        }

        if ($request->user()->notifications()->where('donation_request_id',$request->id)->first())
        {

            $don = $request->user()->notifications()->where('donation_request_id',$donation->id)->first();
            $request->user()->notifications()->updateExistingPivot($don->id, [
                'is_read' => 1
            ]);

        }

            return apiResponse('1', 'تم نجاح العمليه',$donation);
    }




    public function listofNotification(Request $request){

//        DB::enableQueryLog();

        $notifications = $request->user()->notifications()->with('donationRequests')->latest()->paginate(20);


        if (count($notifications)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$notifications);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$notifications);
        }

    }


}
