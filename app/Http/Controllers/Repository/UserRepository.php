<?php

namespace App\Http\Controllers\Repository;
use App\User;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\Collection;
use App\Models\Partner;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\Follow;
use App\Models\CommentLike;
use App\Models\Favourite;
use App\Models\PendingMatches;
use App\Models\Categories;
use App\Models\SubCategories;
use App\Models\Gender;
use App\Models\RoomMessage;
use App\Models\Event;
use App\Models\Faq;
use App\Models\BlockUser;
use App\Models\Answer;
use App\Models\UserAnswer;
use App\Models\ReportList;
use App\Models\Religion;
use App\Models\Report;
use App\Models\PartnerType;
use App\Models\Region;
use App\Models\Subscription;
use App\Models\Notification;
use App\Models\Room;
use App\Models\SingleRoomMessage;
use App\Models\Transaction;
use Twilio\Rest\Client;
use App\Http\Controllers\Utility\CustomVerfication;
use App\Http\Controllers\Service\ApiService;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Utility\SendEmails;
use Carbon\Carbon;	
use Auth;
use DB;

Class UserRepository extends User{
	public function userNotify($arg,$userId){
		//print_r($userId); exit;
			
		$user = User::find($userId);
		$sender_name = $user['username'];

		$receiver_detail = User::find($arg['receiver_id']);
		$receiver_name = $receiver_detail['username'];
		$fcm_token = $receiver_detail['device_token'];
	
		$message =  $sender_name." has send you message.";
		$data['userid'] = $userId;
		$data['name'] = $user['username'];
		$data['message'] = $message;
		$data['chat_room'] = $arg['chat_room'];
		$data['n_type'] = 1;
		$notify = array ();
		$notify['chat_room'] = $arg['chat_room'];
		$notify['receiver_id'] = $arg['receiver_id'];
		$notify['relData'] = $data;
		$notify['message'] = $message;
		//print_r($notify); exit;
		$test =  $this->sendPushNotification($notify); 
		$n_type = 1;
			//$this->notification_save($all_invited_uservalue,$notify,$message,$sender_name,$n_type,$receiver_name,$fcm_token);
		
		return 1;
	}
	public function check_user($data){
		if(isset($data['email'])){
			$user_list = User::Where('email',@$data['email'])
				->where('user_status','!=',0)->first();
		}elseif(isset($data['facebook_id'])){
			$user_list = User::Where('facebook_id',@$data['facebook_id'])->first();
			//print_r($user_list); exit;
		}elseif(isset($data['google_id'])){
			$user_list = User::Where('google_id',@$data['google_id'])->first();
		}elseif(isset($data['apple_id'])){
			$user_list = User::Where('apple_id',@$data['apple_id'])->first();
		}else{
			$user_list = User::Where('phone',@$data['phone'])
				->where('user_status','!=',0)->first();
		}

		return $user_list;				
	}

	public function check_unactive_user($data){
		if(isset($data['email'])){
			$user_list = User::where('email',@$data['email'])->first();
		}else{
			$user_list = User::where('phone',@$data['phone'])->first();
		}
		//echo '<pre>'; print_r($user_list); die;
		return $user_list;				
	}

	public function register($data){

		$CustomVerfication = new CustomVerfication();
		$SendEmail = new SendEmails();
		$code = $CustomVerfication->generateRandomNumber(4);
		$rescod  = "";
		
		if(!isset($data['id'])){
			$create_user = new User();
			$create_user->username = @$data['username']?@$data['username']:'';
			$create_user->photo = @$data['photo'];
			$create_user->phone = @$data['phone'];
			$create_user->country_code = @$data['country_code'];
			$create_user->user_type = @$data['user_type']?$data['user_type']:1;
			$create_user->bio = @$data['bio'];
			$create_user->website = @$data['website'];
			$create_user->rank = @$data['rank']?$data['rank']:0;
			$create_user->followers = 0;
			$create_user->followings = 0;
			$create_user->posts = 0;
			$create_user->first_name = @$data['first_name'];
			$create_user->last_name = @$data['last_name'];
			$create_user->added_date = date ( 'Y-m-d H:i:s' );
			$create_user->user_status = '0';
			$create_user->is_approved = '0';
			$create_user->user_status = '0';
			$create_user->activation_code = $code;
			$create_user->password = hash::make($code);
			$create_user->is_email_verified = '0';
			$create_user->is_phone_verified = '0';
	        $create_user->last_login= date ( 'Y-m-d H:i:s' );
	        $create_user->token_id = mt_rand(); 
			$create_user->created_at = date ( 'Y-m-d H:i:s' );
			$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		
		}else{
			$create_user = User::find($data['id']);
			$follower_count  = $this->follower_count($data['id']);
        	$following_count  = $this->following_count($data['id']);
        	$post_count  = $this->post_count($data['id']);
			$create_user->username = @$data['username']?$data['username']:$create_user['username'];
			$create_user->photo = @$data['photo']?$data['photo']:$create_user['photo'];
			$create_user->phone = @$data['phone']?$data['phone']:$create_user['phone'];
			$create_user->country_code = @$data['country_code']?$data['country_code']:$create_user['country_code'];
			$create_user->user_type = @$data['user_type']?$data['user_type']:$create_user['user_type'];
			$create_user->bio = @$data['bio']?$data['bio']:$create_user['bio'];
			$create_user->website = @$data['website']?$data['website']:$create_user['website'];
			$create_user->rank = @$data['rank']?$data['rank']:$create_user['rank'];
			$create_user->followers = @$follower_count;
			$create_user->followings = @$following_count;
			$create_user->posts = @$post_count;
			$create_user->first_name = @$data['first_name']?$data['first_name']:$create_user['first_name'];
			$create_user->last_name = @$data['last_name']?$data['last_name']:$create_user['last_name'];
			$create_user->added_date = $create_user['added_date'];
			$create_user->user_status = $create_user['user_status'];
			$create_user->is_approved =  $create_user['is_approved'];
			$create_user->activation_code = $code;
			$create_user->password = hash::make($code);
			$create_user->is_email_verified = $create_user['is_email_verified'];
			$create_user->is_phone_verified = $create_user['is_phone_verified'];
	        $create_user->last_login= date ( 'Y-m-d H:i:s' );
	        $create_user->token_id = mt_rand(); 
			$create_user->created_at =  $create_user['created_at'];
			$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		}
		//$create_user->email 	= @$data['email'] ? $data['email']: '';
		//$create_user->password 	= hash::make(@$data['password']) ? hash::make(@$data['password']): '';
		
		$create_user->save();
		$userid = $create_user->id; 
		$message = "Your Wisdom verification Code is ". $code;
		
		if(isset($data['phone'])){
			$phone = $data['country_code'].''.$data['phone'];
            $verify_type = 1;
            $create_user->activation_code = $code;
            $user = User::find($userid);
            /*$sidname = getenv("CHAT_ENV").$userid; 
            if(empty($user['sid'])){
            	$chat_sid_create = $CustomVerfication->chat_user($sidname);
            	$user = User::find($userid);
				$user->sid = $chat_sid_create ;
				$user->save();
            }*/
            
			$verify = $CustomVerfication->phoneVerification($message,$phone);
            //$verify = $CustomVerfication->phoneVerification($message,"+917340337597");

		}else{
            $verify_type = 2;
        }

        $data['forgot_type'] = 1;

        if(@$data['email'] != ''){

            $email = $create_user->email;
            $name = $create_user->name;
            $code =  $code;

            //$url =  url("activation/".$code);
			//$newpassword = $url;

            $SendEmail->sendUserRegisterEmail($email,$name,$code,$data['forgot_type'],$userid);
        	
        }

		return $create_user;
	}


	public function social_register($data){
		if(@$data['facebook_id']){
			$code = @$data['facebook_id'];
		}elseif(@$data['google_id']){
			$code = @$data['google_id'];
		}elseif(@$data['apple_id']){
			$code = @$data['apple_id'];
		}



		$CustomVerfication = new CustomVerfication();
		$SendEmail = new SendEmails();
		$rescod  = "";
		
		if(!isset($data['id'])){
			$create_user = new User();
			$follower_count  = 0;
        	$following_count  = 0;
        	$post_count = 0;
		}else{
			$create_user = User::find($data['id']);
			$follower_count  = $this->follower_count($data['id']);
        	$following_count  = $this->following_count($data['id']);
        	$post_count  = $this->post_count($data['id']);
		}
		//$create_user->email 	= @$data['email'] ? $data['email']: '';
		//$create_user->password 	= hash::make(@$data['password']) ? hash::make(@$data['password']): '';


		$create_user->username = @$data['username'];
		$create_user->bio = @$data['bio'];
		$create_user->website = @$data['website'];
		$create_user->followers = @$follower_count;
		$create_user->followings = @$following_count;
		$create_user->posts = @$post_count;
		$create_user->user_type =  @$data['user_type']?$data['user_type']:1;
		
		$create_user->facebook_id = @$data['facebook_id'];
		$create_user->google_id = @$data['google_id'];
		$create_user->apple_id = @$data['apple_id'];
		$create_user->first_name = @$data['first_name'];
		$create_user->last_name = @$data['last_name'];
		$create_user->phone = @$data['phone'];
		$create_user->added_date = date ( 'Y-m-d H:i:s' );
		$create_user->user_status = 1;
		$create_user->is_approved = '0';
		$create_user->activation_code = $code;
		$create_user->password = hash::make($code);
		$create_user->is_email_verified = '0';
		$create_user->is_phone_verified = '0';
        $create_user->last_login= date ( 'Y-m-d H:i:s' );
        $create_user->token_id = mt_rand(); 
		$create_user->created_at = date ( 'Y-m-d H:i:s' );
		$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		
		$create_user->save();
		$userid = $create_user->id; 
		
		
        return $userid;
	}

	public function getuser($data){
		if(!empty($data['code'])){
			
			if(isset($data['email'])){
				$query = User::where('activation_code','=',$data['code'])
					->where('email',@$data['email'])
					->first();
			}else{
				$find = 0;
				$query = User::where('activation_code','=',$data['code'])
					->where('phone',@$data['phone'])
					->where('user_status','!=',2)
					->first();
				if(!empty($query)){
					$find = 1;	
				}else{
					$query = User::where('activation_code','=',$data['code'])
					->where('phone_tmp',@$data['phone'])
					->first();
					$find = 2; // to blank phone_tmp and  update in phone
				}

			}	
			if(!empty($query)){
				$user = User::find($query->id);
				//$user->password = Hash::make($data['password']);
		        //$user->activation_code = '';
		        //$user->user_status = 1;
				if(isset($data['email'])){
		            $user->is_email_verified = 1;
		        }else{
		           // $user->is_phone_verified = 1;
		        }
		        if($find == 1){
		        	$user->is_phone_verified  = 1;
		        	$user->user_status  = 1;
		        	
		        }
		        if($find == 2){
		        	$user->phone = $data['phone'];
		        	$user->phone_tmp = '';
		        }

	        	$user->save();



	        	$userData['code'] = 205;
	        	//$userData['email'] = $user->email; 
	        	//$userData['password'] = $user->password; 
	        	$userData['id'] = $user->id; 
	        	$userData['phone'] = $user->phone; 
	        	//$userData['access_token'] = $data['token']; 
		        
			}else{

				$userData['code'] = 422;	

	        }

		}else{

			$userData['code'] = 422;	

		}

		return $userData;
	}

	public function login($data){
		if(!empty($data['phone']))
		{
			$query = User::where('phone',$data['phone'])->first();
		}elseif (!empty($data['email'])) {
		
			$query = User::where('email',$data['email'])->first();			
		
		}else{
		
			$query = User::where('phone',$data['phone'])->where('email',$data['email'])->first();
		
		}
		

		return $query;
	}

	public function  clear_user_token($data){

		$clear_token = User::where('device_id',$data)->first();
		$clear_token->device_id = "";
		$clear_token->save();  
	}

	public function get_user_detail($data)
	{
		$token_id =  mt_rand();
		$query = User::find($data['id']);
		$query->token_id    = $token_id;
        $query->last_login  = date ( 'Y-m-d H:i:s' );
    	$query->device_id   = $data['device_id'];
        $query->device_type = $data['device_type']? $data['device_type']:
        0;
        if(@$data['first_name'] != ''){
        	$query->first_name  = @$data['first_name'];
    	}
    	if(@$data['last_name'] != ''){
       	 $query->last_name 	= @$data['last_name'];
    	}
    	if(@$data['photo'] != ''){
      		$query->photo 		= @$data['photo'];
      	}
      
        $query->save();

    	
        $follower_count  = $this->follower_count($data['id']);
        $following_count  = $this->following_count($data['id']);
        $post_count  = $this->post_count($data['id']);
        $userdata['username'] = @$query['username']?$query['username']:'';
		$userdata['bio'] = @$query['bio']?$query['bio']:'';
		$userdata['website'] = @$query['website']?$query['website']:'';
		$userdata['occupation'] = @$query['occupation']?$query['occupation']:'';
		$userdata['location'] = @$query['location']?$query['location']:'';
		$userdata['rank'] = @$query['rank']?$query['rank']:0;
		$userdata['followers'] = @$follower_count;
		$userdata['followings'] = @$following_count;
		$userdata['posts'] = $post_count;
		$userdata['user_type'] =  @$query['user_type']?$query['user_type']:1;

    	$userdata['id'] 		 = $query['id'];
       	$userdata['last_login']  = date ( 'Y-m-d H:i:s' );
        $userdata['device_id'] 	 = $query['device_id']?$query['device_id']:'';
        $userdata['device_type'] = $query['device_type']? intval($query['device_type']):'';
        $userdata['first_name']  = $query['first_name']?$query['first_name']:'';
        $userdata['last_name'] 	 = $query['last_name']?$query['last_name']:'';
        $userdata['device_token']= $query['device_token']?$query['device_token']:'';
        $userdata['access_token']= $data['token'];
        $userdata['user_status'] = $query['user_status']?$query['user_status']:'';
        $userdata['is_active_profile']= $query['is_active_profile']?$query['is_active_profile']:0;
        $userdata['is_notification']= $query['is_notification']?$query['is_notification']:0;
        $userdata['photo']= $query['photo']?$query['photo']:'';
        $userdata['phone']= $query['phone']?$query['phone']:'';
        $userdata['country_code']= $query['country_code']?$query['country_code']:'';
      
	        


		return $userdata;
	}

	public function forgot_password($data,$user){

		$data['forgot_type'] = 1;
		$SendEmail = new SendEmails();
		$getuser = User::find($user->id);
		$PhoneVerification = new CustomVerfication();
		$rescod = "";
		if($data['forgot_type'] == 1){

			if(@$data['phone'] != ''){
		        $pass = 1234;  //mt_rand (1000, 9999) ;
                $getuser->forgot_password_code = $pass;
                $getuser->activation_code  = $pass;

            }else{

                $pass = mt_rand (1000, 9999) ;
                $getuser->forgot_password_code = $pass;
            }

            $getuser->forgot_password_date = date ( 'Y-m-d H:i:s' );
            unset($getuser->password);

            //print_r($getuser);die;
            $getuser->save();


            if(@$data['email'] != ''){
                $email = $getuser->email;
                $name = $getuser->name;
                $newpassword =  $pass;
                $SendEmail->sendUserEmailforgot($email,$name,$newpassword,$data['forgot_type']);
            	$rescod = 601;
            	
            }

            $lastId = $getuser->id;
            $country_code = '';
			$code =  $pass ;

			$message = "Your Pump Tracker verification code is ". $code;

			if(@$data ['phone'] != ''){
                //$verify = $PhoneVerification->phoneVerification($message,$data['phone']);
                $rescod = 601;
            }
		}

		return $rescod;
	}

	public function getdoctor(){

		$getdoctor 	=	User::select('id','name')->where('user_type',1)
						->where('user_status',1)->where('is_approved',1)->get();
		return $getdoctor; 
	}

	public function getuserById($data){
		//print_r($data); exit;
		$user 	=	User::find($data);
		$checkblock = DB::table('block_users')->where('b_to', Auth::user()->id)->where('b_by', $user->id)->first();
		$userdata['is_blocked'] = 0;
		if(isset($checkblock->b_to)){
			$userdata['is_blocked'] = 1;
		}

		$checkblockstaus = DB::table('block_users')->where('b_by', Auth::user()->id)->where('b_to', $user->id)->first();
		$userdata['is_blocked_status'] = 0;
		if(isset($checkblockstaus->b_to)){
			$userdata['is_blocked_status'] = 1;
		}
		$follower_count  = $this->follower_count($user->id);
        $following_count  = $this->following_count($user->id);
        $post_count  = $this->post_count($user->id);
        $check_is_follow  = $this->check_is_follow($user->id);
		$userdata['id'] = $user->id;
        $userdata['username'] = @$user['username']?$user['username']:'';
        $userdata['phone'] = @$user['phone']?$user['phone']:'';
        $userdata['country_code'] = @$user['country_code']?$user['country_code']:'';
        $userdata['photo'] = @$user['photo']?$user['photo']:'';
		$userdata['d_o_b'] = @$user['d_o_b']?$user['d_o_b']:'';
		$userdata['bio'] = @$user['bio']?$user['bio']:'';
		$userdata['occupation'] = @$user['occupation']?$user['occupation']:'';
		$userdata['location'] = @$user['location']?$user['location']:'';
		$userdata['website'] = @$user['website']?$user['website']:'';
		$userdata['rank'] = @$user['rank']?$user['rank']:0;
		$userdata['followers'] = @$follower_count;
		$userdata['followings'] = @$following_count;
		$userdata['is_follow'] = $check_is_follow;
		$userdata['posts'] = @$post_count ;
		$userdata['user_type'] =  @$user['user_type']?$user['user_type']:1;
       	$userdata['last_login']  = date ( 'Y-m-d H:i:s' );
        $userdata['device_id'] 	 = $user['device_id']?$user['device_id']:'';
        $userdata['device_type'] = $user['device_type']?intval($user['device_type']):'';
        $userdata['first_name']  = $user['first_name']?$user['first_name']:'';
        $userdata['last_name'] 	 = $user['last_name']?$user['last_name']:'';
        $userdata['device_token']= $user['device_token']?$user['device_token']:'';
        $userdata['reset_key']= $user['reset_key']?$user['reset_key']:'';
        //$userdata['access_token']= $user['token'];
        $userdata['user_status'] = $user['user_status']?$user['user_status']:'';
        $userdata['is_active_profile']= $user['is_active_profile']?$user['is_active_profile']:0;
        $userdata['is_notification']= $user['is_notification']?$user['is_notification']:0;

       	
		return $userdata;

	}
	public function getotheruserById($data){
		$follower_count  = $this->follower_count($user->id);
        $following_count  = $this->following_count($user->id);
        $post_count  = $this->post_count($user->id);
		$userdata['id'] = $user->id;
        $userdata['username'] = @$user['username']?$user['username']:'';
        $userdata['phone'] = @$user['phone']?$user['phone']:'';
        $userdata['country_code'] = @$user['country_code']?$user['country_code']:'';
        $userdata['photo'] = @$user['photo']?$user['photo']:'';
		$userdata['d_o_b'] = @$user['d_o_b']?$user['d_o_b']:'';
		$userdata['bio'] = @$user['bio']?$user['bio']:'';
		$userdata['occupation'] = @$user['occupation']?$user['occupation']:'';
		$userdata['location'] = @$user['location']?$user['location']:'';
		$userdata['website'] = @$user['website']?$user['website']:'';
		$userdata['rank'] = @$user['rank']?$user['rank']:0;
		$userdata['followers'] = @$follower_count;
		$userdata['followings'] = @$following_count;
		$userdata['posts'] = $post_count ;
		$userdata['user_type'] =  @$user['user_type']?$user['user_type']:1;

       	$userdata['last_login']  = date ( 'Y-m-d H:i:s' );
        $userdata['device_id'] 	 = $user['device_id']?$user['device_id']:'';
        $userdata['device_type'] = $user['device_type']? intval($user['device_type']):'';
        $userdata['first_name']  = $user['first_name']?$user['first_name']:'';
        $userdata['last_name'] 	 = $user['last_name']?$user['last_name']:'';
        $userdata['device_token']= $user['device_token']?$user['device_token']:'';
        //$userdata['access_token']= $user['token'];
        $userdata['user_status'] = $user['user_status']?$user['user_status']:'';
        $userdata['is_active_profile']= $user['is_active_profile']?$user['is_active_profile']:0;
        $userdata['is_notification']= $user['is_notification']?$user['is_notification']:0;
		//print_r($data);die;
		//	print_r($user);die;
       	// $userData['user_type'] = $user->user_type;
        //$userData['phone'] = $user->phone ? $user->phone : '';
        //$userData['address'] = @$user->address ? $user->address : '';
        //$userData['zip'] = @$user->zip ? $user->zip :'';
       //	$userData['forgot_password_code'] = $user->forgot_password_code ? $user->forgot_password_code : '';
        /*if($user->user_type == 2){
        
        }*/
        
        //$userData['photo'] = @$user->photo ? URL('/public/images/'.@$user->photo) : URL('/public/images/profile.png');
        //$userData['license_photo'] = $user->license_photo ? URL('/public/images/'.@$user->license_photo):'';
        
		return $userData;
	}

	public function getupdateprofile($data){
		
		$user 	=	User::find($data['Id']);
		$query  = 0;
		/*if($user->is_email_verified != 1){

			$user->email 	= 	@$data['email'] ? $data['email']:$user->email;
		} 	

		if($user->is_phone_verified != 1){

        	$user->phone 	= 	@$data['phone'] ? $data['phone']:$user->phone;
		}*/	
		/*if(isset($data['d_o_b'])){
			$dob = Carbon::createFromFormat('d/m/Y', $data['d_o_b']);
			//print_r($dob); exit;
			$age = 0;
			if(!empty($dob)){
				$age = Carbon::parse($dob)->diff(Carbon::now())->y;
			}
		}*/
		if(isset($data['username'])){

			$query = User::where('username',@$data['username'])->where('id','!=',@$data['Id'])->count();

			//print_r($query); exit;

		}/*else if(isset($data['phone'])){

			$query = User::where('phone',@$data['phone'])->where('id','!=',@$data['Id'])->count();

		}*/

		$code = 1234;//$CustomVerfication->generateRandomNumber(4);*/
		$is_verify  = 0;
		if($query == 0){
			
			$user->first_name 	= 	@$data['first_name'] ? $data['first_name'] : $user->first_name;
			$user->last_name 	= 	@$data['last_name'] ? $data['last_name'] : $user->last_name;
			$user->username 	= 	@$data['username'] ? $data['username'] : $user->username;
			$user->d_o_b 	= 	@$data['d_o_b'] ? $data['d_o_b'] : $user->d_o_b;
			$user->bio 	= 	@$data['bio'] ? $data['bio'] : $user->bio;
			$user->website 	= 	@$data['website'] ? $data['website'] : $user->website;
			$user->occupation= @$data['occupation'] ? $data['occupation'] : $user->occupation;
			$user->location = @$data['location'] ? $data['location'] : $user->location;
			$user->user_type 	= 	@$data['user_type'] ? $data['user_type'] : $user->user_type;
			$user->photo 	= 	@$data['photo'] ? $data['photo'] : $user->photo;
			$user->is_active_profile 	= 	1;
			if(@$data['is_notification'] == 0){

				$user->is_notification 	= 	0;
			}else{
				$user->is_notification 	= 	@$data['is_notification'] ? $data['is_notification'] :$user->is_notification;
			}

			
			//print_r($user); exit;
			$user->save();

			$userData['code'] = 200;
			$userData['id'] = $user->id;
			$follower_count  = $this->follower_count($user->id);
        	$following_count  = $this->following_count($user->id);
        	$post_count  = $this->post_count($user->id);
	        //$userData['user_type'] = $user->user_type ? $user->user_type : '';
	        $userData['email'] = $user->email ? $user->email : '';
	        $userData['phone'] = $user->phone ? $user->phone : '';
	        $userData['country_code'] = $user->country_code ? $user->country_code : '';
	        $userData['photo'] = $user->photo ? $user->photo : '';
	        $userData['device_id'] = $user->device_id ? $user->device_id :'';
	        $userData['device_type'] = $user->device_type ? intval($user->device_type) : '';
	        $userData['first_name'] = $user->first_name ? $user->first_name : '';
	        $userData['last_name'] = $user->last_name ? $user->last_name : '';
	        $userData['username'] = $user->username ? $user->username : '';
			$userData['d_o_b'] = @$user->d_o_b?$user->d_o_b:'';
			$userData['bio'] = @$user->bio?$user->bio:'';
	       	$userData['occupation'] = @$user->occupation?$user->occupation:'';
			$userData['location'] = @$user->location?$user->location:'';

			$userData['website'] = @$user->website ? $user->website:'';
			$userData['rank'] = @$user->rank ? $user->rank : 0;
			$userData['followers'] = @$follower_count;
			$userData['followings'] = @$following_count;
			$userData['posts'] = $post_count;
			$userData['user_type'] =  @$user->user_type ? intval($user->user_type) : 1 ;

	         
		 	$userData['is_active_profile'] 			= 	 $user->is_active_profile?$user->is_active_profile : 0 ;
			$userData['is_email_verified'] 			= 	 $user->is_email_verified   ? $user->is_email_verified   : 0;

       		$userData['last_login']  = date ( 'Y-m-d H:i:s' );
		    $userData['device_token']= $user->device_token ? $user->device_token : '';
	        //$userdata['access_token']= $user['token'];
	        $userData['user_status'] = $user->user_status ? $user->user_status : '';
	        $userData['is_notification'] = $user->is_notification ? intval($user->is_notification) : 0;
	        
		}else{

	   		$userData['code'] = 649;
	   	}
	  
		return $userData;
	}



	public function create_post($data){
		if($data['description'] !=  ''){
			$send_notification  = 0;
			if(@$data['post_id']){
				$post = Post::where('id','=',@$data['post_id'])
					->where('u_id','=',$data['userid'])
					->first();
			}else{
				$post = new Post();
				$send_notification = 1;
			}
			
			$post->u_id = @$data['userid'] ? $data['userid']: '';
			$post->post_type = @$data['post_type'] ? $data['post_type']: 0;
			$post->posted_time = date ( 'Y-m-d H:i:s' );
			$post->description = @$data['description'] ? $data['description']: '';
			$post->created_at =  date ( 'Y-m-d H:i:s' );
			$post->updated_at =  date ( 'Y-m-d H:i:s' );
			$post->save();
			$lastid = $post->id;
            if(@$data['imgUrl'] !=  ''){
				$datanew =  json_decode($data['imgUrl']);
				foreach ($datanew as $photokey => $photovalue) {
					if(!empty($photovalue->thumb)){
						$photo = new Photo();
						$photo->p_u_id = @$data['userid'] ? $data['userid']: '';
						$photo->thumb = @$photovalue->thumb ? $photovalue->thumb: '';
						$photo->url = @$photovalue->url ? $photovalue->url: '';
						$photo->post_id = $lastid;
						//echo '<per>'; print_r($photo); exit;
						$photo->save();
					}
					//$lastid = $photo->p_id;
				}
				
			}

			$partner_array['code'] = 200;
			$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('posts.id', $lastid)
					->leftjoin('users','posts.u_id','users.id')
					->first();
			//echo '<pre>';print_r($list); exit;

			
			$is_my_favourite = DB::table('favourities')
	            ->where('post_id','=',$list['id'])
	            ->where('f_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_favourite == 1){

	            $partner_array['post_data']['is_favorited']  =  true;
	        }else{
	            $partner_array['post_data']['is_favorited']  =  false;

	        }


	        $is_my_like = DB::table('likes')
	                        ->where('post_id','=',$list['id'])
	            ->where('l_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_like == 1){

	            $partner_array['post_data']['is_liked']  =  true;
	        }else{
	            $partner_array['post_data']['is_liked']  =  false;

	        }
	        $partner_array['post_data']['is_reposted']  =  false;
			$partner_array['id']            =   @$list['id'] ? $list['id'] : '';
	        $partner_array['userid']      =   @$list['userid'] ? $list['userid'] : '';
	        $partner_array['picUrl']      =   @$list['picUrl'] ? $list['picUrl'] : '';
	        $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
	        $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
	        $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
	        $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
	       // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
	        $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
	        $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
	        
	        $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
	        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;
	        
	        if($send_notification  == 1){
	        	//echo '<pre>'; print_r($send_notification); exit;
	        	$sender = $data['userid'];
	        	$message ="created a new post.";
	        	$n_type = $partner_array['post_type'];
	        	$ref_id = $lastid;//post_id
	        	$push_type = 1; //1 for normal 2 for seclient 
	        	// get follower list and send notification
	        	$ApiService = new ApiService();
	        	$other_user_id['user_id'] =  $data['userid'];
	        	$follower = $ApiService->followUser($other_user_id);
	        	//echo '<per>'; print_r($follower); exit;
	        	if($follower->error_code == 280){
	                $data = $follower->data;   
	                $followerresponseOld = [
	                    'data'  => $data->toArray()    
	                ];
	                 // print_r($Check->data); exit;           
	                //echo '<pre>';print_r($followerresponseOld['data']['data']); exit;
	               
	                foreach($followerresponseOld['data']['data']  as $followerlist){
	                	$userArr = $followerlist['userid'];
						$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
					}
				}
			}

		}else{

			$partner_array['code'] = 633;

		}

		return $partner_array;
	}


	public function delete_post($data){
		$deleteanswer =  Post::where('id',$data['post_id'])
		->where('u_id',$data['userid'])
		->delete();	

		/*$deleteanswerq =  Post::where('repost_id',$data['post_id'])
		->delete();	*/
		return 1;
	}

	// Post Related Fundtion
	// Total vote count on post
	public function total_vote_count($postid){
		$total_vote_count = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->count();
        return $total_vote_count;
	}

	// Total vote count on post
	public function vote_count($postid){
		$vote_count_one = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',1)
		    ->count();

		$vote_count_two = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',2)
		    ->count();    

		$vote_count_three = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',3)
		    ->count();    

		$vote_count_four = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',4)
		    ->count();
		
		$total_vote_count = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->count();


		$vote_poll['one'] =  $vote_count_one;  
		$vote_poll['two'] =  $vote_count_two;  
		$vote_poll['three'] = $vote_count_three;  
		$vote_poll['four'] =  $vote_count_four;
		if($vote_count_one != 0){
			$vote_poll['one_per'] =  $vote_count_one/$total_vote_count*100;  
		}else{
			$vote_poll['one_per'] =  0;  
		}


		if($vote_count_two != 0){
			$vote_poll['two_per'] =  $vote_count_two/$total_vote_count*100;   
		}else{
			$vote_poll['two_per'] =  0;  
		}

		if($vote_count_three != 0){
			$vote_poll['three_per'] =  $vote_count_three/$total_vote_count*100;   
		}else{
			$vote_poll['three_per'] =  0;  
		}

		if($vote_count_four != 0){
			$vote_poll['four_per'] =  $vote_count_four/$total_vote_count*100;   
		}else{
			$vote_poll['four_per'] =  0;  
		}
		//print_r($vote_poll); exit;
	
        return $vote_poll;
	}

	// Total follower count on user
	public function follower_count($userid){
		$follower_count = DB::table('follows')
		    ->where('user_id','=',@$userid)
		    ->count();
        return $follower_count;
	}

	// is_follow
	public function check_is_follow($userid){
		$check_is_follow = DB::table('follows')
		    ->where('follow_by','=',Auth::user()->id)
		    ->where('user_id','=',@$userid)
		    ->count();
        return $check_is_follow;
	}

	// Total following count on user
	public function following_count($userid){
		$following_count = DB::table('follows')
		    ->where('follow_by','=',@$userid)
		    ->count();
        return $following_count;
	}


	public function post_count($userid){
		$post_count = DB::table('posts')
		    ->where('u_id','=',@$userid)
		    ->count();
		return $post_count;
	}

	// Total like count on post
	public function like_count($postid){
		$like_count = DB::table('likes')
		    ->where('post_id','=',@$postid)
		    ->count();
        return $like_count;
	}
	
	// Total favourite count on post
	public function favourite_count($postid){
		$favourite_count = DB::table('favourities')
		    ->where('post_id','=',@$postid)
		    ->count();
        return $favourite_count;
	}
	// Total Comment count on post
	public function comment_count($postid){
		$comment_count = DB::table('comments')
		    ->where('post_id','=',@$postid)
		    ->count();
        return $comment_count;
	}
	// Retweet Cont on post
	public function repost_count($postid){
		$repost_count = DB::table('posts')
            ->where('repost_id','=',@$postid)
            ->count();    
        return $repost_count;
	}
	// Get Own like on post
	public function my_like_count($postid,$user_id){
		$is_my_like = DB::table('likes')
            ->where('post_id','=',$postid)
            ->where('l_user_id','=',$user_id)
            ->count();
        if($is_my_like == 1){

            $mylike  =  true;
        }else{
            $mylike   =  false;

        }
        return $mylike;
	}
	
	// Get Own favourite on post
	public function is_my_favourite($postid,$user_id){
		$is_my_favourite = DB::table('favourities')
                ->where('post_id','=',@$postid)
                ->where('f_user_id','=',$user_id)
                ->count();
            if($is_my_favourite == 1){

                $is_my_favourite  =  true;
            }else{
                $is_my_favourite  =  false;

            }
            return $is_my_favourite;
    }

    // Get Own favourite on post
	public function is_my_bookmark($postid,$user_id){
		$is_my_bookmark = DB::table('bookmarks')
                ->where('b_post_id','=',@$postid)
                ->where('b_user_id','=',$user_id)
                ->count();
            if($is_my_bookmark == 1){

                $is_my_bookmark  =  true;
            }else{
                $is_my_bookmark  =  false;

            }
            return $is_my_bookmark;
    }

    // Total Comment/Reply like count
	public function comment_like_count($commentid){
		$comment_like_count = DB::table('comment_likes')
		    ->where('c_id','=',@$commentid)
		    ->count();
        return $comment_like_count;
	}

	// Get own like  on Comment/Reply
	public function my_comment_like_count($commentid,$user_id){
		$my_comment_like_count = DB::table('comment_likes')
            ->where('c_id','=',$commentid)
            ->where('l_user_id','=',$user_id)
            ->count();
        if($my_comment_like_count == 1){

            $mycommentlike  =  true;
        }else{
            $mycommentlike   =  false;

        }
        return $mycommentlike;
	}

	public function get_photo_list($post_id){
		$photoData = DB::table('photos')
            ->where('post_id','=',$post_id)
            ->where('status','=',1)
            ->get();
            $photo_array = array();
            $Photo_list = array();
            foreach ($photoData as $photoDatakey => $photoDatavalue) {
                $photo_array['post_id']  =  @$photoDatavalue->post_id ? $photoDatavalue->post_id : '';
                $photo_array['photo_id']  =  @$photoDatavalue->p_id ? $photoDatavalue->p_id : '';
                $photo_array['thumb']  =  @$photoDatavalue->thumb ? $photoDatavalue->thumb : '';
                $photo_array['url']  =  @$photoDatavalue->url ? $photoDatavalue->url : '';
                $photo_array['p_u_id']  =  @$photoDatavalue->p_u_id ? $photoDatavalue->p_u_id : '';
                array_push($Photo_list,$photo_array);
               
            }
            return $pohots  =   $Photo_list;
	}

	public function get_media_list($data){
		$model 		= "App\Models\Photo";	
		$post_type = @$data['post_type'];
		$userid = @$data['userid'];
		//$category = @$data['category'];
		$query = $model::query();
			

			if(isset($post_type)){
				if($post_type != 0 ){
				//echo $selected_date ; exit;
					$query =$query->where('post_type','=',@$post_type);
				}
			}
			if(isset($userid)){
				$query =$query->where('p_u_id','=',@$userid);
			}
		//print_r($userid); exit;
		$query = $query->where('status',1)
					->orderBy('p_id', 'DESC')
					->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('status',1)
				->count();

		$partner = $query;
		return $partner;

		/*$photoData = DB::table('photos')
            ->where('p_u_id','=',$userid['userid'])
            ->where('status','=',1)
            ->get();
            $photo_array = array();
            $Photo_list = array();
            foreach ($photoData as $photoDatakey => $photoDatavalue) {
                $photo_array['post_id']  =  @$photoDatavalue->post_id ? $photoDatavalue->post_id : '';
                $photo_array['photo_id']  =  @$photoDatavalue->p_id ? $photoDatavalue->p_id : '';
                $photo_array['thumb']  =  @$photoDatavalue->thumb ? $photoDatavalue->thumb : '';
                $photo_array['url']  =  @$photoDatavalue->url ? $photoDatavalue->url : '';
                $photo_array['p_u_id']  =  @$photoDatavalue->p_u_id ? $photoDatavalue->p_u_id : '';
                array_push($Photo_list,$photo_array);
               
            }
            return $pohots  =   $Photo_list;*/
	}

    // Get post detail Model
    public function post_response($postid,$result=null){
    	$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('posts.id', $postid)
					->leftjoin('users','posts.u_id','users.id')
					->first();
		//echo '<pre>';print_r($list); exit;
		    
        
        $like_count  = $this->like_count($postid);
        $favourite_count  = $this->favourite_count($postid);
        $comment_count  = $this->comment_count($postid);
        //$repost_count  = $this->repost_count($postid);  
        $is_my_like = $this->my_like_count($postid,Auth::user()->id);      
        $is_my_favourite = $this->is_my_favourite($postid,Auth::user()->id); 
        $is_my_bookmark = $this->is_my_bookmark($postid,Auth::user()->id);         
                        

		$partner_array['result']            =   $result;
		
		

        $partner_array['post_data']['is_favorited']  =  $is_my_favourite;
        $partner_array['post_data']['is_liked']  =  $is_my_like;
       
        $partner_array['post_data']['is_reposted']  =  false;
		$partner_array['id']            =   @$list['id'] ? $list['id'] : '';
        $partner_array['userid']      =   @$list['userid'] ? $list['userid'] : '';
        $partner_array['picUrl']      =   @$list['picUrl'] ? $list['picUrl'] : '';
        $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
        $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
        $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
        $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
       // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
        $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
        $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
        
        $partner_array['post_data']['imgUrl']  =  array();
        $photo_list =  $this->get_photo_list($list['id']);
        $partner_array['post_data']['imgUrl']  = $photo_list;
                   
        $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
        $partner_array['post_data']['like_count']  =  $like_count;
        $partner_array['post_data']['favourite_count']  =   $favourite_count;
        $partner_array['post_data']['comment_count']  =   $comment_count;
        $partner_array['post_data']['is_my_bookmark']  =  $is_my_bookmark;
        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;

        return $partner_array;
    } 





	public function comment_post($data){
		//print_r($data); exit;
		if($data['description'] !=  ''){
			$postID = intval($data['post_id']);
			$post = Post::where('id','=',$data['post_id'])
				//->where('u_id','=',$data['userid'])
				->first();
			
			$comment = new Comment();
			$comment->u_id = @$data['userid'] ? $data['userid']: '';
			$comment->post_id = @$data['post_id'] ? $data['post_id']: '';
			$is_reply = 0;
			if(@$data['c_id']){
				$comment->parent_id = @$data['c_id'] ? $data['c_id']: '';
				$is_reply = 1;
			}
			
			$comment->description = @$data['description'] ? $data['description']: '';
			$comment->created_at =  date ( 'Y-m-d H:i:s' );
			$comment->updated_at =  date ( 'Y-m-d H:i:s' );
			$comment->save();
			$lastid = $comment->c_id;
			
			$userData['code'] = 200;
			$userData['c_id'] = @$lastid;
			$userData['created_at'] = @$comment->created_at;
			$userData['updated_at'] = @$comment->updated_at;
			$userData['u_id'] = @$comment->u_id;
			
			$send_notification  = 1;
			if($is_reply == 0){
				if($post['u_id'] != Auth::user()->id){
					if($send_notification  == 1){
			        	//echo '<pre>'; print_r($send_notification); exit;
			        	$sender = $data['userid'];
			        	$message ="Commented on your post.";
			        	$n_type = 2;
			        	$ref_id = $post['id'];//post_id
			        	$push_type = 1; //1 for normal 2 for seclient 
			        	// get follower list and send notification
			        	$other_user_id['user_id'] =  $data['userid'];
			        	   
			            $userArr = $post['u_id'];
						$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
						
						
					}
				}
			}else{
				$post = Comment::where('c_id','=',$data['c_id'])
						//->where('u_id','=',$data['userid'])
						->first();
				if($post['u_id'] != Auth::user()->id){
					if($post['u_id'] != Auth::user()->id){
						if($send_notification  == 1){
				        	//echo '<pre>'; print_r($send_notification); exit;
				        	$sender = $data['userid'];
				        	$message ="Reply on your comment.";
				        	$n_type = 2;
				        	$ref_id = $postID;//post_id
				        	$push_type = 1; //1 for normal 2 for seclient 
				        	// get follower list and send notification
				        	$other_user_id['user_id'] =  $data['userid'];
				        	   
				            $userArr = $post['u_id'];
							$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
							
							
						}
					}

				}
			}
	
		}else{

			$userData['code'] = 633;

		}

		return $userData;
	}

	public function report($arg,$userId){
		$checkreport = Report::where('user_id', $userId)->where('post_id', $arg['post_id'])->first();
		if(empty($checkreport)){
			$report = new Report();
			$report->user_id = $userId;
			$report->post_id = $arg['post_id'];
			//$report->reported_user = intval($arg['reported_user']);
			$report->report_type = $arg['report_type'];
			$report->report_desc = @$arg['report_desc'];
			//echo '<pre>'; print_r($report); exit;
			$report->save();
			return 1;
		}else{
			return 0;
		}		
	}


	public function userList($data){
		$model 		= "App\User";

		$name = @$data['name'];
		if(!empty(@$name)){
			$name = str_replace('@', '', @$name);
		}
		$userId= Auth::user()->id;
        $Is_method  = 0; 
		$query = $model::query();
		if(isset($name)){
			//echo $selected_date ; exit;
				$query = $query->Where(function($query) use ($name){
			        $query->orwhere('first_name','LIKE','%'.$name.'%');
			        $query->orwhere('username','LIKE','%'.$name.'%');
			     });

				//$query =$query->where('first_name','LIKE','%'.$name.'%');
				//$query =$query->where('username','LIKE','%'.$name.'%');
		}

		$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.location','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','follows.*')
				->where('users.id','!=',$userId)
				->where('users.id','!=',1)
				->where('users.is_active_profile',1)
				->where('users.isdelete',0)
				//->leftjoin('users','follows.user_id','users.id')
				->leftjoin('follows','users.id','follows.user_id')
				->groupBy('userid')
				->orderBy('users.first_name', 'ASC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('users.id','!=',$userId)->where('users.id','!=',1)
				->count();
		$users = $query;
		return $users;
	}

	public function collectionList($data){
		$model 		= "App\Models\Collection";		
		$name = @$data['name'];
		$type = @$data['type'];
		$userId= Auth::user()->id;
        $Is_method  = 0; 
		$query = $model::query();
		if(isset($name)){
			//echo $selected_date ; exit;
				$query =$query->where('title','LIKE','%'.$name.'%');
		}

		if(isset($type)){
			//echo $selected_date ; exit;
				$query =$query->where('category',$type);
		}

		$query = $query->where('status',1)
				//->leftjoin('users','follows.user_id','users.id')
				//->leftjoin('follows','users.id','follows.user_id')
				->orderBy('title', 'ASC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('status',1)
				->count();
		$users = $query;
		return $users;
	}

	public function collectionDetail($data){
		$list = Collection::where('id', $data)
			//->leftjoin('users','posts.u_id','users.id')
			->first();
		//echo '<pre>';print_r($checkPost); exit;
	 	$partner_array['id']   =   @$list['id'] ? $list['id'] : '';
		
		    
        //$like_count  = $this->like_count($postid);
        //$favourite_count  = $this->favourite_count($postid);
        //$comment_count  = $this->comment_count($postid);
        //$is_my_like = $this->my_like_count($postid,Auth::user()->id);      
        //$is_my_favourite = $this->is_my_favourite($postid,Auth::user()->id);      

		//$partner_array['is_favorited']  =  $is_my_favourite;
        //$partner_array['is_liked']  =  $is_my_like;
       
    	$partner_array['title']        =   @$list['title'] ? $list['title'] : '';
    	$partner_array['author']        =   @$list['author'] ? $list['author'] : '';
    	$partner_array['desc']        =   @$list['desc'] ? $list['desc'] : '';
    	$partner_array['photo']        =   @$list['photo'] ? $list['photo'] : '';
    	$partner_array['status']        =   @$list['status'] ? $list['status'] : '';
    	$partner_array['type']        =   @$list['type'] ? $list['type'] : '';
       
        
		//print_r($comment); exit;
		return $partner_array;
	}


	public function repost($data){
		//print_r($data); exit;
		$post_old = Post::where('id','=',@$data['post_id'])
					->first();
		if($post_old['description'] !=  ''){
			
			
			$post = new Post();
			
			
			$post->u_id = @$post_old['u_id'] ? $post_old['u_id']: 0;
			$post->repost_u_id = @$data['userid'] ? $data['userid']: '';
			$post->repost_id = @$post_old['id'] ? $post_old['id']: 0;
			$post->post_type = @$post_old['post_type'] ? $post_old['post_type']: 0;
			$post->post_type = @$post_old['post_type'] ? $post_old['post_type']: 0;
			
			$post->posted_time = date ( 'Y-m-d H:i:s' );
			$post->description = @$data['description'] ? $data['description']: '';
			$post->created_at =  date ( 'Y-m-d H:i:s' );
			$post->updated_at =  date ( 'Y-m-d H:i:s' );
			
			$post->save();
			$lastid = $post->id;

			$partner_array['code'] = 200;
			$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('posts.id', @$data['post_id'])
					->leftjoin('users','posts.u_id','users.id')
					->first();
			//echo '<pre>';print_r($list); exit;

			
			$is_my_favourite = DB::table('favourities')
	            ->where('post_id','=',$list['id'])
	            ->where('f_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_favourite == 1){

	            $partner_array['post_data']['is_favorited']  =  true;
	        }else{
	            $partner_array['post_data']['is_favorited']  =  false;

	        }


	        $is_my_like = DB::table('likes')
	                        ->where('post_id','=',$list['id'])
	            ->where('l_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_like == 1){

	            $partner_array['post_data']['is_liked']  =  true;
	        }else{
	            $partner_array['post_data']['is_liked']  =  false;

	        }
	        $partner_array['post_data']['is_reposted']  =  false;
			$partner_array['id']            =   @$list['id'] ? $list['id'] : '';
	        $partner_array['userid']      =   @$list['userid'] ? $list['userid'] : '';
	        $partner_array['picUrl']      =   @$list['picUrl'] ? $list['picUrl'] : '';
	        $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
	        $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
	        $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
	        $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
	       // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
	        $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
	        $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
	        
	        $partner_array['post_data']['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] : '';
	        $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
	        $partner_array['post_data']['like_count']  =   @$list['like_count'] ? $list['like_count'] : 0;
	        $partner_array['post_data']['favourite_count']  =   @$list['favourite_count'] ? $list['favourite_count'] : 0;
	        $partner_array['post_data']['comment_count']  =   @$list['comment_count'] ? $list['comment_count'] : 0;

	        $partner_array['post_data']['share_count']  =   @$list['share_count'] ? $list['share_count'] : 0;
	        $partner_array['post_data']['retweet_count']  =   @$list['retweet_count'] ? $list['retweet_count'] : 0;
	        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;
	        $partner_array['post_data']['stock_name']  =   @$list['stock_name'] ? $list['stock_name'] : '';
	        $partner_array['post_data']['stock_target_price']  =   @$list['stock_target_price'] ? $list['stock_target_price'] : '';
	        $partner_array['post_data']['time_left']  =   @$list['time_left'] ? $list['time_left'] : '';
	        $partner_array['post_data']['term']  =   @$list['term'] ? $list['term'] : '';
	        $partner_array['post_data']['result']  =   @$list['result'] ? $list['result'] : '';
	        $partner_array['post_data']['trend']   =  @$list['trend'] ? $list['trend'] : 0;
	        $partner_array['post_data']['recommendation']   =  @$list['recommendation'] ? $list['recommendation'] : 0;

	        $partner_array['post_data']['total_votes']  =   @$list['total_votes'] ? $list['total_votes'] : 0;
	        
	        if(!empty($list['poll_one'])){
	            $partner_array['post_data']['options'][0]['id']  =   1;
	            $partner_array['post_data']['options'][0]['title']  =   @$list['poll_one'] ? $list['poll_one'] : '';
	            $partner_array['post_data']['options'][0]['percentage']  =   0;
	            $partner_array['post_data']['options'][0]['is_voted']  =  0;
	        }
	        if(!empty($list['poll_two'])){
	            $partner_array['post_data']['options'][1]['id']  =   2;
	            $partner_array['post_data']['options'][1]['title']  =   @$list['poll_two'] ? $list['poll_two'] : '';
	            $partner_array['post_data']['options'][1]['percentage']  =  0;
	            $partner_array['post_data']['options'][1]['is_voted']  =   0;
	        }
	        if(!empty($list['poll_three'])){
	            $partner_array['post_data']['options'][2]['id']  =   3;
	            $partner_array['post_data']['options'][2]['title']  =   @$list['poll_three'] ? $list['poll_three'] : '';
	            $partner_array['post_data']['options'][2]['percentage']  =  0;
	            $partner_array['post_data']['options'][2]['is_voted']  =  0;
	        }
	        if(!empty($list['poll_four'])){
	            $partner_array['post_data']['options'][3]['id']  =   4;
	            $partner_array['post_data']['options'][3]['title']  =   @$list['poll_four'] ? $list['poll_four'] : '';
	            $partner_array['post_data']['options'][3]['percentage']  =  0;
	            $partner_array['post_data']['options'][3]['is_voted']  =  0;
	            
	        }
			/*$userData['code'] = 200;
			$userData['p_id'] = @$lastid;
			$userData['imgUrl'] = @$post_old->imgUrl;
			$userData['post_type'] = @$post_old->post_type;
			if(@$post_old['post_type'] == 3){
				$userData['poll_one'] = @$post_old->poll_one;
				$userData['poll_two'] = @$post_old->poll_two;
				if(@$post_old->poll_three != ''){
					$userData['poll_three'] = @$post_old->poll_three;
				}
				if(@@$post_old->poll_four != ''){
					$userData['poll_four'] = @$post_old->poll_four;
				}
			}
			$userData['created_at'] = @$post->created_at;
			$userData['updated_at'] = @$post->updated_at;
			$userData['u_id'] = @$post->u_id;*/
	

		}

		return $partner_array;
	}


	public function view_gallery($data){

		$getphotolist =  Photo::where('p_u_id',$data['p_u_id'])->get();	
		
		$PhotoData = array();
		$PhotoArr = array();
		foreach($getphotolist as $list){

			$PhotoData['p_id'] 		=  @$list->p_id ? $list->p_id : '';
			$PhotoData['p_u_id'] 	=  @$list->p_u_id ? $list->p_u_id : '';
			$PhotoData['p_photo'] 	=  @$list->p_photo? URL('/public/images/'.$list->p_photo): '';
			$PhotoData['is_default'] 	=  @$list->is_default ? $list->is_default : '';
			array_push($PhotoArr,$PhotoData);
			
		}

		return $PhotoArr;
	}

	public function delete_gallery($data){

		$getphotolist =  Photo::where('p_id',$data['p_id'])->delete();	
		return 1;
	}

	public function delete_match($data){
		$getmatch = PendingMatches::where('id','=',$data['id'])
					->first();
		if(!empty($getmatch)){
			$getothermatch = PendingMatches::where('reciver_id','=',$getmatch['sender_id'])
					->where('sender_id','=',$getmatch['reciver_id'])
					->first();
			
			//$deleteMymatch =  PendingMatches::where('id',$getmatch['id'])->delete();
			PendingMatches::where('id', $getmatch['id'])
	       		->update([
	           'is_deleted' => 1
        	]);	
	       	PendingMatches::where('id', $getothermatch['id'])
	       		->update([
	           'is_deleted' => 1
        	]);		
			//$deleteOthermatch =  PendingMatches::where('id',$getothermatch['id'])->delete();
			return 1;
		}else{
			return 0;
		}
	}

	public function get_user_list($data){

		$getpatient = User::where('current_physican_id','=',$data['Id'])
						->where('user_type','=',2)->where('user_status','=',1)->get();

		$patient = array();
		$Patient_list = array();

		foreach($getpatient as $list){


			$patient['id'] 				=  	@$list->id ? $list->id : '';
			$patient['name'] 			=  	@$list->name ? $list->name : '';
			/*$patient['email'] 			=  	@$list->email ? $list->email : '';
			$patient['country_code'] 	= 	@$list->country_code ? $list->country_code : '';
			$patient['phone'] 			= 	@$list->phone ? $list->phone : '';
			$patient['photo'] 			=  	@$list->photo ? $list->photo : '';
			$patient['address'] 		=  	@$list->address ? $list->address : '';
			$patient['zip'] 			=  	@$list->zip ? $list->zip : '';
			$patient['gender'] 			=  	@$list->gender ? $list->gender : '';
			$patient['phone'] 			=  	@$list->phone ? $list->phone : '';*/
			
			array_push($Patient_list,$patient);
			
		}

		return $Patient_list;
	}


	public function groupChat($data){
		//print_r($data); exit;
		//if($data['text'] !=  ''){
			
			/*$post = RoomMessage::where('rm_id','=',$data['post_id'])
				->where('u_id','=',$data['userid'])
				->first();*/
			
			$room_message = new RoomMessage();
			$room_message->sender_id = @$data['userid'] ? $data['userid']: '';
			$room_message->rm_g_id = @$data['g_id'] ? $data['g_id']: '';
			
			if(@$data['rm_id']){
				$comment->parent_id = @$data['rm_id'] ? $data['rm_id']: '';
			}
			
			$room_message->text = @$data['text'] ? $data['text']: '';
			$room_message->reply_id = @$data['reply_id'];
			$room_message->added_date = date ( 'Y-m-d H:i:s' );;
			$room_message->save();
			$lastid = $room_message->rm_id;
			
			$model 		= "App\Models\RoomMessage";	
			$r_id = @$data['r_id'];
			$query = $model::query();
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','room_msgs.*')
					->where('rm_id','=',@$lastid)
					//->where('users.user_status',1)
					->leftjoin('users','room_msgs.sender_id','users.id')
					->first();
			$partner = $query;
			$userData['msg']  = $partner;
			$userData['code'] = 200;
			//$userData['c_id'] = @$lastid;
			$userData['c_id'] = @$lastid;
			if($data['message_type'] == 1){
				$message = @$data['text'];
			}elseif($data['message_type'] == 2){
				$message = 'IMAGE';

			}else{
				$message = 'GIF';

			}
			//echo Auth::user()->id;
			/*$send_notification = 1;
			if($send_notification  == 1){
				$sender = $data['userid'];
				$message = $message;
				$n_type = 6;
				$ref_id = $lastid;//post_id
				$push_type = 2; //1 for normal 2 for seclient 
				$model1 		= "App\Models\RoomMember";	
				$r_id = @$data['r_id'];
				$query = $model1::query();
				$room_user = $query->select('room_members.*')
						->where('rm_r_id','=',$r_id)
						->where('rm_status',1)
						->where('rm_u_id','!=',$data['userid'])
						->get();  
		        //echo '<pre>'; print_r($room_user); exit;
		        foreach($room_user  as $room_userlist){
		        	$userArr = $room_userlist->rm_u_id;
					$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
				}
				
			}*/

		

		
	
		/*}else{

			$userData['code'] = 633;

		}*/

		return $userData;
	}

	public function groupChatNewList($data){
		$model 		= "App\Models\RoomMessage";	
		$r_id = @$data['g_id'];	
		$query = $model::query();
			
			//echo 'da'; exit;
			/*if(isset($partner_type)){
				//echo $selected_date ; exit;
				$query =$query->where('rm_r_id','=',@$r_id);
			}
			*/
			//$today = date ( 'Y-m-d H:i:s' );
			$today = $data['last_seen']; 
			//$today = date('Y-m-d H:i:s', strtotime('1 minutes', strtotime($today)));
			//$lastid = $data['rm_id'];	
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','room_msgs.*')
					->where('rm_g_id','=',@$r_id)
					->where('room_msgs.added_date','>', $today)
					//->where('users.user_status',1)
					->leftjoin('users','room_msgs.sender_id','users.id')
					->orderBy('room_msgs.rm_id', 'DESC')
					->get();

			$query->total_count = $model::where('rm_g_id','=',@$r_id)
			//->where('users.user_status',1)
					->count();
			$partner = $query;
			//echo '<pre>'; print_r($partner); exit;
		
		return $partner;
		
			
			
	}

	public function groupChatMessageList($data){
		$model 		= "App\Models\RoomMessage";	
		$r_id = @$data['g_id'];
		$query = $model::query();
			

			/*if(isset($partner_type)){
				//echo $selected_date ; exit;
				$query =$query->where('rm_r_id','=',@$r_id);
			}
			*/
				
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','room_msgs.*')
					->where('rm_g_id','=',@$r_id)
					//->where('users.user_status',1)
					->leftjoin('users','room_msgs.sender_id','users.id')
					->orderBy('room_msgs.rm_id', 'DESC')
					->paginate(10,['*'],'page_no');

			$query->total_count = $model::where('rm_g_id','=',@$r_id)
			//->where('users.user_status',1)
					->count();
			$partner = $query;
		
		
		return $partner;
	}

	public function roomList($data){
		$model 		= "App\Models\Room";	
		$query = $model::query();
		$userId = Auth::user()->id;
		
		$query = $query->select('rooms.*')
				//->where('rooms.status',1)
				->orwhere('sender_id', $userId)
				->orwhere('receiver_id', $userId)
				->orderBy('rooms.r_id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('rooms.status',1)
				->orwhere('sender_id', $userId)
				->orwhere('receiver_id', $userId)
				->count();
		$partner = $query;
		//echo '<pre>'; print_r($partner); exit;
		
		
		return $partner;
	}


	public function chat($data){
		//print_r($data); exit;
		if($data['text'] !=  ''){
			$userId = Auth::user()->id;
			$room = Room::where('sender_id', $userId)
				->where('receiver_id','=',$data['userid'])
				->first();
			if(empty($room)){
				$room = Room::where('sender_id', $data['userid'])
				->where('receiver_id',$userId)
				->first();
			}		
			if(!empty($room)){
				$lastid = $room->r_id;
			}else{
				$room = new Room();
				$room->sender_id = $userId;
				$room->receiver_id = @$data['userid'] ? $data['userid']: '';
				$room->room = $userId.'-'.$data['userid'];
				$room->save();
				$lastid = $room->r_id;
			}
			$roomData 	=	Room::where('r_id', $lastid)->first();
			//echo '<pre>'; print_r($roomData['room']); exit;
			$room_message = new SingleRoomMessage();
			$room_message->sender_id = $userId;
			$room_message->receiver_id = @$data['userid'] ? $data['userid']: '';
			$room_message->rm_g_id = $roomData['room'];
			$room_message->rm_r_id = $lastid;
			$room_message->message_type = @$data['message_type'] ? $data['message_type']: '';
			$room_message->media_url = @$data['media_url'] ? $data['media_url']: '';
			
			if(@$data['rm_id']){
				$comment->parent_id = @$data['rm_id'] ? $data['rm_id']: '';
			}
			
			$room_message->text = @$data['text'] ? $data['text']: '';
			$room_message->reply_id = @$data['reply_id'];
			$room_message->added_date =  date ( 'Y-m-d H:i:s' );
			$room_message->save();
			$lastid = $room_message->rm_id;
			

			$model 		= "App\Models\SingleRoomMessage";	
			$r_id = @$data['r_id'];
			$query = $model::query();
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','chat_room_msgs.*')
					->where('rm_id','=',@$lastid)
					//->where('users.user_status',1)
					->leftjoin('users','chat_room_msgs.sender_id','users.id')
					->first();
			$partner = $query;
			$userData['msg']  = $partner;
			$userData['code'] = 200;
			$userData['c_id'] = @$lastid;

			//echo Auth::user()->id;
			$send_notification = 1;
			if($send_notification  == 1){
				$sender = $userId;
				$message = $data['text'];
				$n_type = 7;
				$ref_id = $lastid;//post_id
				$push_type = 1; //1 for normal 2 for seclient 
				$userArr = $data['userid'];
				$this->notification_master($sender,$userArr,$partner,$n_type,$ref_id,$push_type);
			}

	
		}else{

			$userData['code'] = 633;

		}

		return $userData;
	}

	public function ChatMessageList($data){
		$model 		= "App\Models\SingleRoomMessage";	
		$r_id = 0;
		$query = $model::query();
		$userId = Auth::user()->id;
		$room = Room::where('sender_id', $userId)
			->where('receiver_id','=',$data['userid'])
			->first();
		if(empty($room)){
			$room = Room::where('sender_id', $data['userid'])
			->where('receiver_id',$userId)
			->first();
		}	
		if(!empty($room)){
			$r_id = $room->r_id;
		}	

			
		SingleRoomMessage::where('rm_r_id', $r_id)
		->where('receiver_id',$userId)
	       		->update([
	           'is_read' => 1
        	]);	
		$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','chat_room_msgs.*')
				->where('rm_r_id','=',@$r_id)
				//->where('users.user_status',1)
				->leftjoin('users','chat_room_msgs.sender_id','users.id')
				->orderBy('chat_room_msgs.rm_id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('rm_r_id','=',@$r_id)
			//->where('users.user_status',1)
			->count();
		$partner = $query;
		
		return $partner;
	}
	public function chat_t($data){
		//print_r($data); exit;
		$userId = Auth::user()->id;
		if($data['g_title'] !=  ''){
				$is_new = 0;
				$chat = Chat::where('u_id',@$data['u_id'])
				->where('sender',$userId)
					->first();
				
			
			if(empty($chat)){// if new group then add first user in this Group as admin
				
				$chat = new Chat();
				$chat->c_u_id = @$userId;
				$chat->c_sender_id = @$data['u_id'];
				$chat->c_status = 1;
				$chat->c_added_date =  date ( 'Y-m-d H:i:s' );
				$chat->save();


				$chat = new Chat();
				$chat->c_sender_id = @$userId;
				$chat->c_u_id = @$data['u_id'];
				$chat->c_status = 1;
				$chat->c_added_date =  date ( 'Y-m-d H:i:s' );
				$chat->save();
				
			}
			$partner_array['code'] = 200;
			
		}else{

			$partner_array['code'] = 633;

		}
		//echo '<pre>'; print_r($partner_array); exit;

		return $partner_array;
	}

	public function update_forgot_code($userId,$code){
		
		$user = User::find($userId);
		$user->reset_key = $code;
		$user->save();
		return $user;
	}

	public function update_activation($userId){
		
		$user = User::find($userId);
		$user->activation_code = "";
		$user->user_status = 1;
		$user->is_email_verified = 1;

		$user->save();
		$sender_name = $user['first_name'];
		$message =  $sender_name." your email account has been activated.";
		$data['userid'] = $userId;
		$data['name'] = $user['first_name'];
		$data['message'] = $message;
		$data['n_type'] = 1;
		$notify = array ();
		$notify['receiver_id'] = $userId;
		$notify['relData'] = $data;
		$notify['message'] = $message;
		//print_r($notify); exit;
		$test =  $this->sendPushNotification($notify); 
		return $user;
	}

	public function update_password($data){
		//print_r($data); exit;	
		//$user = User::where('reset_key', $data['code'])->where('email', $data['email'])->first();
		$user = User::where('id', $data['id'])->first();
		if($user){
			$forgot_password = 0;
			if($user->password != ''){
				$forgot_password = 1;
			}
			//if($user->reset_key == $data['code']){

				$user->password = hash::make($data['password']);
				$user->user_status = 1;
				$user->activation_code  = '';
				$user->is_phone_verified = 1;

				$user->save();

				$user->is_forgot = $forgot_password; 
			//}
		}
		
		return $user;
	}

	public function category_list($data){

		$category = Categories::where('c_status',1)->paginate(100,['*'],'page_no');
	
		$category_array = array();
		$category_list = array();

		foreach($category as $list){
			$category_array['c_id'] 			=  	@$list->c_id ? $list->c_id : '';
			$category_array['c_name'] 	=  	@$list->c_name ? $list->c_name : '';
			$category_array['c_desc'] 	=  	@$list->c_desc ? $list->c_desc : '';
			$category_array['c_image'] =  @$list['c_image'] ? URL('/public/images/'.$list['c_image']) : '';
			$category_array['c_status'] 	=  	@$list->c_status ? $list->c_status : '';
			
			array_push($category_list,$category_array);
		}

		//echo '<pre>'; print_r($chip); exit;
		
		return $category;;
	}
	public function gender_list($data){

		$gender = Gender::where('status',1)->paginate(100,['*'],'page_no');

		$gender_array = array();
		$gender_list = array();

		foreach($gender as $list){
			$gender_array['id'] 			=  	@$list->id ? $list->id : '';
			$gender_array['gender'] 	=  	@$list->gender ? $list->gender : '';
			
			array_push($gender_list,$gender_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $gender;
	}

	public function race_list($data){

		$race = Race::where('status',1)->paginate(100,['*'],'page_no');

		$race_array = array();
		$race_list = array();

		foreach($race as $list){
			$race_array['id'] 			=  	@$list->id ? $list->id : '';
			$race_array['race'] 	=  	@$list->race ? $list->race : '';
			
			array_push($race_list,$race_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $race;
	}

	public function religion_list($data){

		$religion = Religion::where('status',1)->paginate(100,['*'],'page_no');

		$religion_array = array();
		$religion_list = array();

		foreach($religion as $list){
			$religion_array['id'] 			=  	@$list->id ? $list->id : '';
			$religion_array['religion'] 	=  	@$list->religion ? $list->religion : '';
			
			array_push($religion_list,$religion_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $religion;
	}

	public function report_list($data){

		$report = ReportList::paginate(100,['*'],'page_no');

		$report_array = array();
		$report_list = array();

		foreach($report as $list){
			$report_array['id'] 			=  	@$list->id ? $list->id : '';
			$report_array['gender'] 	=  	@$list->gender ? $list->report : '';
			
			array_push($report_list,$report_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $report;
	}

	public function partner_type($data){

		$partner_type = PartnerType::paginate(100,['*'],'page_no');

		
		//echo '<pre>'; print_r($chip); exit;
		
		return $partner_type;
	}

	public function region($data){

		$region = Region::paginate(100,['*'],'page_no');

		
		//echo '<pre>'; print_r($chip); exit;
		
		return $region;
	}


	public function subcategory_list($data){
		$subcategory = SubCategories::where('sc_c_id',$data)->paginate(100,['*'],'page_no');
		$subcategory_array = array();
		$subcategory_list = array();

		foreach($subcategory as $list){
			$subcategory_array['sc_id'] 	=  	@$list->sc_id ? $list->sc_id : '';
			$subcategory_array['sc_name'] 	=  	@$list->sc_name ? $list->sc_name : '';
			
			array_push($subcategory_list,$subcategory_array);
		}
		//echo '<pre>'; print_r($chip); exit;
		
		return $subcategory;
	}

	public function mark_default($arg){
		$photo = Photo::where('p_id', $arg['p_id'])->first();
		if(!empty($photo)){
			/*$photo->p_id = $arg['p_id'];
			$photo->is_default = $arg['is_default'];
			//echo '<pre>'; print_r($photo); exit;
			$photo->save();*/
			Photo::where('p_u_id', $photo['p_u_id'])
	       		->update([
	           'is_default' => 0
        	]);
			Photo::where('p_id', $arg['p_id'])
	       		->update([
	           'is_default' => $arg['is_default']
        	]);
			$userData = Photo::where('p_id', $arg['p_id'])->first();
				$userData['code'] = 200;
			$userData['p_id'] = @$userData->p_id;
			$userData['p_photo'] = @$userData->p_photo? URL('/public/images/'.$userData->p_photo):'';
			$userData['p_u_id'] = @$userData->p_u_id;
			$userData['is_default'] = @$userData->is_default;
		}else{
			$userData['code'] = 431;
			//print_r($userData); exit;
		}
		return $userData;
	}

	


	public function like($arg,$userId){
		$checklike = Like::where('l_user_id', $userId)->where('post_id', $arg['post_id'])->first();
		if(empty($checklike)){
			$like = new Like();
			$like->l_user_id = $userId;
			$like->post_id = $arg['post_id'];
			//echo '<pre>'; print_r($like); exit;
			$like->save();
			$result= 1;
			$send_notification  = 1;
			$post = Post::where('id','=',$arg['post_id'])
						//->where('u_id','=',$data['userid'])
						->first();
			if($post['u_id'] != Auth::user()->id){
				if($send_notification  == 1){
					$post = Post::where('id','=',$arg['post_id'])
						//->where('u_id','=',$data['userid'])
						->first();
		        	//echo '<pre>'; print_r($send_notification); exit;
		        	$sender = Auth::user()->id;
		        	$message ="like your post.";
		        	$n_type = 3;
		        	$ref_id = $post['id'];//post_id
		        	$push_type = 1; //1 for normal 2 for seclient 
		        	// get follower list and send notification
		        	   
		            $userArr = $post['u_id'];
					$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
					
					
				}
			}
		}else{
			$deletelike =  Like::where('l_id',$checklike['l_id'])->delete();	
			$result = 0;
		}		
		
		
		
		$partner_array = $this->post_response($arg['post_id'],$result);
		return $partner_array;
	}


	public function bookmark($arg,$userId){
		$checklike = Bookmark::where('b_user_id', $userId)->where('b_post_id', $arg['post_id'])->first();
		if(empty($checklike)){
			$like = new Bookmark();
			$like->b_user_id = $userId;
			$like->b_post_id = $arg['post_id'];
			//echo '<pre>'; print_r($like); exit;
			$like->save();
			$result= 1;
		}else{
			$deletelike =  Bookmark::where('b_id',$checklike['b_id'])->delete();	
			$result = 0;
		}		
		
		
		
		$partner_array = $this->post_response($arg['post_id'],$result);
		return $partner_array;
	}

	public function follow($arg,$userId){
		//echo $userId;
		//print_r($arg); exit;
		$checkfollow = Follow::where('follow_by', $userId)->where('user_id', $arg['user_id'])->first();
		if(empty($checkfollow)){
			$follow = new Follow();
			$follow->follow_by = $userId;
			$follow->user_id = $arg['user_id'];
			//echo '<pre>'; print_r($like); exit;
			$follow->save();
			$result= 1;

			$send_notification  = 1;
			if($arg['user_id'] != Auth::user()->id){
				if($send_notification  == 1){
		        	//echo '<pre>'; print_r($send_notification); exit;
		        	$sender = Auth::user()->id;
		        	$message ="following you.";
		        	$n_type = 8;
		        	$ref_id = Auth::user()->id;//user_id
		        	$push_type = 1; //1 for normal 2 for seclient 
		        	// get follower list and send notification
		        	   
		            $userArr = $arg['user_id'];
					$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
					
					
				}
			}
		}else{
			$deletefollow =  Follow::where('id',$checkfollow['id'])->delete();	
			$result = 0;
		}		
		$getuser =array();
		$id = $arg['user_id'];

		$getuser  =   $this->getuserById($id);
		$getuser['result'] = $result;
		//$partner_array = $this->post_response($arg['post_id'],$result);
		return $getuser;
	}

	public function followUser($data){
		$model 		= "App\Models\Follow";	
		$post_type = @$data['post_type'];
		$userId= Auth::user()->id;
        $Is_method  = 0; 
		$query = $model::query();
		if(isset($post_type)){
			//echo $selected_date ; exit;
			$query =$query->where('post_type','=',@$post_type);
		}

		$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','follows.*')
				->where('follows.user_id',$userId)
				->leftjoin('users','follows.follow_by','users.id')
				->orderBy('users.first_name', 'ASC')
				->paginate(100,['*'],'page_no');

		$query->total_count = $model::where('follows.user_id',$userId)
				->count();
		$users = $query;
		return $users;
	}


	public function comment_like($arg,$userId){
		$checklike = CommentLike::where('l_user_id', $userId)->where('c_id', $arg['c_id'])->first();
		if(empty($checklike)){
			$like = new CommentLike();
			$like->l_user_id = $userId;
			$like->c_id = $arg['c_id'];
			//echo '<pre>'; print_r($like); exit;
			$like->save();
			$result= 1;
			$send_notification  = 1;
			$post = Comment::where('c_id','=',$arg['c_id'])
						//->where('u_id','=',$data['userid'])
						->first();
			if($post['u_id'] != Auth::user()->id){
				if($send_notification  == 1){
					$sender = Auth::user()->id;
					if($post['parent_id'] == ''){
		        		$message ="like your comment.";
		        		$n_type = 4;
		        	}else{
		        		$n_type = 5;
		        		$message ="like your reply.";
		        	}
		        	$ref_id = $post['post_id'];//post_id
		        	$push_type = 1; //1 for normal 2 for seclient 
		        	// get follower list and send notification
		        	   
		            $userArr = $post['u_id'];
					$this->notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type);
					
					
				}
			}
		}else{
			$deletelike =  CommentLike::where('c_id',$checklike['c_id'])->delete();	
			$result = 0;
		}		
		
		
		$postData 	=	Comment::where('c_id', $arg['c_id'])->first();
		$partner_array = $this->post_detail($postData['post_id']);
		$partner_array['result'] = $result;
		//echo '<pre>'; print_r($partner_array); exit;
		return $partner_array;
	}

	public function favourite($arg,$userId){
		$checklike = favourite::where('f_user_id', $userId)->where('post_id', $arg['post_id'])->first();
		if(empty($checklike)){
			$favourite = new favourite();
			$favourite->f_user_id = $userId;
			$favourite->post_id = $arg['post_id'];
			//echo '<pre>'; print_r($like); exit;
			$favourite->save();
			$result = 1;
		}else{
			$deletelike =  favourite::where('f_id',$checklike['f_id'])->delete();	
			$result = 0;
		}		
		
		
		$partner_array = $this->post_response($arg['post_id'],$result);
		

		return $partner_array;
	}

	public function vote($arg,$userId){
		$checklike = Vote::where('v_user_id', $userId)->where('v_post_id', $arg['v_post_id'])->first();
		if(empty($checklike)){
			$vote = new Vote();
			$vote->v_user_id = $userId;
			$vote->v_post_id = $arg['v_post_id'];
			$vote->v_option = $arg['v_option'];
			//echo '<pre>'; print_r($like); exit;
			$vote->save();
			$result= 1;
		}else{
			$deletelike =  Vote::where('v_user_id', $userId)->where('v_post_id', $arg['v_post_id'])->delete();	
			$result = 0;
		}		
		
		$partner_array = $this->post_response($arg['v_post_id'],$result);
		

		//echo '<pre>'; print_r($partner_array); exit;
		return $partner_array;
	}


	public function post_list($data){
		$model 		= "App\Models\Post";	
		$post_type = @$data['post_type'];
		$userid = @$data['userid'];
		$post_description = @$data['name'];
		//$category = @$data['category'];
		$query = $model::query();
			

			if(isset($post_type)){
				if($post_type != 0 ){
				//echo $selected_date ; exit;
					$query =$query->where('post_type','=',@$post_type);
				}
			}
			if(isset($userid)){
				$query =$query->where('u_id','=',@$userid);
			}

			if(isset($post_description)){
				
				//echo $post_description ; exit;
				
				$query =$query->where('description','LIKE','%'.$post_description.'%');
				//$query =$query->where('description','=',@$post_description);
			}

				
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('status',1)
					->leftjoin('users','posts.u_id','users.id')
					->orderBy('posts.id', 'DESC')
					->paginate(10,['*'],'page_no');

			$query->total_count = $model::where('status',1)
					->count();

			$partner = $query;

			//print_r($partner); exit;
		/*$partner = Partner::where('status','=',1)->paginate(10,['*'],'page_no');
		$partner_array = array();
		$Partner_list = array();*/

		/*foreach($partner as $list){
			$partner_array['id'] 			=  	@$list->id ? $list->id : '';
			$partner_array['name'] 	=  	@$list->name ? $list->name : '';
			$partner_array['desc'] 	=  	@$list->desc ? $list->desc : '';
			$partner_array['photo'] 		=  	@$list->photo ? $list->photo : '';
			$partner_array['status'] 		=  	@$list->status ? $list->status : '';
			
			array_push($Partner_list,$partner_array);
		}*/
		//echo '<pre>'; print_r($partner); exit;
		
		return $partner;
	}

	public function post_detail($data){
		$checkPost = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
			->where('posts.id', $data)
			->leftjoin('users','posts.u_id','users.id')
			->first();
		//echo '<pre>';print_r($checkPost); exit;
	 	$data = $data;
	 	$is_repost = false;
	 	$repost_id = 0;
		$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
			->where('posts.id', $data)
			->leftjoin('users','posts.u_id','users.id')
			->first();
		$partner_array['id']   =   @$list['id'] ? $list['id'] : '';
		
		$postid =  $data;
            
        $like_count  = $this->like_count($postid);
        $favourite_count  = $this->favourite_count($postid);
        $comment_count  = $this->comment_count($postid);
        $is_my_like = $this->my_like_count($postid,Auth::user()->id);      
        $is_my_favourite = $this->is_my_favourite($postid,Auth::user()->id);      
        $is_my_bookmark = $this->is_my_bookmark($postid,Auth::user()->id);         

		

        $partner_array['post_data']['is_favorited']  =  $is_my_favourite;
        $partner_array['post_data']['is_my_bookmark']  =  $is_my_bookmark;

        $partner_array['post_data']['is_liked']  =  $is_my_like;
       
    	$partner_array['userid']        =   @$list['userid'] ? $list['userid'] : '';
        $partner_array['picUrl']  =   @$list['picUrl'] ? $list['picUrl'] : '';
        $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
        $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
        $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
        $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
       // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
        $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
        
       

        $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
        //$partner_array['post_data']['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] : '';
        $partner_array['post_data']['imgUrl']  =  array();
        $photo_list =  $this->get_photo_list($list['id']);
        $partner_array['post_data']['imgUrl']  = $photo_list;
        $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
        $partner_array['post_data']['like_count']  =   @$like_count;
        $partner_array['post_data']['favourite_count']  =   @$favourite_count;
        $partner_array['post_data']['comment_count']  =   @$comment_count;
        $partner_array['post_data']['retweet_count']  =   @$repost_count;

        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;
       
        $comment = Comment::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','comments.*')
			->where('comments.post_id', $data)
			->WhereNull('comments.parent_id')
			->leftjoin('users','comments.u_id','users.id')
	        ->orderBy('c_id','DESC')
			->get();
		if(!empty($comment)){
			$partner_array['post_data']['comments'] =array();
		
			foreach ($comment as $commentkey => $commentvalue) {
				//print_r($commentvalue['c_id']);
				$partner_array['post_data']['comments'][$commentkey]['id']= $commentvalue['c_id']?$commentvalue['c_id']:0;

				$comment_like_count  = $this->comment_like_count($commentvalue['c_id']);
      
				$partner_array['post_data']['comments'][$commentkey]['userid']=   @$commentvalue['userid'] ? $commentvalue['userid'] : 0;
		        $partner_array['post_data']['comments'][$commentkey]['picUrl']  =   @$commentvalue['picUrl'] ? $commentvalue['picUrl'] : '';
		        $partner_array['post_data']['comments'][$commentkey]['user_name']  =   @$commentvalue['username'] ? $commentvalue['username'] : '';
		        $partner_array['post_data']['comments'][$commentkey]['first_name']  =   @$commentvalue['first_name'] ? $commentvalue['first_name'] : '';
		        $partner_array['post_data']['comments'][$commentkey]['last_name']  =   @$commentvalue['last_name'] ? $commentvalue['last_name'] : '';
		        $partner_array['post_data']['comments'][$commentkey]['description']  =   @$commentvalue['description'] ? $commentvalue['description'] : '';
		        $partner_array['post_data']['comments'][$commentkey]['posted_time']  =   @$commentvalue['created_at'] ? $commentvalue['created_at'] : '';
		        $partner_array['post_data']['comments'][$commentkey]['like_count']  =   $comment_like_count;
		       
		        $myowncommenton = $this->my_comment_like_count($commentvalue['c_id'],Auth::user()->id);
		        $partner_array['post_data']['comments'][$commentkey]['is_liked']  =  $myowncommenton;

		        $reply = Comment::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','comments.*')
				->where('comments.parent_id', $commentvalue['c_id'])
				->leftjoin('users','comments.u_id','users.id')
				->get();
				if(!empty($reply)){
					$partner_array['post_data']['comments'][$commentkey]['sub_comments']  =array();
					foreach ($reply as $replykey => $replyvalue) {
						$partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['id']= $replyvalue['c_id']?$replyvalue['c_id']:0;
						$reply_like_count  = $this->comment_like_count($replyvalue['c_id']);
						$partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['userid']=   @$replyvalue['userid'] ? $replyvalue['userid'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['picUrl']  =   @$replyvalue['picUrl'] ? $replyvalue['picUrl'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['user_name']  =   @$replyvalue['username'] ? $replyvalue['username'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['first_name']  =   @$replyvalue['first_name'] ? $replyvalue['first_name'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['last_name']  =   @$replyvalue['last_name'] ? $replyvalue['last_name'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['description']  =   @$replyvalue['description'] ? $replyvalue['description'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['posted_time']  =   @$replyvalue['created_at'] ? $replyvalue['created_at'] : '';

				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['like_count']  =   @$reply_like_count;
				      

				        $myownreplyon = $this->my_comment_like_count($replyvalue['c_id'],Auth::user()->id);
				        
				        $partner_array['post_data']['comments'][$commentkey]['sub_comments'][$replykey]['is_liked']  =  $myownreplyon;

					}
				}
			}
		}
		//print_r($comment); exit;
		return $partner_array;
	}


	public function bookmark_list($data){
		$model 		= "App\Models\Bookmark";	
		$post_type = @$data['post_type'];
		$userid =  Auth::user()->id;//  @$data['userid'];
		//$category = @$data['category'];
		$query = $model::query();
			

			if(isset($post_type)){
				if($post_type != 0 ){
				//echo $selected_date ; exit;
					$query =$query->where('post_type','=',@$post_type);
				}
			}
			/*if(isset($userid)){
				$query =$query->where('u_id','=',@$userid);
			}*/

			
				
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*','bookmarks.*')
					->where('b_user_id','=',$userid)
					->leftjoin('posts','bookmarks.b_post_id','posts.id')
					//->leftjoin('bookmarks','posts.id','bookmarks.b_post_id')
					->leftjoin('users','posts.u_id','users.id')
					->orderBy('posts.id', 'DESC')
					->paginate(10,['*'],'page_no');

			$query->total_count = $model::where('b_user_id','=',$userid)
					->count();

			$partner = $query;

			//print_r($partner); exit;
		/*$partner = Partner::where('status','=',1)->paginate(10,['*'],'page_no');
		$partner_array = array();
		$Partner_list = array();*/

		/*foreach($partner as $list){
			$partner_array['id'] 			=  	@$list->id ? $list->id : '';
			$partner_array['name'] 	=  	@$list->name ? $list->name : '';
			$partner_array['desc'] 	=  	@$list->desc ? $list->desc : '';
			$partner_array['photo'] 		=  	@$list->photo ? $list->photo : '';
			$partner_array['status'] 		=  	@$list->status ? $list->status : '';
			
			array_push($Partner_list,$partner_array);
		}*/
		//echo '<pre>'; print_r($partner); exit;
		
		return $partner;
	}


	public function check_username($data,$userId){
		$checkEmail = User::where('username', $data['username'])->first();
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail); exit;
		$userData =array();
		$userData['is_username_available'] = 0;	
		if(!isset($checkEmail['id'])){
			$userData['is_username_available'] = 0;
		}else{
			
	   		$userData['is_username_available'] = 1;
	   	}

		return $userData;
	}


	public function update_device($data,$userId){
		$checkEmail = User::where('id', $userId)
	       		->update([
	           'device_token' => @$data['device_token'] ,'device_type' => @$data['device_type'],
	           'device_id' => @$data['device_id']
        ]);	
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail['id']); exit;
		$userData =array();
		$userData['code'] = 200;
		$userData['device_token'] = $data['device_token'];
		
		return $userData;
	}

	public function notificationRead($data,$userId){
		//echo '<pre>'; print_r($data); exit;
		$checkEmail = Notification::where('n_u_id', $userId)
				->where('n_id',$data['n_id'])
	       		->update([
	           'n_status' => 1 
        ]);	


		////////////
		//print_r($userId); exit;
		//print_r($checkEmail['id']); exit;
		$userData =array();
		$userData['code'] = 200;
		$userData['n_id'] = $data['n_id'];
		
		return $userData;
	}


	public function UnreadReadCount($data,$userId){
		//echo '<pre>'; print_r($data); exit;
		/*$checkEmail = Notification::where('n_u_id', $userId)
				->where('n_id',$data['n_id'])
	       		->update([
	           'n_status' => 1 
        ]);	*/
        $checkEmail = Notification::where('n_u_id', $userId)
        ->where('n_status',0)
        ->count();


        $room_msg_count = SingleRoomMessage::where('receiver_id',$userId)
        				->where('is_read',0)
                        ->count(); 
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail['id']); exit;
		$userData =array();
		$userData['code'] = 200;
		$userData['notification_count'] = $checkEmail;
		$userData['chat_count'] = $room_msg_count;
		
		return $userData;
	}



	public function chat_user_sid_update($sid,$userId){
		$checkEmail = User::where('id', $userId)
	       		->update([
	           'sid' => @$sid 
        ]);	
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail['id']); exit;
		$userData =array();
		$userData['code'] = 200;
		$userData['sid'] = $sid;
		
		return $userData;
	}


	public function sendPushNotification($notify) {

		$data                       = $notify['relData'];
		$receiver_id                = trim($notify['receiver_id']); 
		$message                    = trim($notify['message']);
	    // $badge                      = trim(@$_POST['badge']);
		if (strlen($message) > 189) {
			$message = substr($message, 0, 185);
			$message = $message . '...';
		}else{
			$message = $message;
		}
		//echo $receiver_id; exit;
		$check_user 	=	User::find($receiver_id);
		
		$badge = 1;
		/*$notificationTable = TableRegistry::get('Notifications');
		$badge = $notificationTable
					->find()
					->where(['n_u_id'=> $receiver_id])
					->where(['n_type != 5'])
					->where(['n_status' => 0])
					->count();
		//print_r($badge);
		if($badge == 0){
		}else{
			$badge = $badge+1;
		}*/
		//prd($data);
		//echo '<pre>'; print_r($check_user); exit;

		if (empty($receiver_id)) {
			exit;
		}
		if ($check_user['device_type'] == 0) { //ios
			$check_user['device_id'] = trim($check_user['device_id']);
			if($check_user['device_id'] != ''){
				if(!empty($message)){
					//$this->iphone_push($check_user['device_token'], $message,  $data, $badge);
					//echo 'yesy';
					//print_r($data); exit;
					//$this->sendApns_P8($check_user['device_token'], $message,  $data, 0);
					$this->ios_fcm_push($check_user['device_token'], $message,  $data, $badge);
				}
			}
			//$this->android_push($check_user['device_id'], $message,  $data, $badge=0);
		}else{ //android
			//dd($check_user);
			if($check_user['device_id'] != ''){
				if(!empty($message)){
					//echo '<br>'.$check_user['device_id'].'<br>';
					$this->android_fcm_push($check_user['device_id'], $message,  $data, $badge);
				}
			}
		}
	   
		//return;
	}

	//  FCM
	public function android_push($id, $message, $relData, $badge){
		header('Content-type: text/html; charset=utf-8');
		// API access key from Google API's Console
		//CGT Key
		//prd($id);
		//Client Account
		$API_ACCESS_KEY  = 'AAAAh1ldrwA:APA91bFLkaiO52hj8z04m6tiCOt06wKejWKMR7uQLup9XbJ9fPoTelGjmrKYre-oBhhVRj5UEwspjbL3fMmMq3LMKcAv52exUWrWuFcSqdQQRKcxBrCCBLIB5vPtloUIBVll3zmvg8u7';
	   	
	   //	$id = 'courlEezNQ0:APA91bEPfxQbaJUUD_WakvYMZLyxDpKu6ydF1vXIu6j3QwGcPQFVWTS2H3oAayHRXsIGt39D_XcJ5qVtSJSKfjZpnZJ9zGLtvE9pk5xq_n4s2dIv_yv0XcnMVDvI6XlWq8p-1WXJRcy7';
		$registrationIds = array($id);
		//echo 'come'; exit;
		$msg['data']= array(
		'message' => $message,
		'badge' => (int)$badge,
		'relData' => $relData,
		//'vibrate' => 1,
		
		//'data'=>$data
		);
	   
		$fields = array(
					   'registration_ids' => $registrationIds,
					   'data' => $msg,
					   'title' => 'Eureka',
					   'priority'=>'high',
					   'sound' => 'default',
					   //'relData' => $relData
						);
		//prd($fields);
		$headers         = array(
		'Authorization: key=' . $API_ACCESS_KEY,
		'Content-Type: application/json'
		);
		$ch        = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
					curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		//curl_close($ch);
		$res = json_decode($result,true);
		//print_r($res); exit;
		if($res['success']){
		echo 'complete'; exit;
		curl_close($ch);
		return 1;
		}else{
		  echo 'not'; exit;
		curl_close($ch);
		return 0;
		}
	}

	// iphone FCM 
	public function android_fcm_push($id, $message, $relData, $badge){
		
		$url = "https://fcm.googleapis.com/fcm/send";
		$token =  $id; 
		//Client key
		//prd($relData['notification_title']);
		$serverKey = 'AAAAHp-Z9H8:APA91bHOXWGp1gNrqfuoTBioHB2JXpyRYSwehL7CQ62_hAJ9f-l5lHN1a6_2KR2QhBV1l4usJxeP9LQMRwJbGxbWdnCHQqhLaV-edGYkTdq4qucl7o7qMT3u5nSlLRLGGst18ysyQQYA';
		$title = "Hopple";
 		if(isset($relData['notification_title'])){
			$title = $relData['notification_title'];
		}
		
		$body = $message;
		$msg['data']= array(
		'message' => $message,
		'relData' => $relData,
		'badge' => (int)$badge,
		);
		$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => $badge);
		//$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high','data'=>$msg);
		$arrayToSend = array('to' => $token, 'priority'=>'high','data'=>$msg );
		$json = json_encode($arrayToSend);
		//print_r($json);exit;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key='. $serverKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,

		"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		//Send the request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		//print_r($response); exit;
		//Close request
		if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);	
	}
	
	public function ios_fcm_push($id, $message, $relData, $badge){
		
		$url = "https://fcm.googleapis.com/fcm/send";
		$token =  $id; 
		//Client key
		//prd($relData['notification_title']);
		$serverKey = 'AAAAPfs3PaM:APA91bE2dIp9_6zSow57Cxxx4IVeUYKGYo5-KfLyV3E9kB7C3h0DNgIiUmhRYjCRQpLU1rcmUNN4FwI01beS4WisMFH0sBkZw-CfJ9fBFgxmsfJIoVrLRYKRXekSyY2Pbv4OyuI0BcPC';
		$title = "Wisdom";
 		if(isset($relData['notification_title'])){
			$title = $relData['notification_title'];
		}
		
		if($relData['n_type'] == 7 ){
			//echo  '<pre>'; print_r($relData['message']['text']); exit;
			$body =$relData['message']['text'];
			$chat = $message;
			$msg['data']= array(
			'message' => $message,
			'relData' => $relData,
			'badge' => (int)$badge,
			);
			$notification = array('title' =>$relData['name'] ,'chat' =>$message, 'body' => $body, 'sound' => 'default', 'badge' => $badge);
		}else{
			$body = $message;
			$msg['data']= array(
			'message' => $message,
			'relData' => $relData,
			'badge' => (int)$badge,
			);
			$notification = array('title' =>$relData['name'] , 'body' => $body, 'sound' => 'default', 'badge' => $badge);
		}


			
		
		$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high','data'=>$msg['data']['relData']);
		/*$arrayToSend = array('aps'=>array(
		 	'relData' => $relData,
		 	'alert' => $message, 
		 	'badge' => intval(0), 'sound' => 'default' 
		 ),'to' => $token, 'priority'=>'high');*/
		$json = json_encode($arrayToSend);
		//echo '<pre>'; print_r($json);exit;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key='. $serverKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,

		"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		//Send the request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		//print_r($response); exit;
		//Close request
		if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);	
	}

	// Iphone APNS
	public function iphone_push($id, $message, $relData, $badge) {
		//header('Content-type: text/html; charset=utf-8');
		 echo $deviceToken = $id.'<br>';
		//$deviceToken = '5673719219f37a51aaa253126b892095c9d778feed081629939cd163a7cb5e33';
		// Put your private key's passphrase here:
		$deviceToken  = $id;
		$deviceToken  = trim($deviceToken);  
		$deviceToken  = '5673719219f37a51aaa253126b892095c9d778feed081629939cd163a7cb5e33';  
		$passphrase  = '';
		// //////////////////////////////////////////////////////////////////////////////
		//$ctx         = stream_context_create();
		/*$ctx = $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);*/
        $ctx = stream_context_create();
        //echo app_path(); exit;
		echo $pem_path = app_path().'/HopplePushCertificatesPemLive.pem';
		stream_context_set_option($ctx, 'ssl', 'local_cert', $pem_path );
		//stream_context_set_option($ctx, 'ssl', 'local_cert', './Meprosh_Development.pem');
		//echo stream_context_set_option($ctx, 'ssl', 'local_cert', $_SERVER['DOCUMENT_ROOT'].$this->webroot.'ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		// Open a connection to the APNS server
		//$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		//print_r($fp);
		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);
			echo 'Connected to APNS' . PHP_EOL;
			// Create the payload body
			//$resp = $this->cpSTR_to_utf8STR($message);
			//$this->writeResponseLog($resp);
			//$m = (string) $this->cpSTR_to_utf8STR($message);
		//echo strlen($message);
		$title = "Hopple";
 		
 		if(isset($relData['notification_title'])){
			$title = $relData['notification_title'];
		}

		$body['aps'] = array(
		'alert' => html_entity_decode($message, ENT_NOQUOTES, 'UTF-8'),
		'title' => $title,
		'sound' => 'default',
		'badge' => (int)$badge,
		'relData' => $relData,
		
	
		);
		//print_r($body); 
		//$this->writeResponseLog($body);
		//echo $count;
		// Encode the payload as JSON
		$payload = json_encode($body);
		//echo strlen($payload); exit;
		// Build the binary notification
		$msg     = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		$msg     = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result  = fwrite($fp, $msg, strlen($msg));
	    //print_r($result); 
	    //echo '<br>';
	    if (! $result)
			echo 'Message not delivered' . PHP_EOL;
		else
			echo 'Message successfully delivered' . PHP_EOL;
			
		//Close the connection to the server
		@socket_close($fp);
		fclose($fp);
			return;
	}

	public function sendApns_P8($deviceIds,$message,$optionalData,$badge){
        //print_r([$deviceIds,$message,$optionalData]); exit;
        //$pem_path = app_path().'/AuthKey_RR5BW56AWA.p8';
        $keyfile = app_path().'/AuthKey_RR5BW56AWA.p8';  # <- Your AuthKey file
        $keyid = 'RR5BW56AWA';                            # <- Your Key ID
        $teamid = '89PP7D2SJJ';                           # <- Your Team ID (see Developer Portal)
        $bundleid = 'com.hopple.app';               # <- Your Bundle ID
        $url = 'https://api.push.apple.com'; # <- production url, or use 
        //$url = 'https://api.sandbox.push.apple.com'; # <- development url, or use 

 
        //print_r($optionalData) exit;
        $pload = isset($optionalData) ? $optionalData : [];
        
        $payload = array();
        $n_type = $optionalData['n_type'];
        $payload['aps'] = array('noti_type' => $n_type,'alert' => $message, 'badge' => intval(0), 'sound' => 'default','pload'=>$pload, 'n_type' => $n_type  );
        $payload = json_encode($payload);

 		//print_r($payload); exit;

        $key = openssl_pkey_get_private('file://'.$keyfile);

 

        $header = ['alg'=>'ES256','kid'=>$keyid];
        $claims = ['iss'=>$teamid,'iat'=>time()];

 

        // $header_encoded = base64($header);
        // $claims_encoded = base64($claims);
        $header_encoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $claims_encoded = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=');

 

        $signature = '';
        openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
        $jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

 

        // only needed for PHP prior to 5.5.24
        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }

 

        if(is_array($deviceIds)){
            foreach ($deviceIds as $k => $v) {
                $http2ch = curl_init();
                curl_setopt_array($http2ch, array(
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                    CURLOPT_URL => "$url/3/device/$v",
                    CURLOPT_PORT => 443,
                    CURLOPT_HTTPHEADER => array(
                        "apns-topic: {$bundleid}",
                        "authorization: bearer $jwt"
                    ),
                    CURLOPT_POST => TRUE,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HEADER => 1
                ));

                $result = curl_exec($http2ch);
                //print_r($deviceIds);
                if ($result === FALSE) {
                    echo "Error for given device : ".$v;
                    //$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
                    //throw new Exception("Curl failed: ".curl_error($http2ch));
                }
            }
        }else{
            $http2ch = curl_init();
            curl_setopt_array($http2ch, array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_URL => "$url/3/device/$deviceIds",
                CURLOPT_PORT => 443,
                CURLOPT_HTTPHEADER => array(
                    "apns-topic: {$bundleid}",
                    "authorization: bearer $jwt"
                ),
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HEADER => 1
            ));

 

            $result = curl_exec($http2ch);
            
            if ($result === FALSE) {
                echo "Error for one device : ".$deviceIds;
                //$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
                //throw new Exception("Curl failed: ".curl_error($http2ch));
            }            
        }        
        return true;            
    }

	//subscriptionsList => It is used for get Subscription plan List
	public function subscriptionsList(){
        $query = Subscription::where('country', 'US')->get();
        if(!empty($query)){
        	//$query =  $query->toArray();
        	$query->code = 200;
        }else{
        	$query->code = 400;
        }
        return $query;
    }

    //pendingSubscriptionPlan =>  It is used for save the purchased plan which is pending
 	public function pendingSubscriptionPlan($arg,$userId)
    { 
    	$data = $arg;
		$u_id =  $userId;
		$itunesReceipt = $data['itunes_receipt'];

        $receiptData = '{"receipt-data":"'.$itunesReceipt.'","password":"51197df0c08744ca903b0dcc0f0a259a"}';

        $endpoint =  'https://sandbox.itunes.apple.com/verifyReceipt';

		$query = Transaction::where('user_id','=',$u_id )
        ->leftjoin('subscriptions','transactions.subscription_id','subscriptions.id')
        ->where('payment_status','=',1)
        ->where('expired_at', '>', NOW())
        ->orderBy('expired_at','DESC')
        ->first();
       //	print_r($query); exit;
        
        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $receiptData);

        $errno = curl_errno($ch);

        //print_r($errno); exit;

        if($errno==0){

            $response = curl_exec($ch);

            $receiptInfo = json_decode($response,true);

            if(!empty($receiptInfo)){

                if(isset($receiptInfo['status']) && $receiptInfo['status']==0){

                    $latestReceiptInfo = $receiptInfo['latest_receipt_info'];

                    $latestTransactioninfo = $latestReceiptInfo[count($latestReceiptInfo)-1];

                    //echo'<pre>';print_r($latestTransactioninfo);

                   /* $SubscriptionModel = TableRegistry::get('Subscriptions'); //use Cake\ORM\TableRegistry;

                    $subscriptionData = $SubscriptionModel

                    ->find()

                    ->select(['id','price'])

                    ->where(['itunes_product_id'=>$latestTransactioninfo['product_id']])

                    ->first();  */ 
                    $find_other_user = Transaction::where('user_id','!=',$u_id )
			        ->where('itune_original_transaction_id','=',$latestTransactioninfo['original_transaction_id'])
			        ->first();

	                //print_r($find_other_user); exit;
                    
                    if(empty($find_other_user)){
	                    $transactionData = new Transaction();
						$transactionData->user_id = $u_id;
						$transactionData->subscription_id = 1;
						$transactionData->total_amount 	=  9.99;
						$transactionData->payment_status 	=  1;
						$transactionData->itune_original_transaction_id = $latestTransactioninfo['original_transaction_id'];
						$transactionData->itunes_receipt = $itunesReceipt;
						$transactionData->orderId = $latestTransactioninfo['transaction_id'];
						$transactionData->packageName = $latestTransactioninfo['product_id'];
						$transactionData->productId = $latestTransactioninfo['product_id'];
						$transactionData->purchaseTime =  date('Y-m-d H:i:s',strtotime($latestTransactioninfo['purchase_date']));
						$transactionData->purchaseState =  1;
						$transactionData->created_at =  date('Y-m-d H:i:s',strtotime($latestTransactioninfo['purchase_date']));
						$transactionData->expired_at =  date('Y-m-d H:i:s',strtotime($latestTransactioninfo['expires_date']));
						$transactionData->device_type = 0;
						$transactionData->purchaseToken = 'Iphone';
						if ($result = $transactionData->save()){
	                        $transaction_last_id = $transactionData->id;
	                      	$user = User::where('id', $u_id)
						       		->update([
						           'itunes_autorenewal' => 1 ,'is_subscribe' => 1,'active_subscription' => 1,
						           'last_transaction_id' => $transaction_last_id
					        ]);	
	                     
	                       	$is_success = 221;
						    //print_r($query); exit;


	                    }else{
	                        $is_success = 423;

	                    }
	                }else{
	                	$is_success = 424;
	                }

                }else{
                	$user = User::where('id', $u_id)
					       		->update([
					           'itunes_autorenewal' => 0 
				        ]);	

                     $is_success = 424;
                }

            }

        }

        return $is_success;

        
    } 

	//subscriptions -> It is used for get Subscription Type
	public function subscriptions()
	{ 

        if ($this->request->is('get')){

            $data = $this->request->query;

            $uid = $this->userid;

            $SubscriptionsModel = TableRegistry::get('Subscriptions'); //use Cake\ORM\TableRegistry;

            $querySubscriptions = $SubscriptionsModel

            ->find();

            $TransactionsModel = TableRegistry::get('Transactions'); //use Cake\ORM\TableRegistry;

            $query = $TransactionsModel

            ->find()

            ->contain(['Subscriptions','Users'])

            ->where(['user_id'=>$uid])

            ->where(['payment_status'=>'1'])

            ->where(['NOW()<`expired_at`'])

            ->order(['expired_at'=>'DESC'])

             ->first();

            $timestamp = strtotime(date('Y-m-d H:i:s'));

            if(!empty($query)){

                $query =  $query->toArray();

                if(!empty($query['user']['id'])){

                    $addded_date = strtotime($query['user']['added_date']);

                }else{

                    $addded_date ='';

                }

                if($query['device_type']== 0){

                     $this->set([

                    'data' => array('Subscriptions'=>$querySubscriptions,'timestamp' =>$timestamp,'plan_name'=>$query['subscription']['name'],'plan_id'=>$query['subscription_id'],'added_date'=>$addded_date,'itune_original_transaction_id'=>$query['itune_original_transaction_id'],'itunes_receipt'=>json_decode($query['itunes_receipt'])),

                    'code' => 209,

                    'msg'=> responseMsg(209),

                    '_serialize' => ['code','data','msg']

                 ]);



                }else{     

                    $this->set([

                        'data' => array('Subscriptions'=>$querySubscriptions,'timestamp' =>$timestamp,'plan_name'=>$query['subscription']['name'],'plan_id'=>$query['subscription_id'],'added_date'=>$addded_date,'itune_original_transaction_id'=>$query['itune_original_transaction_id'],),

                        'code' => 209,

                        'msg'=> responseMsg(209),

                        '_serialize' => ['code','data','msg']

                     ]);

                }

            }else{

                $querySubscriptions =  $querySubscriptions->toArray();

                  $this->set([

                    'data' => array('Subscriptions'=>$querySubscriptions,'timestamp' =>$timestamp,'plan_name'=>'','plan_id'=>0,'added_date'=>'','itune_original_transaction_id'=>''),

                    'code' => 209,

                    'msg'=> responseMsg(209),

                    '_serialize' => ['code','data','msg']

                 ]);

            }

        }
    }

	//newSubscriptionPlan => It is used for Add new Subscription Plan (not need)
	public function newSubscriptionPlan()
    { 

        if ($this->request->is('post')){



            $data = $this->request->data;

            //pr($data);

            $u_id = $this->userid;

            $this->loadModel('Transactions');

            $Transactions = TableRegistry::get('Transactions'); 

            $transaction = $this->Transactions->newEntity();

            $transaction = $this->Transactions->patchEntity($transaction, $data);

            $transaction ['user_id'] = $this->userid;

            $created_at = $data['created_at']/1000;

            $transaction ['created_at'] =  date('Y-m-d H:i:s', $created_at);

            $expired_at = $data['expired_at']/1000;

            $transaction ['expired_at'] =date('Y-m-d H:i:s', $expired_at);

            

            //prd($transaction);

            if ($this->Transactions->save($transaction)){

                $this->loadModel('Users');

                $UserModel = TableRegistry::get('Users'); //use Cake\ORM\TableRegistry;

                $user = $UserModel->get($u_id);

                $user->itunes_autorenewal = 0;

                $user->active_subscription = $data['subscription_id'];

                $user->last_transaction_id = $transaction_last_id;

                $UserModel->save($user);

                $this->set([

                    'msg'=> responseMsg(210),

                    'code'  => 200,

                    '_serialize' => ['code','msg']

                ]);

                

            }else{

                 $this->set([

                    'msg'=> responseMsg(418),

                    'code'  => 418,

                    '_serialize' => ['code','msg']

                ]);

            }

        }
    }


	//actionCheckTransactionId => This function is used to check original trasaction id of itunes.
	public function actionCheckTransactionId()
    {   

        $Transactions = TableRegistry::get('Transactions'); 

        if ($this->request->is('post')){

            $data = $this->request->data;

            $userId = $this->userid;

            $itune_original_transaction_id = $data['itune_original_transaction_id'];

            $subscription = $Transactions

            ->find()

            ->where(['itune_original_transaction_id'=> $itune_original_transaction_id])

            ->where(['NOW()>`expired_at`'])

            ->first();  

            if(empty($subscription)){

                 $this->set([

                    'msg'=> responseMsg(210),

                    'data' => '',

                    'code'  => 200,

                    '_serialize' => ['code','msg','data']

                 ]);

            }else{

                $this->set([

                    'msg'=> responseMsg(436),

                    'data' => '',

                    'code'  => 436,

                    '_serialize' => ['code','msg','data']

                 ]);

            }

        }
    }  

	

	///androidSubscreption
	public function androidSubscreption($arg,$userId) {

        $request = $this->request;
        $postData = $arg;
		$u_id =  $userId;
        

       
        $requestStatus = 1;

        if( !isset($postData['orderId']) ) { $requestStatus = 0; }

        if( !isset($postData['productId']) ) { $requestStatus = 0; }

        if( !isset($postData['packageName']) ) { $requestStatus = 0; }

        if( !isset($postData['autoRenewing']) ) { $requestStatus = 0; }

        if( !isset($postData['purchaseToken']) ) { $requestStatus = 0; }

        if( !isset($postData['purchaseTime']) ) { $requestStatus = 0; }

        



        if($requestStatus==1) { 



            $user_id = $this->userid;

            /*$subTable = TableRegistry::get('Subscreption'); 

            $subData = $subTable->find()

                        ->where(['user_id'=>$user_id, 'status'=>1])

                        ->first();*/



            /*if(!empty($subData)) {



                $Result['code'] = '217';

                $Result['message'] = $this->ErrorMessages($Result['code']);

                echo json_encode($Result); exit;



            } else {*/



                require_once app_path().'/GoogleClientApi/Google_Client.php';

                require_once app_path().'/GoogleClientApi/auth/Google_AssertionCredentials.php';



            $CLIENT_ID = '100377813809460893738';

                //'110053402852490647256';

            $SERVICE_ACCOUNT_NAME = 'hopple-subscriptions@hopple.iam.gserviceaccount.com';
            $KEY_FILE = app_path().'/GoogleClientApi/hopple-39e53e5c539b.p12';

            $KEY_PW   = 'notasecret';



            $key = file_get_contents($KEY_FILE);

            $client = new \Google_Client();

            $client->setApplicationName("hopple");



                $cred = new \Google_AssertionCredentials(

                            $SERVICE_ACCOUNT_NAME,

                            array('https://www.googleapis.com/auth/androidpublisher'),

                            $key);  



                $client->setAssertionCredentials($cred);

                $client->setClientId($CLIENT_ID);

               

                if ($client->getAuth()->isAccessTokenExpired()) {

                    try {

                        $client->getAuth()->refreshTokenWithAssertion($cred);

                    } catch (Exception $e) {

                    }

                }

                $token = json_decode($client->getAccessToken());
                //print_r($token); exit;
                    

                $expireTime = "";

                $amount = 0;

                if( isset($token->access_token) && !empty($token->access_token) ) {

                    $appid = $postData['packageName'];

                    $productID = $postData['productId'];

                    $purchaseToken = $postData['purchaseToken'];



                    $ch = curl_init();

                    $VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/";

                    $VALIDATE_URL .= $appid."/purchases/subscriptions/".$productID."/tokens/".$purchaseToken;

                    $res = $token->access_token;
                    //print_r($res); exit;



                    $ch = curl_init();

                    curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$res);

                    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

                    $result = curl_exec($ch);

                    $result = json_decode($result, true);

                    //print_r($result); exit;

                    

                    if(isset($result["startTimeMillis"])) {

                        $startTime = date('Y-m-d H:i:s', $result["startTimeMillis"]/1000. - date("Z"));

                        //$amount = $result["priceAmountMicros"]/1000000;

                    }

                    if(isset($result["expiryTimeMillis"])) {

                        $expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z"));

                        $amount = $result["priceAmountMicros"]/1000000;

                    }

                }

                if(!empty($result)){
	                $date = new \DateTime();

	                $date->setTimestamp($postData['purchaseTime']/1000);

	                $dateStart = $date->format('Y-m-d H:i:s');

	                $transactionData = new Transaction();
					$transactionData->user_id = $u_id;
					$transactionData->subscription_id = 1;
					$transactionData->total_amount 	= $amount;
					$transactionData->payment_status 	=  1;
					$transactionData->itune_original_transaction_id = $postData['orderId'];
					$transactionData->itunes_receipt = $result["orderId"];
					$transactionData->orderId = $result["orderId"];
					$transactionData->packageName = $postData['packageName'];
					$transactionData->productId = $productID;
					$transactionData->purchaseState =  @$postData['purchaseState'];
					$transactionData->created_at =  $dateStart;
					$transactionData->expired_at =  $expireTime;
					$transactionData->device_type = 2;
					$transactionData->purchaseToken = $postData['purchaseToken'];
					if ($result = $transactionData->save()){
	                    $transaction_last_id = $transactionData->id;
	                  	$user = User::where('id', $u_id)
					       		->update([
					           'itunes_autorenewal' => 1 ,'is_subscribe' => 1,'active_subscription' => 1,
					           'last_transaction_id' => $transaction_last_id
				        ]);	
	                 
	                   	$is_success = 221;
					    //print_r($query); exit;


	                }else{
	                    $is_success = 423;

	                }
	            }else{
	            	$is_success = 429;
	            }

        } else {

             $is_success = 424;

        }
        return $is_success;
        
    }


	//cronJobForSubscreption 
	public function cronJobForSubscreption() { //use for  cron
   

        $Result['code'] = '200';

        $request = $this->request;

        $requestStatus = 1;

        if($requestStatus==1) { 

             $currentDate = date('Y-m-d H:i:s');

            //$transactionsTable = TableRegistry::get('Transactions');

           /* $subData = $transactionsTable->find()

                        ->where(['expired_at < '=>$currentDate])

                        ->ToArray();*/
            $subData = Transaction::where('expired_at', '<', $currentDate)
	        ->get();
	        echo $currentDate;
	        //echo '<pre>'; print_r($subData); 
            if(!empty($subData) && count($subData)) {

                //---- get auth token ---------------

                require_once app_path().'/GoogleClientApi/Google_Client.php';

                require_once app_path().'/GoogleClientApi/auth/Google_AssertionCredentials.php';

                $CLIENT_ID = '100377813809460893738';

                    //'110053402852490647256';

                $SERVICE_ACCOUNT_NAME = 'hopple-subscriptions@hopple.iam.gserviceaccount.com';
                $KEY_FILE = app_path().'/GoogleClientApi/hopple-39e53e5c539b.p12';

                $KEY_PW   = 'notasecret';



                $key = file_get_contents($KEY_FILE);

                $client = new \Google_Client();

                $client->setApplicationName("hopple");


                $cred = new \Google_AssertionCredentials(

                            $SERVICE_ACCOUNT_NAME,

                            array('https://www.googleapis.com/auth/androidpublisher'),

                            $key);  



                $client->setAssertionCredentials($cred);

                $client->setClientId($CLIENT_ID);

                

                if ($client->getAuth()->isAccessTokenExpired()) {

                    try {

                        $client->getAuth()->refreshTokenWithAssertion($cred);

                    } catch (Exception $e) {

                    }

                }

                $token = json_decode($client->getAccessToken());





                //---- cron job work  ---------------------



                foreach ($subData as $key => $val) {

                    if( $val->device_type==2 ) {  // android
	                	

                        $expireTime = "";

                        $amount = 0;

                        if( isset($token->access_token) && !empty($token->access_token) ) {

                            $appid = $val->packageName;

                            $productID = $val->productId;

                            $purchaseToken = $val->purchaseToken;



                            $VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/";

                            $VALIDATE_URL .= $appid."/purchases/subscriptions/".$productID."/tokens/".$purchaseToken;

                            $res = $token->access_token;



                            $ch = curl_init();

                            curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$res);

                            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

                            $result = curl_exec($ch);

                            $result = json_decode($result, true);

	                        if(isset($result["expiryTimeMillis"])) {
	                        	echo '<pre>'; print_r($result);

                                $expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z"));
                                echo  $expireTime;
                                $amount = $result["priceAmountMicros"]/1000000;

                            	echo 'SUNIL'.$val->user_id; 

                                if($expireTime > date('Y-m-d H:i:s')) {
                                	echo 'Renew Test Sunil';
                                   /* Transaction::where('id',  $val->user_id)
							       		->update([
							           'expired_at' => $expireTime,
							           'payment_status' => 1
						        	]);	*/

                                    User::where('id',  $val->user_id)
                                    	->where('is_subscribe',0)
							       		->update([
							           'is_subscribe' => 1
						        	]);	

                                 

                                } else {

                                    echo 'Expire Test Sunil Aadroid';
                                    /*Transaction::where('id',  $val->user_id)
							       		->update([
							           'payment_status' => 2
						        	]);	*/
        

                                            
							       	User::where('id',  $val->user_id)
                                    	->where('is_subscribe',1)
							       		->update([
							           'is_subscribe' => 0
						        	]);	


                                    
                                } 



                            }

                        }

                    } else if( $val->device_type==1 ) {   // iphone

                        $itunesReceipt = $val->purchase_token;  

                        //$password = "58c72878cd56401a9c71927679fd9ee5";        

                        $password = "51197df0c08744ca903b0dcc0f0a259a";        

                        $receiptData = '{"receipt-data":"'.$itunesReceipt.'","password":"'. $password .'"}';

                        $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';

                        // $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';    



                        $ch = curl_init($endpoint);

                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        curl_setopt($ch, CURLOPT_POST, true);

                        curl_setopt($ch, CURLOPT_POSTFIELDS, $receiptData);

                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                        $response = curl_exec($ch);

                        $errno = curl_errno($ch);



                        if($errno==0) {



                            $receiptInfo = json_decode($response,true);

                            

                            if( isset($receiptInfo['latest_receipt_info']) && !empty($receiptInfo['latest_receipt_info']) ) {



                                $lastData = end($receiptInfo['latest_receipt_info']);

                                

                                $expireTime = date('Y-m-d H:i:s',strtotime($lastData['expires_date']));



                                if($expireTime > date('Y-m-d H:i:s')) {
                                	echo '<pre>'; print_r($receiptInfo);
                                    echo 'SUNIL'.$val->user_id;

                                    $query = $transactionsTable->query();

                                    $result = $query->update()

                                            ->set(['expired_at' => $expireTime , 'status' => 1])

                                            ->where(['id' => $val->user_id])

                                            ->execute();

                                       User::where('id',  $val->user_id)
                                    	->where('is_subscribe',0)
							       		->update([
							           'is_subscribe' => 1,
							           'active_subscription' => 1
						        	]);	     

                                   /* $salonQuery = $userTable->query();

                                    $salonQuery->update()

                                                    ->set(['active_subscription' => 1])

                                                    ->where(['id' => $val->user_id, 'active_subscription' => 0])

                                                    ->execute();*/

                                } else {

                                    $query = $transactionsTable->query();

                                    /*$result = $query->update()

                                            ->set(['payment_status' => 2])

                                            ->where(['id' => $val->id])

                                            ->execute();*/

                                    User::where('id',  $val->user_id)
                                    	->where('is_subscribe',1)
							       		->update([
							           'is_subscribe' => 0,
							           'active_subscription' => 0
						        	]);	

                                      echo 'Expire Test Sunil IOS';
                                    $salonQuery = $userTable->query();


                                } 
                            }
                        }       
                    }
                }

            } 
        }   

        exit;   
    }

	public function checkchip($data){
		$checkunique = Chip::where('unique_id', $data['unique_id'])->where('u_id', $data['id'])->first();
		if(!empty($checkunique)){
			$rescod = 1;
		
    	}else{

        	$rescod = 0;

    	}
		return $rescod;
	}


	public function chip($data){

		$chip = new Chip();
		$chip->chip_name = $data['chip_name'];
		$chip->unique_id = $data['unique_id'];
		$chip->u_id 	=  $data['id'];
		$chip->save();
		
		return $chip;
	}

	public function chip_data_list($data,$arg){
		//$checkunique = ChipData::where('unique_id', $data['unique_id'])->where('u_id', $arg['id'])->first();
		$chip = ChipData::where('unique_id',$data['unique_id'])->paginate(10,['*'],'page_no');
		//print_r($chip); exit;
		//$chip = ChipData::where('unique_id',$data['unique_id'])->paginate(20,['*'],'page_no');
		$chip_array = array();
		$Chip_list = array();

		foreach($chip as $list){
			$chip_array['c_id'] 			=  	@$list->c_id ? $list->c_id : '';
			$chip_array['u_id'] 			=  	@$list->u_id ? $list->u_id : '';
			$chip_array['unique_id'] 		=  	@$list->unique_id ? $list->unique_id : '';
			$chip_array['data_date_time'] 	=  	@$list->data_date_time ? $list->data_date_time : '';
			$chip_array['cycle_count'] 		=  	@$list->cycle_count ? $list->cycle_count : '';
			$chip_array['status'] 			=  	@$list->status ? $list->status : '';
			
			array_push($Chip_list,$chip_array);
		}
		//echo '<pre>'; print_r($chip); exit;
		
		return $chip;
	}

	public function getquestion($data){
		$user 	=	User::find($data);
		//$photo = Photo::where('p_u_id', $user->id)->where('is_default', 1)->first();
		$getquestionlist =  Faq::where('f_status',1)->get();	
		//print_r($getquestionlist); exit;
		$QuestionData = array();
		$QuestionArr = array();
		foreach($getquestionlist as $list){
			$QuestionData['id'] 		=  @$list->id ? $list->id : '';
			$QuestionData['question'] 	=  @$list->question ? $list->question : '';
			$QuestionData['admin_answer_id'] 	=  @$list->admin_answer_id ? $list->admin_answer_id : 0;
			$is_answer = Answer::where('u_id', $user->id)->where('q_id', $list->id)->first();
			//print_r($is_answer); exit;
			if(!empty($is_answer['id'])){
				$QuestionData['ans_id'] 	=   $is_answer['id'];
				$QuestionData['admin_answer_id'] 	= $is_answer['admin_answer_id'];
				$QuestionData['answer'] 	=   $is_answer['answer'];
			}else{
				$QuestionData['answer'] 	=   '';
			}
			$question_option =  Answer::where('q_id', $list->id)->where('u_id', 1)->get();
			//print_r($question_option); exit;
			if(!empty($question_option)){
				$QuestionData['option'] = array();
				foreach($question_option as $key =>$question_optionlist){
					$QuestionData['option'][$key]['ans_id'] 		=  @$question_optionlist->id ? $question_optionlist->id : '';
					$QuestionData['option'][$key]['admin_answer_id'] 		=  @$question_optionlist->admin_answer_id ? $question_optionlist->admin_answer_id : '';
					$QuestionData['option'][$key]['answer'] 		=  @$question_optionlist->answer ? $question_optionlist->answer : '';
					//$QuestionData['answer'][$key]['answer'] 	=  @$answer_option->answer ? $answer_option->answer : '';
				}
			}
			array_push($QuestionArr,$QuestionData);
			
		}
       	
		return $QuestionArr;
	}


	public function answer($arg,$userId){
		$checkreport = Answer::where('u_id', $userId)->where('q_id', $arg['q_id'])->first();
		if(!empty($checkreport)){
			$deleteanswer =  Answer::where('id',$checkreport['id'])->delete();	
		}
		//print_r($checkreport['id']); exit;	
		$answer = new Answer();
		$answer->u_id = $userId;
		$answer->answer = $arg['answer'];
		$answer->admin_answer_id = $arg['admin_answer_id'];
		$answer->q_id = intval($arg['q_id']);
		$answer->status = 1;
		$answer->save();
		return 1;
	}

	public function answer_delete($data){
		$deleteanswer =  Answer::where('id',$data['id'])->delete();	
		return 1;
	}

	public function notification_match_detail($arg,$user_id){
		$modal     =  "App\Models\PendingMatches";
		$query = $modal::query();

		$user =$query->select('customer.*','pending_matches.*')
				->leftjoin('users as customer','pending_matches.reciver_id','customer.id')
				->where('pending_matches.sender_id','=',@$user_id)
				->where('pending_matches.chat_channel','=',$arg)
				->orderBy('pending_matches.id', 'DESC')->first();
			////////////

			$userData =array();	
			//$userData['myMatch'] = array();
				$userData['isFromSubCategory'] = 0;
			if(isset($user['id'])){
				if($user['is_pending'] == 1){
					$userData['isFromSubCategory'] = 1;
				}
				//	print_r($user); exit;
				$userData['id'] = $user['id'];
				$category = Categories::where('c_id',$user['cat_id'])->first();
				$subcategory = SubCategories::where('sc_id',$user['sub_cat_id'])->first();
				//print_r($subcategory); exit; 
				$photo = Photo::where('p_u_id',  $user['reciver_id'])->where('is_default', 1)->first();
				$userData['c_id'] = @$category['c_id']?$category['c_id']:0;
				$userData['c_name'] = @$category['c_name']?$category['c_name']:'';
				$userData['sc_c_id'] = @$subcategory['sc_id']?$subcategory['sc_id']:0;
				$userData['sc_name'] = @$subcategory['sc_name']?$subcategory['sc_name']:'';
				if(isset($user['phone'])){
					$userData['p_photo'] = @$photo->p_photo? URL('/public/images/'.$photo->p_photo):'';
					$userData['p_id'] = @$photo->p_id? $photo->p_id:0;
			        $userData['first_name'] = $user['first_name'] ? $user['first_name'] : '';
			        $userData['age'] 	= 	$user['age'].' Years';
					$userData['race'] 	= 	$user['race'] ? $user['race'] : 0;

					$userData['occupation_status'] = 	$user['occupation_status'] ? $user['occupation_status'] : 1;
					$userData['occupation'] = 	$user['occupation'] ? $user['occupation'] : '';
					$userData['descr'] = 	$user['description'] ? $user['description'] : '';
					$userData['is_pending'] = 0;
					$userData['sender_id'] = 	$user['sender_id'] ? $user['sender_id'] :'';
					$userData['reciver_id'] = 	$user['reciver_id'] ? $user['reciver_id'] :'';
					$userData['chat_channel'] = 	$user['chat_channel'] ? $user['chat_channel'] :'';
				}else{
					$userData['is_pending'] = 1;

				}
				
			}
			return $userData;
	}

	public function logout($data){

		$rescod = "";
		//print_r($data); exit;
		if ($data) {
        
			$user =  User::findorfail($data);
			$user->device_id = "";
			$user->device_type = 2;
			$user->device_token = "";
			$user->save();

			$user = Auth::user()->token();
        	//$user->revoke();
        	$rescod = 642;

    	}else{

        	$rescod = 461;

    	}
		return $rescod;
	}

	public function deleteAccount($data){
		if($data['delete_type'] == 1){

			$deletepost =  Post::where('u_id',$data['userid'])
			->delete();	


			$deletefav =  Favourite::where('f_user_id',$data['userid'])
			->delete();	


			$deletefollow =  Follow::where('user_id',$data['userid'])
			->delete();


			$deletefollowby =  Follow::where('follow_by',$data['userid'])
			->delete();	
			

			$deletelike =  Like::where('l_user_id',$data['userid'])
			->delete();	

			$deletephoto =  Photo::where('p_u_id',$data['userid'])
			->delete();	


			$deletereport =  Report::where('user_id',$data['userid'])
			->delete();	

			$deletereported =  Report::where('reported_user',$data['userid'])
			->delete();


			$deleteuser =  User::where('id',$data['userid'])
			->delete();
		}else{
			$user =  User::findorfail($data['userid']);
			$user->user_status = 3;
			$user->save();
		}
	
		return 1;
	}

	public function notificationList($data){
		$model 		= "App\Models\Notification";	
		$post_type = @$data['post_type'];
		$u_id = Auth::user()->id;
		$query = $model::query();
		if(isset($post_type)){
			//echo $selected_date ; exit;
			$query =$query->where('post_type','=',@$post_type);
		}

		$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','notifications.*')
				->where('notifications.n_u_id',$u_id)
				->where('notifications.n_status','!=',2)

				->leftjoin('users','notifications.n_sender_id','users.id')
				->orderBy('notifications.n_id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('notifications.n_status','!=',2)
				->where('notifications.n_u_id',$u_id)
				->count();
		$notification = $query;
		return $notification;
	}

	public function block($arg,$userId){
		$checkblock = BlockUser::where('b_by', $userId)->where('b_to', $arg['other_user'])->first();
		if(empty($checkblock)){
			$bolckUser = new BlockUser();
			$bolckUser->b_by = $userId;
			$bolckUser->b_to = $arg['other_user'];
			$bolckUser->save();
			return 1;
		}else{
			$deleteblock =  BlockUser::where('b_by', $userId)->where('b_to', $arg['other_user'])
				->delete();	
			return 0;
		}		
	}


	public function block_list(){
		$model = "App\Models\BlockUser";
		$u_id  =	 Auth::user()->id;
		$query = $model::query();
				
		$query = $query->leftjoin('users','users.id','block_users.b_to')
				->leftjoin('rooms','rooms.sender_id','block_users.b_to')
				->where('block_users.b_by',$u_id)
				->orderBy('block_users.b_id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('b_by',$u_id)->count();
		$partner = $query;
		//echo '<pre>'; print_r($partner); exit;		
	
		return $partner;
	}

	public function block_list1($data,$arg){
		$model 		= "App\Models\BlockUser";
		//$post_type = @$data['post_type'];
		$u_id = Auth::user()->id;
		$query = $model::query();
			

			/*if(isset($partner_type)){
				//echo $selected_date ; exit;
				$query =$query->where('post_type','=',@$post_type);
			}*/

				
		$query = $query->select('users.id as userid','users.unique_id as unique_id','users.username as username','block_users.*')
				//->where('is_active_profile',1)
				->where('block_users.b_by',$u_id)
				->leftjoin('block_users','users.id','block_users.b_by')
				->orderBy('block_users.b_id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('users.id',$arg['id'])
				->count();
		$partner = $query;
			print_r($partner); exit;
		
		
		return $partner;
	}

	public function notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type){
		$user = User::find($sender); //notification sender

		$receiver_detail = User::find($userArr);// Notification Recceiver
		$receiver_name = @$receiver_detail['first_name'];
		$device_token = @$receiver_detail['device_token'];
		if($n_type == 7 ){

			$message = $message;
		}else{
			$message = $user['first_name'].' '.$message;
		}
		// Notification Payload
		$data['userid'] = $sender;
		$data['name'] = $user['first_name'];
		$data['message'] = $message;
		$data['n_type'] = $n_type;
		$data['ref_id'] = $ref_id;

		$notify = array ();
		$notify['receiver_id'] = $userArr;
		$notify['relData'] = $data;
		$notify['message'] = $message;
		//print_r($notify); exit;
		$test =  $this->sendPushNotification($notify); 

		if($n_type != 7){
			if($n_type != 10){
			$this->notification_save($userArr,$notify,$message,$user['first_name'],$n_type,$receiver_name,$device_token);
			}
		}
	}
	// Save Notification
	public function notification_save($receiver_id,$notify,$message,$sender_name,$n_type,$receiver_name,$device_token){
		$notification = new Notification();
		$notification->n_u_id = @$receiver_id;
		$notification->n_sender_id = @Auth::user()->id ? Auth::user()->id:1;
		$notification->n_type = $n_type;
		$notification->n_data = json_encode($notify);
		$notification->n_message = $message;
		$notification->n_name = $sender_name;
		$notification->n_receiver_name = $receiver_name;
		$notification->n_fcm_token = $device_token;
		$notification->n_status  = 0;
		$notification->n_added_date  =  date ( 'Y-m-d H:i:s' );
		$notification->n_update_date  =  date ( 'Y-m-d H:i:s' );
		$notification->save();
	}

	public function createEvent($data){
		//print_r($data); exit;
		$userId = Auth::user()->id;
	    	Event::where('e_u_id', $userId)
       		->update([
           'e_status' => 2
    	]);	
		$send_notification  = 1;
		$event = new Event();
		$event->e_u_id = Auth::user()->id;
		$event->e_channel = @$data['e_channel'] ? $data['e_channel']: '';
		$event->e_token = @$data['e_token'] ? $data['e_token']: '';
		$event->e_status = 1;
		$event->added_date  =  date ( 'Y-m-d H:i:s' );
		//echo '<pre>'; print_r($group); exit;
		$event->save();
		$lastid = $event->id;
		$partner_array['code'] = 200;
		$partner_array['data'] = $lastid;

	     
		//echo '<pre>'; print_r($partner_array); exit;

		return $partner_array;
	}

	public function eventList($data){

		$category = Event::select('events.*','users.*')
			->where('e_status',1)
			->leftjoin('users','events.e_u_id','users.id')
			->paginate(100,['*'],'page_no');
	
		
		

		return $category;;
	}


	
	public function follower_list($data){
		$model 		= "App\Models\Follow";	

		$name = @$data['name'];
		$query = $model::query();
			

		if(isset($name)){
			//echo $selected_date ; exit;
			$query =$query->where('users.first_name','like', '%'.$name.'%');
		}

			$query = $query->select('users.id as userid','users.first_name as first_name','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','follows.*')
				->where('user_id',$data['id'])
				->leftjoin('users','follows.follow_by','users.id')
				->orderBy('follows.id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('user_id',$data['id'])
				->count();
				
		
		$follower = $query;

			
		return $follower;
	}


	public function following_list($data){
		$model 		= "App\Models\Follow";	
		$name = @$data['name'];
		$query = $model::query();
			

		if(isset($name)){
			//echo $selected_date ; exit;
			$query =$query->where('users.first_name','like', '%'.$name.'%');

		}


		$query = $query->select('users.id as userid','users.first_name as first_name','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','follows.*')
				->where('follow_by',$data['id'])
				->leftjoin('users','follows.user_id','users.id')
				->orderBy('follows.id', 'DESC')
				->paginate(10,['*'],'page_no');

		$query->total_count = $model::where('user_id',$data['id'])
				->count();		
	
		$following = $query;

			
		return $following;
	}
} 

