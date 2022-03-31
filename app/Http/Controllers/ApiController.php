<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Service\ApiService;
use App\Http\Controllers\Service\SpecialitiesService;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Msg;
use App\Http\Controllers\Repository\UserRepository;
use App\Http\Controllers\Repository\CrudRepository;
use App\User;
use App\Models\Partners;
use App\Models\ChipData;
use App\Models\Categories;
use App\Models\Page;
use App\Models\Event;
use App\Models\SingleRoomMessage;
use App\Models\Collection;
use App\Models\SubCategories;
use Twilio\Rest\Client;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use App\Http\Controllers\Utility\SendEmails;
use PolygonIO\Rest\Rest;
use App\Http\Controllers\RtcTokenBuilder;

use DateTime;
use DB;

use Validator;
use Route;


//use Illuminate\Routing\Controller as BaseController;

class ApiController extends Controller
{   

     public function userNotify(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            $ApiService = new ApiService();
            $Check = $ApiService->userNotify($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            //print_r($Check); exit;
            if($Check->error_code == 219){
         
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg 
                ];
            }else if($Check->error_code == 302){
                

                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg
                ];
            
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }
    
    public function register(Request $request){
            
        $data = $request->all();
        
           
        if($request->method() == 'POST'){

            
            if(isset($data['phone'])){// Register With Phone Number 
                $rules = array(  'phone'=>'required|digits:10');
                
                //$rest = new Rest('tgnGLgYDUx381oPn76SXt1OePgt6AxPH');

              //  print_r($rest->forex->realTimeCurrencyConversion->get('USD', 'EUR', 10)); exit;

                $validate = Validator::make($data,$rules);

                if($validate->fails() ){
                    
                    $validate_error = $validate->errors()->all();

                    $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

                }else{
                    $ApiService = new ApiService();
                     $query = User::where('phone',@$data['phone'])
                        ->first();
                    if(@$query->user_status == 2){
                         $response = [
                            'code' => 422,
                            'msg'=>  'Your account is deactivated by admin'
                        ];

                    }else{
                   
                        $Check = $ApiService->checkemail_phone($data);  
                        $error_msg = new Msg();
                        $msg =  $error_msg->responseMsg($Check->error_code);
                    

                        if($Check->error_code == 203 ){
                            $response = [
                                'code' => 200,
                                'msg'=>  $msg
                            ];
                        }else{
                            $response = [
                                'code' => $Check->error_code,
                                'msg'=>  $msg
                            ];
                        }
                    }
                }
            }
           
            
            return $response;
        }   
    }
    
    /*****************************************************************************
    * API                   => verify Phone and email                            *
    * Description           => It is used  verify                                *
    * Required Parameters   => code,password,confirm_password                    *
    * Created by            => Sunil                                             *
    *****************************************************************************/
    public function verifyUser(Request $request){

        $data = $request->all();

        if($request->method() == 'POST'){

            $ApiService = new ApiService();
            $Check = $ApiService->verifyUser($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 205 ){
                $ApiService = new ApiService();
                $Check = $ApiService->login($data);
                //print_r($Check); exit;
                $response = [
                    'code' => 200,
                    'msg'=>  $msg,
                    'data' => $Check->data
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   

    }


    /*****************************************************************************
    * API                   => Social Login                                      *
    * Description           => It is used  verify                                *
    * Required Parameters   => facebook_id,google_id,apple_id                    *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
    public function socialLogin(Request $request){
            
        $data = $request->all();
           
        if($request->method() == 'POST'){

            
            if(isset($data['facebook_id'])){// Register With facebook
                $rules = array(  'facebook_id'=>'required');
            }
            if(isset($data['google_id'])){// Register With google 
                $rules = array(  'google_id'=>'required');
            }
            if(isset($data['apple_id'])){// Register With Apple 
                $rules = array(  'apple_id'=>'required');
            }

                $validate = Validator::make($data,$rules);

                if($validate->fails() ){
                    
                    $validate_error = $validate->errors()->all();

                    $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

                }else{
                    $ApiService = new ApiService();
                    $Check = $ApiService->socialLogin($data); 
                    //print_r($Check); exit; 
                    $error_msg = new Msg();
                    $msg =  $error_msg->responseMsg($Check->error_code);
                

                    if($Check->error_code == 200 ){
                        $response = [
                            'code' => 200,
                            'msg'=>  $msg,
                            'data' => $Check->data
                        ];
                    }else{
                        $response = [
                            'code' => $Check->error_code,
                            'msg'=>  $msg
                        ];
                    }

                }
            
           
            
            return $response;
        }   
    }
    
    /*****************************************************************************
      API                   => set Password                                      *
    * Description           => It is to set the ssword                           *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    ******************************************************************************/
    public function resetPassword(Request $request){
       
        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'id'         =>  'required',
                    'password'      =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){

                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->resetPassword($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 638 || $Check->error_code == 645){

                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            
            return $response;
        }   
    }


   

    public function changePassword(Request $request){
        
        $userId= Auth::user()->id; 
        if($request->method() == 'POST'){

            $data = $request->all();
            $rules = array(
                'old_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
            } else {
                try {
                    if ((Hash::check(request('old_password'), Auth::user()->password)) == false) {

                        $arr = array("code" => 400, "msg" => "Check your old password.", "data" => array());
                    } else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
                        $arr = array("code" => 400, "msg" => "Your new password cannot be the same as your current password.", "data" => array());
                    } else {
                        User::where('id', $userId)->update(['password' => Hash::make($data['new_password'])]);
                        $arr = array("code" => 200, "msg" => "Password updated successfully.", "data" => array());
                    }
                } catch (\Exception $ex) {
                    if (isset($ex->errorInfo[2])) {
                        $msg = $ex->errorInfo[2];
                    } else {
                        $msg = $ex->getMessage();
                    }
                    $arr = array("code" => 404, "msg" => $msg, "data" => array());
                }
            }
            return \Response::json($arr);
        }
    }



    /************************************************************************************
    * API                   => Login                                                    *
    * Description           => It is used to login new user                             *
    * Required Parameters   => email,password,device_id,device_type                     *
    * Created by            => Sunil                                                    *
    *************************************************************************************/

    public function login(Request $request){
        $data = $request->all();

        if($request->method() == 'POST'){

            $rules = array(
                    'password'      =>  'required | min:8',
                    'device_id'     =>  'required',
                    'device_type'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->login($data);
                
                    //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 200){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                        'data' => $Check->data
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }


    public function registeremail(Request $request){
            
    	$data = $request->all();
    	   
       
    	if($request->method() == 'POST'){

            $rules = array('email' =>'required|email|max:255|unique:users','password'=>'required | min:8');
            

            $validate = Validator::make($data,$rules);

            if($validate->fails() ){
                
                $validate_error = $validate->errors()->all();

                $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

            }else{
                
                $ApiService = new ApiService();
                $Check = $ApiService->checkemail_phone($data);  
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            

                if($Check->error_code == 203 ){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }

            }
    		
    		return $response;
    	}	
    }


    /***********************************************************************************
    * API                   => ''                                                      *
    * Description           => It is used  verify  the email..                         *
    * Required Parameters   => ''                                                      *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function activation(Request $request){
            //print_r($request->all());die;
            $id = $request->id;
            $code = $request->code;   

            $UserRepostitory   = new UserRepository();
            $getuser = $UserRepostitory->getuserById($id);
            //echo '<pre>'; print_r($getuser); exit;
            if($getuser['id'] == 1){
                $getCode = $getuser['forgot_password_code'];
            }else{
                $getCode = $getuser['activation_code'];
            }
            $endTime = strtotime("+5 minutes",strtotime($getCode));
            $newTime = date('H:i:s',$endTime);
            if($getCode == $request->code){
                $user = $UserRepostitory->update_activation($id);
                if($getuser['id'] == 1){
                    return view('admin/users/reset');
                }else{
                    return view('activations');

                } 
            }else{
                
                return view('activationsfail');
            }   
        }


    /******************************************************************************
    * API                   => ''                                                 *
    * Description           => It is used  verify  the email..                    *
    * Required Parameters   => ''                                                 *
    * Created by            => Sunil                                              *
    *******************************************************************************/

    public function terms(Request $request){
           $result = DB::table('pages')->where('p_status','=',1)->where('id','=',1)->first();
           print_r($result->p_description);
    }   

    public function privacypolicy(Request $request){
           $result = DB::table('pages')->where('p_status','=',1)->where('id','=',2)->first();
           print_r($result->p_description);

    }   

    
    public function aboutus(Request $request){
            echo 'About Us';
    }    
      
  

    /*************************************************************************************
    * API                   => Forgot Password                                           *
    * Description           => It is used send forgot password mail..                    *
    * Required Parameters   => email                                                     *
    * Created by            => Sunil                                                     *
    **************************************************************************************/

    public function forgotPassword(Request $request){
        $data = $request->all();
        if($request->method() == 'POST'){
        
            $ApiService = new ApiService();
            $Check = $ApiService->forgotPassword($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 601){
                $response = [
                    'code' => 200,
                    'msg'=>  $msg
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }



    /********************************************************************************
      API                   => Category list                                        *
    * Description           => It is to get Chip list                               *
    * Required Parameters   => Access Token                                         *
    * Created by            => Sunil                                                *
    *********************************************************************************/

    public function category_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->category_list();
            

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
             
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray(),
                   
                ];
                $category_array = array();
                $category_list = array();
                foreach($responseOld['data']['data'] as $list){
                    //  print_r($list);
                    $category_array['c_id']  =  @$list['c_id'] ? $list['c_id'] : '';
                    $category_array['c_name'] = @$list['c_name'] ? $list['c_name'] : '';
                    $category_array['c_status'] =  @$list['c_status'] ? $list['c_status'] : '';
                    $category_array['c_image'] =  @$list['c_image'] ? URL('/public/images/'.$list['c_image']) : '';
                    
                    array_push($category_list,$category_array);
                }
                //echo '<pre>'; print_r($responseOld['gender']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $category_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    /***************************************************************************************
      API                   => Category list                                                *
    * Description           => It is to get Chip list                                       *
    * Required Parameters   => Access Token                                                 *
    * Created by            => Sunil                                                        *
    ***************************************************************************************/

    public function category_list_old(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->category_list();
            /*$Check_gender = $ApiService->gender_list();
            $Check_race = $ApiService->race_list();
            $Check_religion = $ApiService->religion_list();
            $Check_report = $ApiService->report_list();
            $Check_report = $ApiService->report_list();
            $Check_partner_type = $ApiService->partner_type();
            $Check_region = $ApiService->region();
            */
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
           /* $gender = $Check_gender->data;   
            $race = $Check_race->data;   
            $religion = $Check_religion->data;   
            $report = $Check_report->data;   
            $partner_type = $Check_partner_type->data;   
            $region = $Check_region->data;   */
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray(),
                    /*'gender' =>  $gender->toArray(),  
                    'race' =>  $race->toArray(),  
                    'religion' =>  $religion->toArray(),  
                    'report' =>  $report->toArray(),  
                    'partner_type' =>  $partner_type->toArray(),  
                    'region' =>  $region->toArray(),  */
                ];
                //echo '<pre>'; print_r($responseOld['gender']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $responseOld['data']['data'],
                   /* 'gender'  =>  $responseOld['gender']['data'],
                    'race'  =>  $responseOld['race']['data'],
                    'religion'  =>  $responseOld['religion']['data'],
                    'report'  =>  $responseOld['report']['data'],
                    'partner_type'  =>  $responseOld['partner_type']['data'],
                    'region'  =>  $responseOld['region']['data'],*/
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    /**********************************************************************************
      API                   => Get and update Profile                                 *
    * Description           => It is user for Profile                                 *
    * Required Parameters   =>                                                        *
    * Created by            => Sunil                                                  *
    ***********************************************************************************/
    public function profile(Request $request){
        
        $userId= Auth::user()->id;
        $userId = @$request['userid']?$request['userid']:Auth::user()->id;
        $Is_method  = 0; 
        
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $userId;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }
        }      
        return $response;
    }

    /************************************************************************************
    * API                   => Update Device                                            *
    * Description           => It is user for email                                     *
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function update_device(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'POST'){
            $data = $request;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->update_device($Is_method,$data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        return $response;
    }

    /************************************************************************************
    * API                   => notificationRead                                         *
    * Description           => It is user for email                                     *
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function notificationRead(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
        if($request->method() == 'POST'){
        
            $data = $request->all();
            //print_r($data); exit;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->notificationRead($Is_method,$data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 314){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        return $response;
    }

    /************************************************************************************
    * API                   => notificationRead                                         *
    * Description           => It is user for email                                     *
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function UnreadReadCount(Request $request){
        $userId= Auth::user()->id;
        $Is_method  = 0; 
        if($request->method() == 'POST'){
            $data = $request->all();
            //print_r($userId); exit;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->UnreadReadCount($Is_method,$data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 314){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        return $response;
    }


    /*****************************************************************************
    * API                   => create Post                                       *
    * Description           => It is Use to  create Post                         *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
    public function createPost(Request $request){

        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'description'   =>  'required',
                    'post_type'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->createPost(2, $data);
                
                    //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 218){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                        'data' => $Check->data
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }

    public function deletePost(Request $request)
    {
        if($request->method() == 'DELETE'){
            $rules = array('post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->deletePost($data);
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 302){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
                return $response;
            }    
        }   
    }


    /*****************************************************************************
    * API                   => Commpent Post                                     *
    * Description           => It is Use to  Comment Post                        *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
    public function commentPost(Request $request){

        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'description'   =>  'required',
                    'post_id'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->commentPost(2, $data);
                
                    //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 218){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                        'data' => $Check->data
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }



     /***********************************************************************************
    * API                   => User List                                               *
    * Description           => It is to get User List                                  *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function userList(Request $request){
       
        if($request->method() == 'POST'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->userList($request);
            $error_msg = new Msg();

            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 280){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                 // print_r($Check->data); exit;           
                //echo '<pre>'; print_r($responseOld['data']['data']); exit;
                $user_list['users'] = array();
                foreach($responseOld['data']['data']  as $list){
                    $user_array = array();
                    //echo '<pre>';print_r($list); exit;
                    $user_array['id'] =  @$list['userid'] ? $list['userid'] : '';
                    $user_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $user_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $user_array['location']  =   @$list['location'] ? $list['location'] : '';
                    $check_is_follow  = $UserRepostitory->check_is_follow($list['userid']);
                    $user_array['is_follow']  =   $check_is_follow;
                    array_push($user_list['users'],$user_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                 $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $user_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    public function contact(Request $request){
        if($request->method() == 'POST'){
            $data = $request->all();
            //print_r($data); exit;
            $email = @$data['email'];
            //$phone = @$data['phone'];
            $subject = @$data['subject'];
            $msg = @$data['messsage'];
            $name = @$data['name'];
            $to = 'socialtrade@mailinator.com';
            $SendEmail = new SendEmails();
           // $SendEmail->sendContact($to,$email,$subject,$name,$msg);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg(648);
            $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
                return $response;
        }
    }


    /***********************************************************************************
    * API                   => Create Report                                           *
    * Description           => It is used for creating the report                      * 
    * Required Parameters   =>                                                         *
    * Created by            => Sunil                                                   *
    ************************************************************************************/
    
    public function report(Request $request){
        if($request->method() == 'POST'){
            $data = $request->all();
            $rules = array('post_id' => 'required');
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->report($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($msg); exit;
                if($Check->error_code == 222){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        //'data'  =>  $Check->data  
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    

            return $response;
        }   
    }
    /*****************************************************************************
      API                   => Get and update Profile                            *
    * Description           => It is user for Profile                            *
    * Required Parameters   =>                                                   * 
    * Created by            => Sunil                                             *
    *****************************************************************************/
    public function user_detail(Request $request){
        
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
            //$data = $request->id;
            $data = $request['userid'];
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->user_detail($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }
        return $response;
    }


    public function collectionList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->collectionList($request);
            $error_msg = new Msg();

            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 280){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                 // print_r($Check->data); exit;           
                //echo '<pre>'; print_r($responseOld['data']['data']); exit;
                $user_list = array();
                foreach($responseOld['data']['data']  as $list){
                    $user_array = array();
                    //echo '<pre>';print_r($list); exit;
                    $user_array['id'] =  @$list['id'] ? $list['id'] : '';
                    $user_array['photo']  =   @$list['photo'] ? $list['photo'] : '';
                    $user_array['title']  =   @$list['title'] ? $list['title'] : '';
                    $user_array['author']  =   @$list['author'] ? $list['author'] : '';
                    $user_array['desc']  =   @$list['desc'] ? $list['desc'] : '';
                    $user_array['status']  =   @$list['status'] ? $list['status'] : '';
                    $user_array['type']  =   @$list['type'] ? $list['type'] : '';
                    $user_array['amazon_link']  =   @$list['amazon_link'] ? $list['amazon_link'] : '';
                    $user_array['ebay_link']  =   @$list['ebay_link'] ? $list['ebay_link'] : '';
                    $user_array['wordery']  =   @$list['wordery'] ? $list['wordery'] : '';
                    $user_array['other_link1']  =   @$list['other_link1'] ? $list['other_link1'] : '';
                    $user_array['other_link2']  =   @$list['other_link2'] ? $list['other_link2'] : '';
                    //$check_is_follow  = $UserRepostitory->check_is_follow($list['userid']);
                    //$user_array['is_follow']  =   $check_is_follow;
                    array_push($user_list,$user_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                 $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $user_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    /*****************************************************************************
      API                   => Get and update Profile                            *
    * Description           => It is user for Profile                            *
    * Required Parameters   =>                                                   * 
    * Created by            => Sunil                                             *
    *****************************************************************************/
    public function collectionDetail(Request $request){
        
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
            //$data = $request->id;
            $data = $request['id'];
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->collectionDetail($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }
        return $response;
    }



    /*****************************************************************************
    * API                   => CgroupChat                                        *
    * Description           => It is Use to  groupChat                           *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
    public function groupChat(Request $request){

        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'g_id'   =>  'required',
                    'text'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->groupChat(2, $data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                
                if($Check->error_code == 311){
                    $data = $Check;   
                    $responseOld = [
                        'data'  => $data->data['msg']    
                    ];
                    $partner_array['id']            =   @$responseOld['data']['rm_id'] ? $responseOld['data']['rm_id'] : '';
                    $partner_array['userid']        =   @$responseOld['data']['userid'] ? $responseOld['data']['userid'] : '';
                    $partner_array['picUrl']  =   $responseOld['data']['picUrl'] ? $responseOld['data']['picUrl'] : '';
                    $partner_array['user_name']  =   @$responseOld['data']['username'] ? $responseOld['data']['username'] : '';
                    $partner_array['first_name']  =   @$responseOld['data']['first_name'] ? $responseOld['data']['first_name'] : '';
                    $partner_array['last_name']  =   @$responseOld['data']['last_name'] ? $responseOld['data']['last_name'] : '';
                    $partner_array['is_verified']  =   @$responseOld['data']['is_verified'] ? $responseOld['data']['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$responseOld['data']['user_type'] ? $responseOld['data']['user_type'] : '';
                    $partner_array['text']  =   @$responseOld['data']['text'] ? $responseOld['data']['text'] : '';
                    $partner_array['media_url']  =   @$responseOld['data']['media_url'] ? $responseOld['data']['media_url'] : '';
                    $partner_array['message_type']  =   @$responseOld['data']['message_type'] ? $responseOld['data']['message_type'] : 0;
                    $partner_array['added_date_timestamp']  =   @$responseOld['data']['added_date'] ? strtotime($responseOld['data']['added_date']) :'';
                    //$partgroupChatner_array['added_date']  =  @$responseOld['data']['added_date'] ?  \Carbon\Carbon::createFromTimeStamp(strtotime($responseOld['data']['added_date']))->diffForHumans() : '';
                    $partner_array['added_date']  =  @$responseOld['data']['added_date'] ?  $responseOld['data']['added_date'] : '';
                    //print_r($responseOld); exit; 
                    // $Check = $ApiService->post_detail(1,$data['post_id']);
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                       'data'=>$partner_array
                        
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }


    /*****************************************************************************
    * API                   => CgroupChat                                        *
    * Description           => It is Use to  groupChat                           *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
     public function groupChatNewList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->groupChatNewList($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //echo '<pre>';print_r($responseOld); exit;
               
                $Partner_list['chat'] = array();

                foreach($responseOld['data'] as $list){
                    $partner_array = array();
                        
                    $partner_array['id']            =   @$list['rm_id'] ? $list['rm_id'] : '';
                    $partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
                    $partner_array['text']  =   @$list['text'] ? $list['text'] : '';
                    $partner_array['media_url']  =   @$list['media_url'] ? $list['media_url'] : '';
                    $partner_array['message_type']  =   @$list['message_type'] ? $list['message_type'] : 0;
                     $partner_array['added_date_timestamp']  =   @$list['added_date'] ? strtotime($list['added_date']) :'';
                    $partner_array['added_date']  =   @$list['added_date'] ? @$list['added_date'] :'';
                    //$partner_array['added_date']  =  @$list['added_date'] ?  \Carbon\Carbon::createFromTimeStamp(strtotime($list['added_date']))->diffForHumans() : '';
                   
                    array_push($Partner_list['chat'],$partner_array);
                }
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }



    /***********************************************************************************
    * API                   => groupChatMessageList                                    *
    * Description           => It is to groupChatMessageList                           *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function groupChatMessageList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->groupChatMessageList($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
               
                $Partner_list['chat'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $partner_array = array();
                        
                    $partner_array['id']            =   @$list['rm_id'] ? $list['rm_id'] : '';
                    $partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
                    $partner_array['text']  =   @$list['text'] ? $list['text'] : '';
                    $partner_array['media_url']  =   @$list['media_url'] ? $list['media_url'] : '';
                    $partner_array['message_type']  =   @$list['message_type'] ? $list['message_type'] : 0;
                     $partner_array['added_date_timestamp']  =   @$list['added_date'] ? strtotime($list['added_date']) :'';
                    $partner_array['added_date']  =   @$list['added_date'] ? @$list['added_date'] :'';
                    //$partner_array['added_date']  =  @$list['added_date'] ?  \Carbon\Carbon::createFromTimeStamp(strtotime($list['added_date']))->diffForHumans() : '';
                   
                    array_push($Partner_list['chat'],$partner_array);
                }
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    public function groupChat_t(Request $request){

        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'g_id'   =>  'required',
                    'text'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->groupChat(2, $data);
                
                
                    //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                
                if($Check->error_code == 311){
                    // $Check = $ApiService->post_detail(1,$data['post_id']);
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                        
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }

    
    /*********************************************************************
    * API                   => One 2 one Chat                            *
    * Description           => It is to get Chat                         *
    * Required Parameters   => Access Token                              *
    * Created by            => Sunil                                     *
    **********************************************************************/

    public function chatList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->roomList($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 437){
               //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $Partner_list['room'] = array();
                //echo '<pre>'; print_r($responseOld['data']['data']); exit;
                foreach($responseOld['data']['data'] as $list){
                    //echo '<pre>'; print_r($list); 
                    //$partner_array = array();
                    if($list['sender_id'] != Auth::user()->id){
                        $result = DB::table('users')->where('id','=',$list['sender_id'])->where('user_status','=',1)->get();

                        $checkblock = DB::table('block_users')->where('b_to', Auth::user()->id)->where('b_by', $list['sender_id'])->first();
                        
                    }else{
                        $result = DB::table('users')->where('id','=',$list['receiver_id'])->where('user_status','=',1)->get();
                        
                        $checkblock = DB::table('block_users')->where('b_to', Auth::user()->id)->where('b_by', $list['receiver_id'])->first();

                    }
                    /////////////////////////////////////////////////////////
                    $lastmsg = DB::table('chat_room_msgs')->select('chat_room_msgs.*')
                        ->where('chat_room_msgs.rm_r_id','=',$list['r_id'])
                        ->orderBy('chat_room_msgs.rm_id','DESC')
                        ->first();
                    //echo '<pre>'; print_r($lastmsg).'<br>';   
                    if(!empty($lastmsg->rm_id)){    
                            $partner_array['last_activity']  =   $lastmsg->text;
                    }
                    $partner_array['last_activity_time']=  @$lastmsg->added_date ? $lastmsg->added_date :'';
                    if($lastmsg->sender_id == Auth::user()->id){
                        $partner_array['is_read']=  1;

                    }else{
                        $partner_array['is_read']=  @$lastmsg->is_read;
                    }
                    ////////////////////////////////////////////////////////
                    $partner_array['is_blocked']            =   0;
                    if(isset($checkblock->b_to)){
                        $partner_array['is_blocked']            =   1;  
                    } 
                    //echo '<pre>'; print_r($result[0]->id); exit;
                    $partner_array['r_id']            =   @$list['r_id'] ? $list['r_id'] : '';
                    $partner_array['sender_id']            =   @$lastmsg->sender_id ? $lastmsg->sender_id : '';
                    $partner_array['receiver_id']            =   @$lastmsg->receiver_id ? $lastmsg->receiver_id : '';
                    $partner_array['userid']        =   @$result[0]->id ? $result[0]->id : '';
                    $partner_array['photo']  =   @$result[0]->photo ? $result[0]->photo : '';
                    $partner_array['first_name']  =   @$result[0]->first_name ? $result[0]->first_name : '';
                    $partner_array['is_active_profile']  =   @$result[0]->is_active_profile ? $result[0]->is_active_profile : '';
                    array_push($Partner_list['room'],$partner_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  'User List',
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }
    /*********************************************************************
    * API                   => One 2 one Send Msg                        *
    * Description           => It is to Send Msg                         *
    * Required Parameters   => Access Token                              *
    * Created by            => Sunil                                     *
    **********************************************************************/    
    public function chat(Request $request){

        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'text'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->chat(2, $data);
                
                //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                
                if($Check->error_code == 311){
                    // $Check = $ApiService->post_detail(1,$data['post_id']);
                    $data = $Check;   
                    $responseOld = [
                        'data'  => $data->data['msg']    
                    ];
                    $partner_array['id']            =   @$responseOld['data']['rm_id'] ? $responseOld['data']['rm_id'] : '';
                    $partner_array['userid']        =   @$responseOld['data']['userid'] ? $responseOld['data']['userid'] : '';
                    $partner_array['picUrl']  =   $responseOld['data']['picUrl'] ? $responseOld['data']['picUrl'] : '';
                    $partner_array['user_name']  =   @$responseOld['data']['username'] ? $responseOld['data']['username'] : '';
                    $partner_array['first_name']  =   @$responseOld['data']['first_name'] ? $responseOld['data']['first_name'] : '';
                    $partner_array['last_name']  =   @$responseOld['data']['last_name'] ? $responseOld['data']['last_name'] : '';
                    $partner_array['is_verified']  =   @$responseOld['data']['is_verified'] ? $responseOld['data']['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$responseOld['data']['user_type'] ? $responseOld['data']['user_type'] : '';
                    $partner_array['text']  =   @$responseOld['data']['text'] ? $responseOld['data']['text'] : '';
                    $partner_array['media_url']  =   @$responseOld['data']['media_url'] ? $responseOld['data']['media_url'] : '';
                    $partner_array['message_type']  =   @$responseOld['data']['message_type'] ? $responseOld['data']['message_type'] : 0;
                    $partner_array['added_date_timestamp']  =   @$responseOld['data']['added_date'] ? strtotime($responseOld['data']['added_date']) :'';
                    
                    $partner_array['added_date']  =  $responseOld['data']['added_date'];
                    //$partner_array['added_date']  =  @$responseOld['data']['added_date'] ?  \Carbon\Carbon::createFromTimeStamp(strtotime($responseOld['data']['added_date']))->diffForHumans() : '';
                    //print_r($responseOld); exit; 
                    // $Check = $ApiService->post_detail(1,$data['post_id']);
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                       'data'=>$partner_array
                        
                    ];
                    /*$response = [
                        'code' => 200,
                        'msg'=>  'message sended',
                        
                    ];*/
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }

    /*********************************************************************
    * API                   => One 2 one All Send Msg List               *
    * Description           => It is to All Send Msg                     *
    * Required Parameters   => Access Token                              *
    * Created by            => Sunil                                     *
    **********************************************************************/

    public function ChatMessageList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->ChatMessageList($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
               
                $Partner_list['chat'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $partner_array = array();
                        
                    $partner_array['id']            =   @$list['rm_id'] ? $list['rm_id'] : '';
                    $partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
                    $partner_array['text']  =   @$list['text'] ? $list['text'] : '';
                    $partner_array['media_url']  =   @$list['media_url'] ? $list['media_url'] : '';
                    $partner_array['message_type']  =   @$list['message_type'] ? $list['message_type'] : 0;
                    $partner_array['added_date_timestamp']  =   @$list['added_date'] ? strtotime($list['added_date']) :'';
                    //$partner_array['added_date']  =  @$list['added_date'] ?  \Carbon\Carbon::createFromTimeStamp(strtotime($list['added_date']))->diffForHumans() : '';
                    $partner_array['added_date']  =  @$list['added_date'] ;

                   
                    array_push($Partner_list['chat'],$partner_array);
                }
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

   
    /*********************************************************************
      API                   => Sub Category list                         *
    * Description           => It is to get                              *
    * Required Parameters   => Access Token                              *
    * Created by            => Sunil                                     *
    **********************************************************************/

    public function subcategory_list(Request $request){
       
        if($request->method() == 'GET'){
            $data = $request->all();
            $ApiService = new ApiService();
            $Check = $ApiService->subcategory_list($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $responseOld['data']['data'],
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    /**********************************************************************
      API                   => pendingSubscriptionPlan IOS                *
    * Required Parameters   => Access Token                               *
    * Created by            => Sunil                                      *
    ***********************************************************************/

    public function pendingSubscriptionPlan(Request $request){
         if($request->method() == 'POST'){
            $data = $request->all();
            $userId = Auth::user()->id;
            $ApiService = new ApiService();
            $Check = $ApiService->pendingSubscriptionPlan($data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            //$data = $Check->data;   
            if($Check->error_code == 221){
                /*$responseOld = [
                    'data'  => $data->toArray()    
                ];*/
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }  
    }

    /**********************************************************************
      API                   => pendingSubscriptionPlan IOS                *
    * Required Parameters   => Access Token                               *
    * Created by            => Sunil                                      *
    ***********************************************************************/

    public function cronJobForSubscreption(Request $request){
         if($request->method() == 'GET'){
            $data = $request->all();
            //$userId = Auth::user()->id;
            $ApiService = new ApiService();
            $Check = $ApiService->cronJobForSubscreption();

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            if($Check->error_code == 221){
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }  
    }

    /**********************************************************************
      API                   => pendingSubscriptionPlan IOS                *
    * Required Parameters   => Access Token                               *
    * Created by            => Sunil                                      *
    ***********************************************************************/


    public function androidSubscreption(Request $request){
         if($request->method() == 'POST'){
            $data = $request->all();
            $userId = Auth::user()->id;
            $ApiService = new ApiService();
            $Check = $ApiService->androidSubscreption($data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            //$data = $Check->data;   
            if($Check->error_code == 221){
                /*$responseOld = [
                    'data'  => $data->toArray()    
                ];*/
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }  
    }

    /**********************************************************************
      API                   => pendingSubscriptionPlan IOS                *
    * Required Parameters   => Access Token                               *
    * Created by            => Sunil                                      *
    ***********************************************************************/

    public function requestVerification(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->requestVerification($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      



        
        return $response;
    }






    /**********************************************************************
      API                   => pendingSubscriptionPlan IOS                *
    * Required Parameters   => Access Token                               *
    * Created by            => Sunil                                      *
    ***********************************************************************/

        public function visibility(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        
        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->visibilty_profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    //'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      



        
        return $response;
    }



    /*******************************************************************
    * API                   => Home Page Post list                     *
    * Description           => It is to get Post list                  *
    * Required Parameters   => Access Token                            *
    * Created by            => Sunil                                   *
    ********************************************************************/

    public function post_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->post_list($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $Partner_list['post'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $partner_array = array();
                    $repost  = array();
                    $postid = @$list['repost_id'] ? $list['repost_id'] : $list['id'];

                    
                    $like_count  = $UserRepostitory->like_count($postid);
                    $favourite_count  = $UserRepostitory->favourite_count($postid);
                    $comment_count  = $UserRepostitory->comment_count($postid);
                    $is_my_like = $UserRepostitory->my_like_count($postid,Auth::user()->id);      
                    $is_my_favourite = $UserRepostitory->is_my_favourite($postid,Auth::user()->id);   

                    $is_my_bookmark = $UserRepostitory->is_my_bookmark($postid,Auth::user()->id);         
                    if($list['post_type'] == 3){
                        $total_vote_count = $UserRepostitory->total_vote_count($postid); 
                        $vote_count_per = $UserRepostitory->vote_count($postid) ; 
                       // print_r($vote_count_per); exit;
                    }else{
                        $total_vote_count = 0; 
                        $vote_count_per = 0 ; 

                    }
                    
                    $partner_array['id']            =   @$list['id'] ? $list['id'] : '';
                    $partner_array['original_id']   =   @$list['id'] ? $list['id'] :''; 
                    $partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
                    $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
                    $partner_array['post_data'] = array();
                    $partner_array['post_data']['imgUrl']  =  array();
                    $photo_list =  $UserRepostitory->get_photo_list($list['id']);
                    $partner_array['post_data']['imgUrl']  = $photo_list;
                    $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
                    $partner_array['post_data']['like_count']  =   $like_count;

                    $partner_array['post_data']['is_liked'] = $is_my_like;
                    
                    $partner_array['post_data']['is_favorited']  =  $is_my_favourite;
                    $partner_array['post_data']['is_my_bookmark']  =  $is_my_bookmark;

                    
                    $partner_array['post_data']['favourite_count'] = $favourite_count;
                    $partner_array['post_data']['comment_count']  =   @$comment_count;

                 
                    $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;

                 
                    $partner_array['post_data']['total_votes']  =   $total_vote_count;
                    array_push($Partner_list['post'],$partner_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    


    /******************************************************************
      API                   => Get  post_detail                       *
    * Description           => It is user for post_detail             *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/
    public function post_detail(Request $request){
        
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $request['post_id'];
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->post_detail($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 213    ){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }
        return $response;
    }

    /******************************************************************
      API                   => Get  bookmarkList                      *
    * Description           => It is user for Bookmark list           *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/

    public function bookmarkList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->bookmark_list($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $Partner_list['post'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $partner_array = array();
                    $repost  = array();
                    $postid = @$list['repost_id'] ? $list['repost_id'] : $list['id'];

                    
                    $like_count  = $UserRepostitory->like_count($postid);
                    $favourite_count  = $UserRepostitory->favourite_count($postid);
                    $comment_count  = $UserRepostitory->comment_count($postid);
                    $is_my_like = $UserRepostitory->my_like_count($postid,Auth::user()->id);      
                    $is_my_favourite = $UserRepostitory->is_my_favourite($postid,Auth::user()->id);      
                    if($list['post_type'] == 3){
                        $total_vote_count = $UserRepostitory->total_vote_count($postid); 
                        $vote_count_per = $UserRepostitory->vote_count($postid) ; 
                       // print_r($vote_count_per); exit;
                    }else{
                        $total_vote_count = 0; 
                        $vote_count_per = 0 ; 

                    }
                    
                    $partner_array['id']            =   @$list['id'] ? $list['id'] : '';
                    $partner_array['original_id']   =   @$list['id'] ? $list['id'] :''; 
                    $partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
                    $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
                    $partner_array['post_data'] = array();
                    $partner_array['post_data']['imgUrl']  =  array();
                    $photo_list =  $UserRepostitory->get_photo_list($list['id']);
                    $partner_array['post_data']['imgUrl']  = $photo_list;
                    $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
                    $partner_array['post_data']['like_count']  =   $like_count;

                    $partner_array['post_data']['is_liked'] = $is_my_like;
                    
                    $partner_array['post_data']['is_favorited']  =  $is_my_favourite;

                    
                    $partner_array['post_data']['favourite_count'] = $favourite_count;
                    $partner_array['post_data']['comment_count']  =   @$comment_count;

                 
                    $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;

                 
                    $partner_array['post_data']['total_votes']  =   $total_vote_count;
                    array_push($Partner_list['post'],$partner_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    /******************************************************************
      API                   => Get  Profile                           *
    * Description           => It is user for Profile                 *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/

    public function profile1(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $userId;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      

        return $response;
    }

    /******************************************************************
      API                   => Get  Profile                           *
    * Description           => It is user for Profile                 *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/
    
    public function gallery(Request $request){
        $Is_method = 0;
        if($request->method() == 'GET'){
        
            $Is_method = 1;

            $rules = array('p_u_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->gallery($Is_method,$data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 218){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data 
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }
        }    


        if($request->method() == 'POST'){


            $Is_method = 2;
            $rules = array('p_photo' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){      

                $validate_error = $validate->errors()->all();
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->gallery($Is_method,$data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 218){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data 
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }  


        
        if($request->method() == 'DELETE'){


            $Is_method = 3;
            $rules = array('p_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->gallery($Is_method,$data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 214){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }  

        return $response;
    
    }


    /******************************************************************
      API                   => Get  Profile                           *
    * Description           => It is user for Profile                 *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/
    
    public function make_default(Request $request){
         if($request->method() == 'POST'){
            $rules = array('p_id' => 'required','is_default' =>'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){      

                $validate_error = $validate->errors()->all();
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->mark_default($data);
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 646){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data 
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }   

        return $response;        
        
    }



    /******************************************************************
      API                   => Get  Profile                           *
    * Description           => It is user for Profile                 *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/
    public function match(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
            $data = $request;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->matchFind($Is_method,$data,$userId);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }elseif($Check->error_code == 304){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      

        if($request->method() == 'DELETE'){
            $Is_method = 3;
            $rules = array('id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->matchFind($Is_method,$data,$userId);

                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 215){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }  


        
        return $response;
    }

    

    
    /******************************************************************
      API                   => Like Post                              *
    * Description           => It is user for Postlike                *
    * Required Parameters   =>                                        *
    * Created by            => Sunil                                  *
    *******************************************************************/
    public function like(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->like($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 219){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }


    /************************************************************************************
    * API                   => Create BookMark post                                     *
    * Description           => It is used for BookMark the post                         * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function bookmark(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->bookmark($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 223){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }


    /************************************************************************************
    * API                   => Create favourite post                                    *
    * Description           => It is used for favourite post                            * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function favourite(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->favourite($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 219){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }    
    }



     /************************************************************************************
    * API                   => Create follow/unfollow                                   *
    * Description           => It is used for follow/unfollow  the post                 * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function follow(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('user_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->follow($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 219){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }


    public function followUser($data){
        $model      = "App\Models\Follow";  
        $post_type = @$data['post_type'];
        $userId= Auth::user()->id;
        $Is_method  = 0; 
        $query = $model::query();
        if(isset($post_type)){
            //echo $selected_date ; exit;
            $query =$query->where('post_type','=',@$post_type);
        }

        $query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','follows.*')
                ->where('follows.follow_by',$userId)
                ->leftjoin('users','follows.user_id','users.id')
                ->orderBy('users.first_name', 'ASC')
                ->paginate(100,['*'],'page_no');

        $query->total_count = $model::where('follows.follow_by',$userId)
                ->count();
        $users = $query;
        return $users;
    }
 
    /************************************************************************************
    * API                   => Create Like comment                                      *
    * Description           => It is used for liked the comment                         * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function comment_like(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('c_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->comment_like($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 219){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }


     /************************************************************************************
    * API                   => vote on post                                             *
    * Description           => It is used for vote on post                              * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function vote(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('v_option' => 'required', 'v_post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->vote($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 219){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }


    /***********************************************************************************
    * API                   => notificationList                                        *
    * Description           => It is to get notificationList                           *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function notificationList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->notificationList($request);
            $error_msg = new Msg();

            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 277){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                    //print_r($Check->data); exit;           
                //print_r($Check); exit;
                $notification_list['notification'] = array();
                foreach($responseOld['data']['data']  as $list){
                    $notification_array = array();
                    $getref = json_decode($list['n_data'], true);
                    //echo '<pre>';print_r($list); exit;
                    $notification_array['id'] =  @$list['n_id'] ? $list['n_id'] : '';
                    $notification_array['sender_id']            =   @$list['n_sender_id'] ? $list['n_sender_id'] : '';
                    $notification_array['userid']        =   @$list['n_u_id'] ? $list['n_u_id'] : '';
                    $notification_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $notification_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $notification_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $notification_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $notification_array['n_type']  =   @$list['n_type'] ? $list['n_type'] : '';
                    $notification_array['ref_id']  =   @$getref['relData']['ref_id'] ? $getref['relData']['ref_id'] : 0;
                    
                    $notification_array['message']  =   @$list['n_message'] ? $list['n_message'] : '';
                    $notification_array['status']  =   @$list['n_status'] ? $list['n_status'] : 0;
                    $notification_array['added_date']  =   @$list['n_added_date'] ? $list['n_added_date'] : '';
                    //$notification_list[] =$notification_array;
                    array_push($notification_list['notification'],$notification_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                 $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $notification_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    /***************************************************************************************
    * API                   => Patient List                                              *
    * Description           => It is used for getting patient list                        *        
    * Required Parameters   =>                                                             *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/
    
    public function patient_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->patient_list();

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 635){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    
    /***************************************************************************************
    * API                   => subscriptionsList                                           *
    * Description           => It is used for subscriptionsList                            * 
    * Required Parameters   =>                                                             *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/
    
    public function subscriptionsList(Request $request){
        //send push notification
        $sender_name = 'sunil';
        $message =  $sender_name." find as match.";
        $datass['userid'] = 66;
        $datass['name'] = 'sunil';
        $datass['n_type'] = 2;
        $datass['noti_type'] = "2";
        $datass['message'] = $message;
        $notify = array ();
        $notify['receiver_id'] = 83;
        $notify['relData'] = $datass;
        $notify['message'] = $message;

        $UserRepostitory = new UserRepository();
        $test =  $UserRepostitory->sendPushNotification($notify);  exit;
        if($request->method() == 'GET'){
            //$data = $request;
            $ApiService = new ApiService();
            $Check = $ApiService->subscriptionsList();
            //print_r($Check); exit;
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 220){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }





    /***************************************************************************************
      API                   => Chip Register                                                *
    * Description           => It is to Register Chip                                                   *
    * Required Parameters   => Access Token                                                             *
    * Created by            => Sunil                                                        *
    ***************************************************************************************/

    public function chip(Request $request){

        if($request->method() == 'POST'){

            $data = $request->all();
            $rules = array('chip_name' =>'required|max:255','unique_id'=>'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails() ){
                
                $validate_error = $validate->errors()->all();

                $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->chip($data);
                //print_r($Check->data->id);exit;

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 210){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  => $Check->data->id
                        
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            
            return $response;
        }

    }

    

    /*********************************************************************
      API                   => check_username                            *
    * Description           => It is user for username                   *
    * Required Parameters   =>                                           *
    * Created by            => Sunil                                     *
    **********************************************************************/
    public function check_username(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
            $data = $request;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->check_username($Is_method,$data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        return $response;
    }

    /*********************************************************************
      API                   => get_media_list                            *
    * Description           => It is user for get_media_list             *
    * Required Parameters   =>                                           *
    * Created by            => Sunil                                     *
    **********************************************************************/
    public function get_media_list(Request $request){
       
        if($request->method() == 'GET'){
            $ApiService = new ApiService();
            $data = $request->all();
            //echo '<pre>'; print_r($data);  exit;
            $Check = $ApiService->get_media_list($data);
            //echo 'dasd'; exit;
            //print_r($Check); exit;
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   


            if($Check->error_code == 641){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $Partner_list['photo'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $photo_array = array();
                    
                    $photo_array['post_id']  =  @$list['post_id'] ? $list['post_id'] : '';
                    $photo_array['photo_id']  =  @$list['p_id'] ? $list['p_id'] : '';
                    $photo_array['thumb']  =  @$list['thumb'] ? $list['thumb'] : '';
                    $photo_array['url']  =  @$list['url'] ? $list['url'] : '';
                    $photo_array['p_u_id']  =  @$list['p_u_id'] ? $list['p_u_id'] : '';

                    array_push($Partner_list['photo'],$photo_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    /***************************************************************************************
      API                   => chat_user for test                                          *
    * Description           => It is user for chat_user                                  *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/
    public function chat_user(Request $request){
        $userId = Auth::user()->id;
        // Find your Account SID and Auth Token at twilio.com/console
        // and set the environment variables. See http://twil.io/secure
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);
        //print_r($twilio); exit;
        $user = $twilio->conversations->v1->users
                                          ->create($userId);

        //print_r($user); exit;
        $sid = $user->sid;
        $ApiService = new ApiService();
        $Check = $ApiService->chat_user_sid_update($sid,$userId);
    }


    /***************************************************************************************
      API                   => Chat_token                                                 *
    * Description           => It is user for test_twilio                                 *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/
    public function chat_token(Request $request){
        
        // Required for all Twilio access tokens
        // Required for Chat grant
        $data = $request; 
        //print_r($data['device_type']); exit; 
        $twilioAccountSid = getenv("TWILIO_ACCOUNT_SID");
        $twilioApiKey = getenv("TWILIO_APIKEY");
        $twilioApiSecret = getenv("TWILIO_APISECRET");
        $userId = Auth::user()->id;
        // Required for Chat grant
        $serviceSid = getenv("TWILIO_SERVICESID");//Default
        $chat_env = getenv("CHAT_ENV");//Default
        // choose a random username for the connecting user
        $identity = $chat_env.''.$userId ;//$data['sid'];

        // Create access token, which we will serialize and send to the client
        $token = new AccessToken(
            $twilioAccountSid,
            $twilioApiKey,
            $twilioApiSecret,
            3600,
            $identity
        );
        //print_r($token); exit;
        // Create Chat grant
        $chatGrant = new ChatGrant();
        $chatGrant->setServiceSid($serviceSid);
        if($data['device_type'] == 0){// APNS
            $chatGrant->setPushCredentialSid('CR6d5f79c62f75ff86e03453027a6662dd');
        }else{//FCM
            $chatGrant->setPushCredentialSid('CR159af2c172372ea4bf411d8e465104c5');
        }
       
        // Add grant to token
        $token->addGrant($chatGrant);

        // render token to string
        $user_token = $token->toJWT();

        
        $response = [
            'code' => 200,
            'msg'=>  'Token created succesfully',
            'token'=> $user_token
        ];

        return $response;
    }


    public function addchatuser(){
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

       /* $message = $twilio->conversations->v1->conversations("CHc1bafe6eab554f01ba755b350fb450e4")
                                     ->messages
                                     ->create([
                                                  "author" => "Dev3",
                                                  "body" => "Ahoy there!"
                                              ]
                                     );*/
          
        //if($data->EventType == 'onConversationAdded'){
            //fwrite($file,"\n ". print_r('sunil2', true));
            // fwrite($file,"\n ". print_r($data->EventType, true));
            // $receiver_id = getenv("CHAT_ENV").''.$data->Attributes; 
           //echo $receiver_id = getenv("CHAT_ENV").'3'; 
        $participant = $twilio->conversations->v1->conversations("CHc779b7e4ed3b44c29bad092083e68d61")
                 ->participants
                 ->create([
                            "identity" => "Dev41"
                          ]
                 );
                $datanew =  json_decode ($participant ,true );                          
        print($datanew);
            //print($participant->sid);
        //}

    }

    public function chat_post_event(Request $request){
        // Find your Account SID and Auth Token at twilio.com/console
        // and set the environment variables. See http://twil.io/secure
        $data = $request->all();  
        //if(isset($data)){

            //$datanew =  json_encode ( $data ,true );
            //$fileName = date('Ymd').'chat_post_event.txt';
            // prd($fileName);
            //$file = fopen($fileName,'a');
            $file = fopen('chat_pre_event.txt','a+');
            
            fwrite($file,"\n ". print_r('sunil1', true));
            //fwrite($file,"\n ". print_r($datanew, true));
            fwrite($file,"\n ". print_r($data, true));
            if(!empty($_FILES))
            {
            
                fwrite($file,"\n ".print_r($_FILES, true));
                fclose($file);
            
            }

            if($data['EventType'] == 'onConversationAdded'){
                $sid = getenv("TWILIO_ACCOUNT_SID");
                $token = getenv("TWILIO_AUTH_TOKEN");
                $twilio = new Client($sid, $token);
                fwrite($file,"\n ". print_r('sunil2', true));
                $ConversationSid = $data['ConversationSid'];
                $Attributes = $data['Attributes'];
                $receiver_id = getenv("CHAT_ENV").''.$data['Attributes']; 
                $participant = $twilio->conversations->v1->conversations($ConversationSid)
                     ->participants
                     ->create([
                                "identity" => $receiver_id
                              ]
                     );

                //print($participant->sid);
            }

           
            fwrite($file,"\n ". print_r('sunil6', true));
                
            /////////
        //}
    }

    public function chat_pre_event(Request $request){
        $data = $request->all();   
        //if(isset($data)){

            $datanew =  json_encode ( $data ,true );

            if($datanew['EventType'] == 'onConversationAdded'){


            }
            $file = fopen('chat_pre_event.txt','a+');
            
            fwrite($file,"\n ". print_r($datanew, true));
            fwrite($file,"\n ". print_r($datanew->EventType, true));
            fwrite($file,"\n ". print_r($datanew->Attributes, true));
            fwrite($file,"\n ". print_r('sunil', true));
            if(!empty($_FILES))
            {
            
                fwrite($file,"\n ".print_r($_FILES, true));
                fclose($file);
            
            }
            if($datanew->EventType == 'onMessageAdded'){
                 fwrite($file,"\n ". print_r($datanew->EventType, true));
                $sid = getenv("TWILIO_ACCOUNT_SID");
                $token = getenv("TWILIO_AUTH_TOKEN");
                $twilio = new Client($sid, $token);
                $receiver_id = getenv("CHAT_ENV").''.$datanew->Attributes; 
                $participant = $twilio->conversations->v1->conversations($datanew->ConversationSid)
                     ->participants
                     ->create([
                                "identity" => $receiver_id
                              ]
                     );

                //print($participant->sid);
            }
            /////////
        //}
    }

    public function chat_update_uername(Request $request){
        $data = $request->all();   
             //if(isset($data)){
       
        // Find your Account SID and Auth Token at twilio.com/console
        // and set the environment variables. See http://twil.io/secure
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

        $user = $twilio->conversations->v1->users("US6808d12f805c493b8572e02f81f03153")
          ->update([
                       "friendlyName" => "techno new name",
                   ]
          );

        //print($user->friendlyName);

       
                //print($participant->sid);
           
            /////////
        //}
    }


    public function check_pending(){
        $date = new DateTime;
        //echo $test = $date->format('Y-m-d H:i:s').'<br>';
        $date->modify('-1 minutes');
        $formatted_date = $date->format('Y-m-d H:i:s');

        $result = DB::table('pending_matches')->where('is_pending','=',1)->where('is_notify','=',0)->where('added_date','<',$formatted_date)->get();
        if(!empty($result)){
            foreach ($result as $resultkey => $resultvalue) {
                # code...
                DB::table('pending_matches')->where('id', $resultvalue->id)
                ->update([
                   'is_notify' => 1,
                   ]);
                $message =  "your are not found any match in last fifteen minutes.";
                $data['userid'] = $resultvalue->sender_id;
                $data['message'] = $message;
                $data['n_type'] = 3;
                $notify = array ();
                $notify['receiver_id'] = $resultvalue->sender_id;
                $notify['relData'] = $data;
                $notify['message'] = $message;
                echo print_r($notify);
                $UserRepostitory   = new UserRepository();
                $test =  $UserRepostitory->sendPushNotification($notify); 
                         echo '<pre>'; print_r($resultvalue->sender_id);
            }
        }
    }

    
    




  

  
    public function answer_delete(Request $request)
    {
         if($request->method() == 'DELETE'){
            $rules = array('id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->answer_delete($data);
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 302){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
                return $response;
            }    
        }   
    }

    /******************************************************************************
      API                   => Logout                                             *
    * Description           => It is user for Logout                              *
    * Required Parameters   =>                                                    *
    * Created by            => Sunil                                              *
    *******************************************************************************/


    public function logout(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->logout();

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 642){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    public function deleteAccount(Request $request)
    {
        if($request->method() == 'DELETE'){
               
                $ApiService = new ApiService();
                $Check = $ApiService->deleteAccount($request);
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 447){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
                return $response;
            
        }   
    }


    /***********************************************************************************
    * API                   => Create Block                                            *
    * Description           => It is used for creating the block                       * 
    * Required Parameters   =>                                                         *
    * Created by            => Sunil                                                   *
    ************************************************************************************/
    
    public function block(Request $request){
        if($request->method() == 'GET'){
            $data = $request->all();
            $rules = array('other_user' => 'required');
            $validate = Validator::make($data,$rules);
            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->block($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($msg); exit;
                if($Check->error_code == 312){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        //'data'  =>  $Check->data  
                    ];
                }elseif($Check->error_code == 313){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        //'data'  =>  $Check->data  
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    

            return $response;
        }   
    }

    /**********************************************************************
    * API                   => Bolck user list                            *
    * Description           => It is to get Bolck user list               *
    * Required Parameters   => Access Token                               *
    * Created by            => Sunil                                      *
    ***********************************************************************/

    public function block_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->block_list();
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $partner_array = array();
                $Partner_list['block_list'] = array();

                foreach($responseOld['data']['data'] as $list){
                    //print_r($list);
                    $partner_array['userid']=@$list['id'] ? $list['id'] : '';
                    /*$partner_array['status']=@$list['status'] ? $list['status'] : '';
                    $partner_array['chatroom']=@$list['room'] ? $list['room'] : '';
                    */$partner_array['first_name']=@$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['photo']=@$list['photo'] ? $list['photo'] : '';
                    
                    array_push($Partner_list['block_list'],$partner_array);
                }
                $Partner_list['current_page'] = $responseOld['data']['current_page'];
                $Partner_list['first_page_url'] = $responseOld['data']['first_page_url'];
                $Partner_list['from'] = $responseOld['data']['from']?$responseOld['data']['from']:0;
                $Partner_list['last_page'] = $responseOld['data']['last_page'];
                $Partner_list['last_page_url'] = $responseOld['data']['last_page_url'];
                $Partner_list['per_page'] = $responseOld['data']['per_page'];
                $Partner_list['to'] = $responseOld['data']['to']?$responseOld['data']['to']:0;
                $Partner_list['total'] = $responseOld['data']['total']?$responseOld['data']['total']:0;
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }
    public function block_list12(Request $request){
       
        if($request->method() == 'GET'){
            $ApiService = new ApiService();
            $Check = $ApiService->block_list($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $partner_array = array();
                $Partner_list['friend'] = array();

                foreach($responseOld['data']['data'] as $list){
                    //print_r($list);
                    $partner_array['userid']            =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['status']            =   @$list['status'] ? $list['status'] : '';
                    $partner_array['chatroom']            =   @$list['chatroom'] ? $list['chatroom'] : '';
                    $partner_array['username']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['unique_id']  =   @$list['unique_id'] ? $list['unique_id'] : '';
                    
                    array_push($Partner_list['friend'],$partner_array);
                }
                $Partner_list['current_page'] = $responseOld['data']['current_page'];
                $Partner_list['first_page_url'] = $responseOld['data']['first_page_url'];
                $Partner_list['from'] = $responseOld['data']['from']?$responseOld['data']['from']:0;
                $Partner_list['last_page'] = $responseOld['data']['last_page'];
                $Partner_list['last_page_url'] = $responseOld['data']['last_page_url'];
                $Partner_list['per_page'] = $responseOld['data']['per_page'];
                $Partner_list['to'] = $responseOld['data']['to']?$responseOld['data']['to']:0;
                $Partner_list['total'] = $responseOld['data']['total']?$responseOld['data']['total']:0;
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    public function agoraToken(Request $request){
        $appID = "4544fc186fcc4ae79e2d3ddf6c9ce4c0";
        $appCertificate = "e7786275285a42d3aad478cb91856f3d";
        //$channelName = "e7786275285a42d3aad478cb91856f3d";
        $channelName = $request['channel_name'];
        $userId= Auth::user()->id; 
        $uid = 0; //    $userId;
        $uidStr = "2882341273";
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $tokenuId = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
       // echo 'Token with int uid: ' . $tokenuId . PHP_EOL;
       // echo $tokenuId;
        $tokenAccountId = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $uidStr, $role, $privilegeExpiredTs);
                //echo 'Token with user account: ' . $tokenAccountId . PHP_EOL;

        $response = [
            'code'  =>  200,
            'msg'   =>  'Token created',
            'tokenuId'  =>  $tokenuId,
            'tokenAccountId' => $tokenAccountId,
        ];
        return $response;
    }

    /*****************************************************************************
    * API                   => create Event                                      *
    * Description           => It is Use to  create Event                        *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
    public function createEvent(Request $request){

        $data = $request->all();
        if($request->method() == 'POST'){

                    //'g_title'   => 'required|unique:groups,g_title',
            $rules = array(
                    'e_channel'   => 'required',
                    'e_token'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->createEvent(2, $data);
                
                // print_r($Check->data['data']); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 223){
                    $response = [
                        'code' => 200,
                        'msg'=>  'Event Created',
                        'data' =>$Check->data['data']
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }

    /********************************************************************************
      API                   => Event list                                           *
    * Description           => It is to get Eventlist                               *
    * Required Parameters   => Access Token                                         *
    * Created by            => Sunil                                                *
    *********************************************************************************/

    public function eventList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->eventList();
            

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
             
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray(),
                   
                ];
                $event_array = array();
                $event_list = array();
                foreach($responseOld['data']['data'] as $list){
                      //print_r($list);
                    $event_array['e_id']  =  @$list['e_id'] ? $list['e_id'] : '';
                    $event_array['e_channel'] = @$list['e_channel'] ? $list['e_channel'] : '';
                    $event_array['e_token'] = @$list['e_token'] ? $list['e_token'] : '';
                    $event_array['e_u_id'] = @$list['e_u_id'] ? $list['e_u_id'] : '';
                    $event_array['e_status'] =  @$list['e_status'] ? $list['e_status'] : '';
                    $event_array['first_name'] =  @$list['first_name'] ? $list['first_name'] : '';
                    $event_array['photo'] =  @$list['photo'] ? $list['photo'] : '';
                    
                    array_push($event_list,$event_array);
                }
                //echo '<pre>'; print_r($responseOld['gender']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  'Event List',
                    'data'  =>  $event_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    /********************************************************************************
      API                   => End Event                                            *
    * Description           => It is to get EndEvent                                *
    * Required Parameters   => Access Token                                         *
    * Created by            => Sunil                                                *
    *********************************************************************************/
    public function endEvent(Request $request){
        
        $userId= Auth::user()->id; 

        if($request->method() == 'GET'){

            $data = $request->all();
            $rules = array(
                'e_id' => 'required',
           
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
            } else {
                try {
                  
                    $event = Event::where('e_status',1)
                    ->where('e_id')
                    ->leftjoin('users','events.e_u_id','users.id')
                    ->paginate(100,['*'],'page_no');
                        
                        Event::where('e_id', $data['e_id'])
                            ->where('e_u_id', $userId)
                            ->update(['e_status' => 2]);
                        

                        $arr = array("code" => 200, "msg" => "Event ended.");
                    
                } catch (\Exception $ex) {
                    if (isset($ex->errorInfo[2])) {
                        $msg = $ex->errorInfo[2];
                    } else {
                        $msg = $ex->getMessage();
                    }
                    $arr = array("code" => 404, "msg" => $msg);
                }
            }
            return \Response::json($arr);
        }
    }

     public function cronJobForEndEvent(Request $request){
        echo $startTime = date ( 'Y-m-d H:i:s' );
        echo '<br>';
        echo $today = date('Y-m-d H:i:s', strtotime('+31 minutes', strtotime($startTime)));
        //echo $today = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        $checkclose = Event::where('e_status',1)
            //->whereDate('added_date','>', date($today))
            ->get();
        //echo '<pre>'; print_r($checkclose); exit;    
        
        

        if(!empty($checkclose)){
            $UserRepostitory   = new UserRepository();
            foreach ($checkclose as $checkclosekey => $checkclosevalue) {
                //echo '<pre>'; print_r($checkclosevalue->added_date); //exit;
                echo 'to = '; 
                echo $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $checkclosevalue->added_date);
                echo '</br>';
                echo 'form'; 
                echo $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $startTime);


                  echo '--';
                  echo $diff_in_minutes = $to->diffInMinutes($from);
                if($diff_in_minutes >  30){

                    $updatestatu = Event::where('e_id', $checkclosevalue->e_id)
                    ->update(['e_status' => 2]); 
                    echo '<br>'.$checkclosevalue->id;
                    $sender = 1;
                    $message ="has ended due cross 30 minutes";
                    $n_type = 10;
                    $ref_id = $checkclosevalue->e_id;//event_id
                    $push_type = 1; //1 for normal 2 for seclient 
                    // get follower list and send notification
                    //echo 'dsad'; exit;      
                    $userArr = $checkclosevalue->e_u_id;
                    $UserRepostitory->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
                }
            }
        }    
    }


    public function cronJobForEndEvent__old(Request $request){
        echo $startTime = date ( 'Y-m-d H:i:s' );
        echo '<br>';
        echo $today = date('Y-m-d H:i:s', strtotime('+31 minutes', strtotime($startTime)));
        //echo $today = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        $checkclose = Event::where('e_status',1)
            //->whereDate('added_date','>', date($today))
            ->get();
        //echo '<pre>'; print_r($checkclose); exit;    
        
        

        if(!empty($checkclose)){
            $UserRepostitory   = new UserRepository();
            foreach ($checkclose as $checkclosekey => $checkclosevalue) {
                //echo '<pre>'; print_r($checkclosevalue->added_date); //exit;
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $checkclosevalue->added_date);

                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $today);


                  echo '--';
                  echo $diff_in_minutes = $to->diffInMinutes($from);
                if($diff_in_minutes >  30){

                    $updatestatu = Event::where('e_id', $checkclosevalue->e_id)
                    ->update(['e_status' => 2]); 
                    echo '<br>'.$checkclosevalue->id;
                    $sender = 1;
                    $message ="has ended due cross 30 minutes";
                    $n_type = 10;
                    $ref_id = $checkclosevalue->e_id;//event_id
                    $push_type = 1; //1 for normal 2 for seclient 
                    // get follower list and send notification
                    //echo 'dsad'; exit;      
                    $userArr = $checkclosevalue->e_u_id;
                    $UserRepostitory->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
                }
            }
        }    
    }

    public function read_mark(Request $request){
        $data = $request->all();
        if($request->method() == 'POST'){
            $userId = Auth::user()->id;

            $getroomId = SingleRoomMessage::where('rm_id', $data['rm_id'])
            ->first();
            //echo '<pre>'; print_r($getroomId->rm_r_id); exit;
            if($getroomId->rm_r_id != ''){
                SingleRoomMessage::where('rm_r_id', $getroomId->rm_r_id)
                ->where('receiver_id',$userId)
                        ->update([
                       'is_read' => 1
                    ]); 
            }
             $response = [
                    'code' => 200,
                    'msg'=>  'Marked read'
                ];
            return $response;
        }
    }

    /*****************************************************************************
      API                   => follower list                                     *
    * Description           => It is to get follower list                        *
    * Required Parameters   => Access Token                                      *
    * Created by            => Sunil                                             *
    *****************************************************************************/

    public function follower_list(Request $request){
       
        if($request->method() == 'GET'){
            $UserRepostitory = new UserRepository();
            
            $ApiService = new ApiService();
            $Check = $ApiService->follower_list($request);
           
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray(),
                ];
                //echo '<pre>'; print_r($responseOld); exit;


                $follower_list = array();
                foreach($responseOld['data']['data']  as $list){
                    $follower_array = array();
                    //echo '<pre>';print_r($list); exit;
                    $follower_array['userid'] =  @$list['userid'] ? $list['userid'] : 0;
                    $follower_array['first_name'] =  @$list['first_name'] ? $list['first_name'] : '';
                    $follower_array['picUrl'] =  @$list['picUrl'] ? $list['picUrl'] : '';
                    $follower_array['is_verified'] =  @$list['is_verified'] ? $list['is_verified'] : 0;
                    $follower_array['user_type'] =  @$list['user_type'] ? $list['user_type'] : 0;
                    $follower_array['id'] =  @$list['id'] ? $list['id'] : 0;
                    $follower_array['user_id'] =  @$list['user_id'] ? $list['user_id'] : 0;
                    $check_is_follow  =  $UserRepostitory->check_is_follow($list['userid']);
                    $follower_array['is_follow'] = $check_is_follow;
                    $follower_array['follow_by'] =  @$list['follow_by'] ? $list['follow_by'] : 0;
                  
                    
                    array_push($follower_list,$follower_array);
                }
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $follower_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }



    /*****************************************************************************
      API                   => following list                                     *
    * Description           => It is to get following list                        *
    * Required Parameters   => Access Token                                      *
    * Created by            => Sunil                                             *
    *****************************************************************************/

    public function following_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->following_list($request);
           
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray(),
                ];
                //echo '<pre>'; print_r($responseOld['gender']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $responseOld['data']['data'],
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


     /**********************************************************************************
    * API                   => userPostSearch                                          *
    * Description           => It is to get userPostSearch                             *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function userPostSearch(Request $request){
       
        if($request->method() == 'POST'){

            $ApiService = new ApiService();
            $UserRepostitory   = new UserRepository();

            $users = $ApiService->userList($request);

            
            $error_msg = new Msg();

            $msg =  $error_msg->responseMsg($users->error_code);
            if($users->error_code == 280){
                $data = $users->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                 // print_r($Check->data); exit;           
                //echo '<pre>'; print_r($responseOld['data']['data']); exit;
                $user_list['users'] = array();
                foreach($responseOld['data']['data']  as $list){
                    $user_array = array();
                    //echo '<pre>';print_r($list); exit;
                    $user_array['id'] =  @$list['userid'] ? $list['userid'] : '';
                    $user_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $user_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $user_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $user_array['location']  =   @$list['location'] ? $list['location'] : '';
                    $check_is_follow  = $UserRepostitory->check_is_follow($list['userid']);
                    $user_array['is_follow']  =   $check_is_follow;
                    array_push($user_list['users'],$user_array);
                }
           
            }

            // post List
            $post = $ApiService->post_list($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($post->error_code);
            if($post->error_code == 647){
                //print_r($Check); exit;
                $data = $post->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                $user_list['post'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $partner_array = array();
                    $repost  = array();
                    $postid = @$list['repost_id'] ? $list['repost_id'] : $list['id'];

                    
                    $like_count  = $UserRepostitory->like_count($postid);
                    $favourite_count  = $UserRepostitory->favourite_count($postid);
                    $comment_count  = $UserRepostitory->comment_count($postid);
                    $is_my_like = $UserRepostitory->my_like_count($postid,Auth::user()->id);      
                    $is_my_favourite = $UserRepostitory->is_my_favourite($postid,Auth::user()->id);   

                    $is_my_bookmark = $UserRepostitory->is_my_bookmark($postid,Auth::user()->id);         
                    if($list['post_type'] == 3){
                        $total_vote_count = $UserRepostitory->total_vote_count($postid); 
                        $vote_count_per = $UserRepostitory->vote_count($postid) ; 
                       // print_r($vote_count_per); exit;
                    }else{
                        $total_vote_count = 0; 
                        $vote_count_per = 0 ; 

                    }
                    
                    $partner_array['id']            =   @$list['id'] ? $list['id'] : '';
                    $partner_array['original_id']   =   @$list['id'] ? $list['id'] :''; 
                    $partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
                    $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
                    $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
                    $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
                    $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
                    $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
                   // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
                    $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
                    $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
                    $partner_array['post_data'] = array();
                    $partner_array['post_data']['imgUrl']  =  array();
                    $photo_list =  $UserRepostitory->get_photo_list($list['id']);
                    $partner_array['post_data']['imgUrl']  = $photo_list;
                    $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
                    $partner_array['post_data']['like_count']  =   $like_count;

                    $partner_array['post_data']['is_liked'] = $is_my_like;
                    
                    $partner_array['post_data']['is_favorited']  =  $is_my_favourite;
                    $partner_array['post_data']['is_my_bookmark']  =  $is_my_bookmark;

                    
                    $partner_array['post_data']['favourite_count'] = $favourite_count;
                    $partner_array['post_data']['comment_count']  =   @$comment_count;

                 
                    $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;

                 
                    $partner_array['post_data']['total_votes']  =   $total_vote_count;
                    array_push($user_list['post'],$partner_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
             
            }


            $response = [
                    'code'  =>  200,
                    'msg'   =>  'User and post List',
                    'data'  =>  $user_list,
                   
                ];
            return $response;
        }   
    }

}
