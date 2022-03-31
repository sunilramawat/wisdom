<?php

namespace App\Http\Controllers\Repository;

use App\User;
use App\Models\TradeManage;
use App\Models\SupplierManage;
use App\Models\ProductManage;
use App\Models\UserAuthority;
use App\Models\Partner;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Categories;
use App\Models\PartnerType;
use App\Models\Answer;
use App\Http\Controllers\Utility\CustomVerfication;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Utility\SendEmails;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;

Class CrudRepository  extends Controller{

	public function view($modal,$moduleType,$id=null){
		//print_r($modal); exit;
		//For user Module 
		if($moduleType == 1){
			//Customer List

			$from = @$id['fromDate'];
			$to = @$id['toDate'];

			$query = $modal::query();
			if(isset($from) && isset($to)){
				$query = $query->whereDate('created_date', '>=', $from);
				$query = $query->whereDate('created_date', '<=', $to);
				//$query = $query->whereBetween('created_date', [$from, $to]);
			}
			$query = $query->select('id','email','first_name','last_name','phone','user_status','is_subscribe')
					//->where('isdelete',0)
					//->where('user_status',1)
					->where('user_type',1)
					//->where('address.set_default','=',1)
					->orderBy('id', 'DESC')
					//->join('address','jhi_user.id','address.user_id')
					->paginate(100000);

			$query->total_count = $modal::where('isdelete',0)
					->count();
			$getdata = $query;

				
			//echo '<pre>';print_r($getdata); exit;
		}
		//2  For Trade 
		if($moduleType == 2){
				$getdata=  $modal::select('collections.*','categories.*'/*,'sub_categories.*'*/,'partner_types.id as p_id','partner_types.name as p_name')
				
				->leftjoin('categories','collections.category','categories.c_id')
				//->leftjoin('sub_categories','partners.sub_category','sub_categories.sc_c_id')
				->leftjoin('partner_types','collections.type','partner_types.id')
				->orderBy('collections.id', 'ASC')
				->groupBy('collections.id')
				->paginate(100000);
		
				$getdata->total_count = $modal::count();
			//echo '<pre>'; print_r($getdata1); exit;
		}
		// 3 For Sub Trade
		if($moduleType == 3){
			$getdata= $modal::all(['id', 'gender','status'])->where('status',1)->toArray();
		}

		if($moduleType == 4){  
			//$getdata= $modal::all(['c_id', 'c_name'])->where('c_status',1)->toArray();
			$getdata =DB::table('categories')->pluck('c_name', 'c_id')->toArray();
			//dd($getdata);
		}

		if($moduleType == 5){	
			$from = @$id['fromDate'];
			$to = @$id['toDate'];

			$query = $modal::query();
			if(isset($from) && isset($to)){
				$query = $query->whereDate('created_date', '>=', $from);
				$query = $query->whereDate('created_date', '<=', $to);
				//$query = $query->whereBetween('created_date', [$from, $to]);
			}
			$query = $query->select('id','email','first_name','last_name','phone')
					->where('isdelete',0)
					->where('user_status',1)
					->where('user_type',1)
					->where('is_subscribe',1)
					//->where('address.set_default','=',1)
					->orderBy('id', 'DESC')
					//->join('address','jhi_user.id','address.user_id')
					->paginate(100000);

			$query->total_count = $modal::where('isdelete',0)->where('user_type',1)->where('is_subscribe',1)
					->count();
			$getdata = $query;
				
			//echo '<pre>';print_r($getdata); exit;
		}

		if($moduleType == 6){ 
			// Pending Request  List
			$getdata= $modal::select('jhi_user_authority.user_id','jhi_user_authority.authority_name','jhi_user.*')
				->where('jhi_user.activated','=',"false")
				->where('jhi_user_authority.authority_name','=','ROLE_BUSINESS')
				/*->where(function($query) {
			        $query->Where('jhi_user_authority.authority_name', '=', 'ROLE_MEMBER')
			            ->orWhere('jhi_user_authority.authority_name','=','ROLE_BUSINESS');
			    })*/
				->orderBy('jhi_user.id', 'DESC')
				->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
				->paginate(100000);

			$getdata->total_count = $modal::where('jhi_user.activated','=',"false")
			->where('jhi_user_authority.authority_name','=','ROLE_BUSINESS')
			/*->where(function($query) {
			        $query->Where('jhi_user_authority.authority_name', '=', 'ROLE_MEMBER')
			            ->orWhere('jhi_user_authority.authority_name','=','ROLE_BUSINESS');
			    })*/
				->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
				->count();
			/*$getdata= $modal::orderBy('id', 'DESC')->paginate(10,['*'],'page_no');
			$getdata->total_count = $modal::where('approve_status','true')->count();*/
				
			//echo '<pre>';print_r($getdata); exit;
		}


		if($moduleType == 7){ 
			// Order  List
			$selected_date = @$id['selected_date'];
			$from = @$id['fromDate'];
			$to = @$id['toDate'];
			$supplier_id = @$id['supplier'];
			$order_status = @$id['order_status'];
			$order_status1 = @$id['order_status1'];
			$payment_status = @$id['payment_status'];
			$customer_id = @$id['customerId'];
			

			$query = $modal::query();
			if(isset($customer_id)){
				$query =$query->where('order_manage.user_id','=',@$customer_id);
			}

			if(isset($selected_date)){
				//echo $selected_date ; exit;
				$query =$query->where('order_date','=',@$selected_date);
			}


			if(isset($from) && isset($to)){
				//echo 'dasd'; exit;
				$query = $query->whereDate('created_at', '>=', $from);
    			$query = $query->whereDate('created_at', '<=', $to);
    			//$query = $query->whereBetween('created_at', [$from, $to]);
			}
			if(isset($supplier_id)){
				$query =$query->where('order_manage.supplier_id','=',@$supplier_id);
			}
			if(isset($order_status)){
				$query =$query->where('order_manage.order_status','=',@$order_status );
				//$query =$query->where('order_manage.order_status', [1, 5] );
			}

			if(isset($order_status1)){
				$query =$query->where('order_manage.order_status','=',@$order_status1);
			}
			if(isset($payment_status)){
				//echo $payment_status; exit;
				$query =$query->where('order_manage.payment_status','=',@$payment_status);
			}
			$query =$query->select('customer.first_name as customer_name','supplier.business_name as supplier_name','order_manage.*')
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')
				->orderBy('order_manage.id', 'DESC')->paginate(100000);
			//dd($query); exit;

			$query->total_count =  $modal::select('customer.first_name as customer_name','supplier.business_name as supplier_name','order_manage.*')
				//->where('jhi_user.activated','=',"false")
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')	
				->orderBy('order_manage.id', 'DESC')
				->count();
			$getdata = $query;
				
			//echo '<pre>';print_r($getdata); exit;
		}

		// 8 For Supplier Product
		if($moduleType == 8){
			$getdata= $modal::all(['sc_id', 'sc_c_id','sc_name','sc_image','sc_status'])->where('sc_status',1)->toArray();
		}

		if($moduleType == 9){
			$getdata= $modal::with('ProductDetailManage')->where('trade_id',$id)->where('soft_delete',0)->orderBy('id', 'DESC')->paginate(100000);
			$getdata->total_count = $modal::with('ProductDetailManage')->where('trade_id',$id)->count();
		}

		if($moduleType == 10){ 	
			// Earning Dashboard
			$getdata = DB::select("select  sum(service_fee) as earnings from order_manage  
				where payment_status = 1 "); 
	
			//echo '<pre>';print_r($getdata); exit;
		}
		
		if($moduleType == 11){ 
			// Payout  List
			$year = @$id['year'];
			$month = @$id['month'];
			

			$query = $modal::query();
			if(isset($customer_id)){
				$query =$query->where('order_manage.user_id','=',@$customer_id);
			}

			if(isset($selected_date)){
				//echo $selected_date ; exit;
				$query =$query->where('order_date','=',@$selected_date);
			}


			if(isset($from) && isset($to)){
				//echo 'dasd'; exit;
				$query = $query->whereDate('created_at', '>=', $from);
    			$query = $query->whereDate('created_at', '<=', $to);
    			//$query = $query->whereBetween('created_at', [$from, $to]);
			}
			if(isset($supplier_id)){
				$query =$query->where('order_manage.supplier_id','=',@$supplier_id);
			}
			if(isset($order_status)){
				$query =$query->where('order_manage.order_status','=',@$order_status);
			}
			if(isset($payment_status)){
				//echo $payment_status; exit;
				$query =$query->where('order_manage.payment_status','=',@$payment_status);
			}
			//echo '<pre>';print_r($query); exit;
			$query =$query->select('customer.first_name as customer_name','supplier.business_name as supplier_name','order_manage.*')
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')
				->orderBy('order_manage.id', 'DESC')->paginate(100000);

			$query->total_count =  $modal::select('customer.first_name as customer_name','supplier.business_name as supplier_name','order_manage.*')
				//->where('jhi_user.activated','=',"false")
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')	
				->orderBy('order_manage.id', 'DESC')
				->count();
			$getdata = $query;
				
			//echo '<pre>';print_r($getdata); exit;
		}

		if($moduleType == 12){
			$getdata= $modal::with('ProductDetailManage')->where('sub_trade_id',$id)->where('soft_delete',0)->orderBy('id', 'DESC')->paginate(100000);
			$getdata->total_count = $modal::with('ProductDetailManage')->where('sub_trade_id',$id)->count();
		}

		if($moduleType == 13){
			$getdata= $modal::paginate(100000);
			$getdata->total_count = $modal::count();
		}
		if($moduleType == 14){ 
			// Payout List
			$selected_date = @$id['selected_date'];
			$year = @$id['cust_year']; 
			$month = @$id['cust_month'];
			
			//dd($year);
			$query = $modal::query();
			$query = $query->whereYear('created_at', '=', $year);
    		$query = $query->whereMonth('created_at', '=', $month);
			//dd($query); exit;
    		
    		/*if(isset($customer_id)){
				$query =$query->where('order_manage.user_id','=',@$customer_id);
			}

			if(isset($selected_date)){
				//echo $selected_date ; exit;
				$query =$query->where('order_date','=',@$selected_date);
			}


			if(isset($from) && isset($to)){
				//echo 'dasd'; exit;
								$query = $query->whereDate('created_at', '>=', $from);
				    			$query = $query->whereDate('created_at', '<=', $to);
				    			//$query = $query->whereBetween('created_at', [$from, $to]);
			}
			if(isset($supplier_id)){
				$query =$query->where('order_manage.supplier_id','=',@$supplier_id);
			}
			if(isset($order_status)){
				$query =$query->where('order_manage.order_status','=',@$order_status);
			}*/
			if(isset($payment_status)){
				//echo $payment_status; exit;
				$query =$query->where('order_payout.payment_status','=',@$payment_status);
			}
			$query =$query->select('supplier.business_name as supplier_name','order_payout.*')
				->leftjoin('jhi_user as  supplier','order_payout.supplier_id','supplier.id')
				->orderBy('order_payout.id', 'DESC')->paginate(100000);

			$query->total_count =  $modal::select('supplier.business_name as supplier_name','order_payout.*')
				//->where('jhi_user.activated','=',"false")
				->leftjoin('jhi_user as  supplier','order_payout.supplier_id','supplier.id')	
				->orderBy('order_payout.id', 'DESC')
				->count();
			$getdata = $query;
				
			//echo '<pre>';print_r($getdata); exit;
		}

		if($moduleType == 15){ 
			// Order canclled  List
			$selected_date = @$id['selected_date'];
			$from = @$id['fromDate'];
			$to = @$id['toDate'];
			$supplier_id = @$id['supplier'];
			$order_status = @$id['order_status'];
			$order_status1 = @$id['order_status1'];
			$payment_status = @$id['payment_status'];
			$customer_id = @$id['customerId'];
			

			$query = $modal::query();
			if(isset($customer_id)){
				$query =$query->where('order_manage.user_id','=',@$customer_id);
			}

			if(isset($selected_date)){
				//echo $selected_date ; exit;
				$query =$query->where('order_date','=',@$selected_date);
			}


			if(isset($from) && isset($to)){
				//echo 'dasd'; exit;
				$query = $query->whereDate('created_at', '>=', $from);
    			$query = $query->whereDate('created_at', '<=', $to);
    			//$query = $query->whereBetween('created_at', [$from, $to]);
			}
			if(isset($supplier_id)){
				$query =$query->where('order_manage.supplier_id','=',@$supplier_id);
			}
			if(isset($order_status)){
				//$query =$query->where('order_manage.order_status', [1,5] );
				$query =$query->where('order_manage.order_status','=',@$order_status );
			}

			
			if(isset($payment_status)){
				//echo $payment_status; exit;
				$query =$query->where('order_manage.payment_status','=',@$payment_status);
			}
			$query =$query->select('customer.first_name as customer_name','supplier.business_name as supplier_name','order_manage.*')
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')
				->orderBy('order_manage.id', 'DESC')->paginate(100000);
			//dd($query); exit;

			$query->total_count =  $modal::select('customer.first_name as customer_name','supplier.business_name as supplier_name','order_manage.*')
				//->where('jhi_user.activated','=',"false")
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')	
				->orderBy('order_manage.id', 'DESC')
				->count();
			$getdata = $query;
				
			//echo '<pre>';print_r($getdata); exit;
		}

		if($moduleType == 16){
			$getdata =DB::table('partner_types')->pluck('name', 'id')->toArray();
			
			//$getdata= $modal::all(['id', 'name'])->where('status',1)->toArray();
			//$getdata= $modal::select('id', 'name')->where('status','=',1)->toArray();
			//dd($getdata); exit;
		//echo '<pre>'; print_r($getdata); exit;
		}
		if($moduleType == 18){
			$getdata =DB::table('regions')->pluck('name', 'id')->toArray();
			//dd($getdata); exit;
		//echo '<pre>'; print_r($getdata); exit;
		}
		if($moduleType == 19){
			$query = $modal::query();
			
			/*$query =$query->where('reported_to.isdelete','=',0);
			$query =$query->select(
				'reported_to.id as reportedToUser',
				'reported_to.first_name as reportedToUserName',
				'reported_by.id as reportedByUser',
				'reported_by.first_name as reportedByUserName',
				'reports.*')
				->leftjoin('users as reported_to','reports.reported_user','reported_to.id')
				->leftjoin('users as  reported_by','reports.user_id','reported_by.id')
				->orderBy('reports.id', 'DESC')->paginate(100000);
			//dd($query); exit;

			$query->total_count =  $modal::select(
				'reported_to.id as reportedToUser',
				'reported_to.first_name as reportedToUserName',
				'reported_by.id as reportedByUser',
				'reported_by.first_name as reportedByUserName',
				'reports.*')
				//->where('jhi_user.activated','=',"false")
				->leftjoin('users as reported_to','reports.reported_user','reported_to.id')
				->leftjoin('users as  reported_by','reports.user_id','reported_by.id')
				->orderBy('reports.id', 'DESC')
				->count();
			$getdata = $query;*/
			$query = $query->select('reports.*','posts.id as post_id','posts.description as description','posts.u_id as post_user')
					->leftjoin('posts','reports.post_id','posts.id')
				
					//->where('isdelete',0)
					//->where('user_status',1)
					->where('posts.status',1)
					//->where('address.set_default','=',1)
					->orderBy('posts.id', 'DESC')
					//->join('address','jhi_user.id','address.user_id')
					->paginate(100000);

			$query->total_count = $modal::where('posts.status',1)
			->leftjoin('posts','reports.post_id','posts.id')
					->count();
			$getdata = $query;
			//echo '<pre>';print_r($getdata); exit;
	

			/*$getdata= $modal::with('Users')->orderBy('id', 'DESC')->paginate(100000);
			$getdata->total_count = $modal::with('Users')->count();*/
		}
		if($moduleType == 26){  
			//$getdata= $modal::all(['c_id', 'c_name'])->where('c_status',1)->toArray();
			$getdata =DB::table('collection_types')->pluck('ct_name', 'ct_id')->toArray();
		}

		if($moduleType == 27){  
			//echo 'c_name='.$id['c_name'].'-sss-'.'c_type='.@$id['c_type'].'-sds-'.'c_id='.@$id['id']; exit;
			$getdata =DB::table('categories')->where('c_name',$id['c_name'])
			->count();
				//dd($getdata);
		}



		if($moduleType == 28){  
			//echo 'c_name='.$id['c_name'].'-sss-'.'c_type='.@$id['c_type'].'-sds-'.'c_id='.@$id['id'].'-old-'.'c_name_old='.@$id['c_name_old'];
			if($id['c_name'] == $id['c_name_old']){
				$getdata = 0;
			}else{
			$getdata =DB::table('categories')->where('c_name',$id['c_name'])
				->count();
			
			}
				//dd($getdata);
		}
		if($moduleType == 29){  
			$getdata= $modal::orderBy('c_id', 'DESC')->paginate(100000);
			$getdata->total_count = $modal::count();
			
				//dd($getdata);
		}
		if($moduleType == 30){
				$getdata=  $modal::select('posts.*')
				->orderBy('posts.id', 'ASC')
				->groupBy('posts.id')
				->paginate(100000);
		
				$getdata->total_count = $modal::count();
			//echo '<pre>'; print_r($getdata1); exit;
		}	

		if($moduleType == 31){
				$getdata=  $modal::select('categories.*')
				->orderBy('categories.c_id', 'ASC')
				->groupBy('categories.c_id')
				->paginate(100000);
		
				$getdata->total_count = $modal::count();
			//echo '<pre>'; print_r($getdata1); exit;
		}
	
		//echo '<pre>';print_r($getdata); exit;
		return $getdata;				
	}


	/*public function viewsubtrade($modal,$moduleType,$id){
		//print_r($modal); exit;
		// 3 For Sub Trade
		if($moduleType == 3){
			$getdata= $modal::with('TradeManage')->where('trade_id',$id)->orderBy('sub_trade', 'ASC')->paginate(10,['*'],'page_no');
		}
		//echo '<pre>'; print_r($getdata ); exit;
		return $getdata;				
	}*/

	public function changestatus($model,$id,$form_data){
		/*$form_data); exit;
		$changestatus = $model::findorfail($id);
    	$changestatus->update($form_data);*/
    	$update_user = $model::find($id);
    	//echo '<per'; print_r($update_user); exit;
    	foreach ($form_data as $key => $value) {
    	//print_r($value); exit;
			$update_user->$key =  $value;
			$update_user->save();

    	} 	
		
	} 
	public function editcategory($model,$id){
		//print_r($id); exit;
		$getuser = $model::where('c_id',$id)->first();	
    	//echo '<pre>'; print_r($getuser); exit;
		return $getuser;				
	} 


	public function editcategorysave($model,$id,$form_data){
		//echo $model.'-'.$id; exit;
    	//$update_user = $model::find($id);
    	$update_user = $model::where('c_id',$id)->first();	
    	//echo '<pre>'; print_r($form_data); exit;
    	Categories::where('c_id', $id)
	       		->update(['c_name' => $form_data['c_name'],
	       				  'c_image' => $form_data['c_image']
	
        	]);	

	} 

	public function categorydelete($model,$id){
		//echo $model. '-'.$id; 
    	// $model::findorfail($id)->delete();
    	//dd($id);
    	$getuser =  $model::where('c_id', $id)->delete();
    	
		return $getuser;				
	}

	public function changestatusmain($model,$id,$form_data){
		
		$changestatus = $model::findorfail($id);
    	$changestatus->update($form_data);
		
	} 

	public function search($modal,$moduleType,$keyword,$id=null){

		//$getdata= $modal::with('SubTradeManage')->where();
		/*$getdata = $modal::whereHas('SubTradeManage', function($q){
		    $q->where('created_at', '>=', '2015-01-01 00:00:00');
		})->where('trade','LIKE','%'.$keyword."%")->get();*/
		
		if($moduleType == 1){
			//Customer List
			$getdata= $modal::select('jhi_user_authority.user_id','jhi_user_authority.authority_name','jhi_user.id','jhi_user.email','jhi_user.first_name','jhi_user.last_name','jhi_user.phone_code','jhi_user.phone_number','jhi_user.address')
				->where('jhi_user_authority.authority_name','=','ROLE_CUSTOMER')
				->where('first_name','ilike','%'.$keyword."%")
				->where('is_soft_delete',0)
				->orwhere('email','ilike','%'.$keyword."%")
				->orwhere('address','ilike','%'.$keyword."%")
				->orwhere(DB::raw("CONCAT('phone_code', ' ', 'phone_number')"),'like','%'.$keyword."%")
				->orderBy('jhi_user.id', 'DESC')
				->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
				->paginate(10);
				 $getdata->total_count = $modal::where('jhi_user_authority.authority_name','=','ROLE_CUSTOMER')
				->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
				->count();
			//echo '<pre>';print_r($getdata); exit;
		}

		//2  For Trade 
		if($moduleType == 2){	
			$getdata = $modal::where('trade','ilike','%'.$keyword."%")->paginate(10);
			$getdata->total_count = $getdata->count();
		}	

		if($moduleType == 3){	
			$getdata= $modal::with('TradeManage')->where('trade_id',$id)->where('sub_trade','ilike','%'.$keyword."%")->paginate(10,['*'],'page_no');
			$getdata->total_count = $getdata->count();

		}	

		if($moduleType == 5){
			//Supplier List
			//print_r($keyword); exit;
			$from = $keyword['fromDate'];
			$to = $keyword['toDate'];
			$getdata= $modal::select('jhi_user_authority.user_id','jhi_user_authority.authority_name','jhi_user.*')
				->where('is_soft_delete',false)
				->where(function($query) {
			        $query->Where('jhi_user_authority.authority_name', '=', 'ROLE_MEMBER')
			            ->orWhere('jhi_user_authority.authority_name','=','ROLE_BUSINESS');
			    })
			   	->whereDate('created_date', '>=', $from)
				->whereDate('created_date', '<=', $to)
			    //->whereBetween('created_date', [$from, $to])
				->orderBy('jhi_user.id', 'DESC')
				->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
				->paginate(10000000);
			$getdata->total_count = $modal::where(function($query) {
			        $query->Where('jhi_user_authority.authority_name', '=', 'ROLE_MEMBER')
			            ->orWhere('jhi_user_authority.authority_name','=','ROLE_BUSINESS');
			    })
			    ->whereDate('created_date', '>=', $from)
				->whereDate('created_date', '<=', $to)
			    //->whereBetween('created_date', [$from, $to])
			    ->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
				->count();
			/*$getdata= $modal::orderBy('id', 'DESC')->paginate(10,['*'],'page_no');
			$getdata->total_count = $modal::where('approve_status','true')->count();*/
				/*return datatables()->of($getdata)->make(true);
				*/
			//echo '<pre>';print_r($getdata); exit;
		}
		return $getdata;
    	//$changestatus->update($form_data);
		
	} 

	public function addsave($model,$form_data){

		$form_data = $model::create($form_data);
		return $form_data;

	} 

	public function faqaddsave($model,$form_data){
		//Save Question Table
		$data = $form_data['answer'];
		//print_r($form_data['answer']); exit;
		$form_data = $model::create($form_data);
		$lastid = $form_data->id; 
		// Save in answer Table
		foreach($data as $key => $list){
			if(!empty($list)){
				$ans = new Answer();
				$ans->answer = $list;
				$ans->q_id = $lastid;
				$ans->u_id = 1;
				$ans->status = 1;
				$ans->save();	
			}
		}  
		return $form_data;

	} 



	public function edit($model,$id){
		//print_r($id); exit;
		$getuser = $model::where('id',$id)->first();	
    	//echo '<pre>'; print_r($getuser); exit;
		return $getuser;				
	} 



	public function editsave($model,$id,$form_data){
		
    	$update_user = $model::find($id);

    	foreach ($form_data as $key => $value) {
			
			$update_user->$key =  $value;
			$update_user->save();

    	} 	
	} 


	public function harddelete($model,$id){

    	// $model::findorfail($id)->delete();
    	//dd($user);
    	$getuser =  $model::where('id', $id)->delete();
    	
		return $getuser;				
	}

	public function harddeletefaq($model,$model1,$id){

    	// $model::findorfail($id)->delete();
    	//dd($user);
    	$getuser =  $model::where('id', $id)->delete();

    	$getuserans =  $model1::where('q_id', $id)->delete();
    	
		return $getuser;				
	}


	public function dodelete($model,$id){

    	$getuser = $model::findorfail($id);
    	$getuser->isdelete = 1;
    	$getuser->user_status = 2;
    	$getuser->device_id = "";
    	$getuser->device_type = 2;
    	$getuser->save();
		$getdata = DB::select("DELETE FROM `oauth_access_tokens` WHERE `oauth_access_tokens`.`user_id` = '".$id."'");


		Post::where('u_id', $id)->update(['status' => 2]);	
		
		
		return $getuser;				
	}

	public function postdodelete($model,$id){

    	//echo $model ;  exit;
		Post::where('id', $id)->update(['status' => 2]);	
		
		
		return 1;				
	}

	public function getdetail($model,$id){
		//print_r($model); exit;
		//$getdata = $model::where('id',$id)->first();	
    	//$getuser = $model::find($id);
    	//echo '<pre>'; print_r($getdata); exit;]
		$query = $model::query();
		
		$query =$query->where('id',$id)->first();
		//echo '<pre>';print_r($query); exit;
		$getdata = $query;

		return $getdata;				
	}

	public function orderdetail($model,$id){
		//print_r($model); exit;
		
		$getdata = $model::with('OrderProductlManage.ProductManage')
				->select('customer.first_name as customer_name','customer.phone_code as customer_phone_code','customer.phone_number as customer_phone_number','supplier.business_name as supplier_name','supplier.business_logo_url as photo','order_manage.*','address.address1','address.address2','address.zip_code','address.city_name','address.state_name','address.country_name')
				->where('order_manage.id','=',$id)
				->leftjoin('address','order_manage.user_id','address.user_id')
				->leftjoin('jhi_user as customer','order_manage.user_id','customer.id')
				->leftjoin('jhi_user as  supplier','order_manage.supplier_id','supplier.id')
				//->leftjoin('orders_products_manage','order_manage.id','orders_products_manage.order_id')
				->first();
		//$getuser = $model::find($id);
    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;
	}


	public function productstatus($model,$id,$form_data){
			//echo '<pre>'; print_r($form_data); exit;
			
	    	$update_user = $model::find($id);
	    	foreach ($form_data as $key => $value) {
	    		$update_user->$key =  $value;
	    		//echo '<per>'; print_r($update_user); exit;
				$update_user->save();

	    	} 	
	} 

	public function list($model,$moduleType,$data=null){
		if($moduleType == 5){
			$getdata= $getdata= $model::select('jhi_user.id','jhi_user.business_name as first_name')
					->where('jhi_user_authority.authority_name','=','ROLE_BUSINESS')
					->orWhere('jhi_user_authority.authority_name', '=', 'ROLE_MEMBER')
					->orderBy('jhi_user.business_name', 'ASC')
					->leftjoin('jhi_user','jhi_user_authority.user_id','jhi_user.id')
					->paginate(100000);
			//->orderBy('jhi_user.id', 'DESC')
		}
		//echo '<per>'; print_r($getdata); exit;
		return $getdata;
	}
 
    // Graph Section 
	public function getdetailuser($model,$data){
		if(!empty($data)){
			$getdata = DB::select("select to_char(users.created_date, 'Mon') as month, count(users.id) as customer from users 
					where users.is_delete = 1 and
					EXTRACT(YEAR FROM created_date) = '".$data['cust_year']."' 
					group by (to_char(created_date, 'Mon'))");
		
		}else{
		$getdata = DB::select("select to_char(users.created_date, 'Mon') as month, count(users.id) as customer from users 
				where users.is_delete = 1 
				group by (to_char(created_date, 'Mon'))"); 
		}
		
    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
	}

	public function getdashboarduser($model,$data){
		$getdata = DB::select("SELECT CONCAT(YEAR(created_at), '/', lpad(WEEK(created_at),2,'0')) AS month, COUNT(*) as customer FROM users where created_at BETWEEN (NOW() - INTERVAL 4 WEEK) AND NOW() GROUP BY month ORDER BY month desc limit 4");
		
		

	/*	SELECT CONCAT(YEAR(created_at), '/', lpad(WEEK(created_at),2,'0')) AS week_name, COUNT(*) as countnum FROM users where created_at BETWEEN (NOW() - INTERVAL 4 WEEK) AND NOW() GROUP BY week_name ORDER BY week_name desc limit 4 
		*/
		/*$conn = ConnectionManager::get('default');
        $stmt =  $conn->execute("SELECT count(*) AS cnt, date_format(added_date ,'%Y-%b') as added_date, date_format(added_date ,'%m') as u_month FROM users Users WHERE (added_date > DATE_SUB(now(), INTERVAL 6 MONTH) AND user_type = 1 ) GROUP BY date_format(added_date,'%Y-%m')"); 
        
        $users = $stmt ->fetchAll('assoc');
        $charArr = array();
        foreach ($users as $userskey => $usersvalue) {
            $chatTemp = array();
            $chatTemp[] = $usersvalue['added_date']; 
            $chatTemp[] = $usersvalue['cnt']; 

            $charArr[] = $chatTemp;
            # code...
        }	*/	
		
    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
	}

	public function getdashboardusersuplier($model,$data){
		$getdata = DB::select("SELECT CONCAT(YEAR(created_at), '/', lpad(WEEK(created_at),2,'0')) AS month, COUNT(*) as customer FROM posts where created_at BETWEEN (NOW() - INTERVAL 4 WEEK) AND NOW() GROUP BY month ORDER BY month desc limit 4");


				
		
    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
	}

	public function getdetailusersuplier($model,$data){
		
		if(!empty($data)){
			$getdata = DB::select("select to_char(jhi_user.created_date, 'Mon') as month, count(jhi_user.id) as customer from jhi_user_authority left join jhi_user on jhi_user_authority.user_id = jhi_user.id 
					where jhi_user.is_soft_delete = false and
					EXTRACT(YEAR FROM created_date) = '".$data['cust_year']."' and
					 jhi_user_authority.authority_name = 'ROLE_BUSINESS'
					group by (to_char(created_date, 'Mon'))");
		
		}else{
			$getdata = DB::select("select to_char(jhi_user.created_date, 'Mon') as month, count(jhi_user.id) as customer from jhi_user_authority left join jhi_user on jhi_user_authority.user_id = jhi_user.id 
					where jhi_user.is_soft_delete = false and
					 jhi_user_authority.authority_name = 'ROLE_BUSINESS'
					group by (to_char(created_date, 'Mon'))"); 
		}
		/*$getdata = DB::select("SELECT to_char(created_date, 'Mon') as month, count(id) as customer FROM jhi_user 
			group by (to_char(created_date, 'Mon'))");     
		*/

    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
	}

	public function getdetailusersmember($model,$data){
		
		if(!empty($data)){
			$getdata = DB::select("select to_char(jhi_user.created_date, 'Mon') as month, count(jhi_user.id) as customer from jhi_user_authority left join jhi_user on jhi_user_authority.user_id = jhi_user.id 
					where jhi_user.is_soft_delete = false and
					EXTRACT(YEAR FROM created_date) = '".$data['cust_year']."' and
					jhi_user_authority.authority_name = 'ROLE_MEMBER'
					group by (to_char(created_date, 'Mon'))"); 
		}else{
			$getdata = DB::select("select to_char(jhi_user.created_date, 'Mon') as month, count(jhi_user.id) as customer from jhi_user_authority left join jhi_user on jhi_user_authority.user_id = jhi_user.id 
					where jhi_user.is_soft_delete = false and
					 jhi_user_authority.authority_name = 'ROLE_MEMBER'
					group by (to_char(created_date, 'Mon'))"); 
		}
		/*$getdata = DB::select("SELECT to_char(created_date, 'Mon') as month, count(id) as customer FROM jhi_user 
			group by (to_char(created_date, 'Mon'))");     
		*/

    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
	}

	public function getearning($model,$data){
		
		if(!empty($data)){
			$getdata = DB::select("select to_char(created_at, 'Mon') as month, sum(service_fee) as earnings from order_manage  
				where payment_status = 1 and
				EXTRACT(YEAR FROM created_at) = '".$data['cust_year']."' 
				group by (to_char(created_at, 'Mon'))"); 
		}else{
			$getdata = DB::select("select to_char(created_at, 'Mon') as month, sum(service_fee) as earnings from order_manage  
				where payment_status = 1 
				group by (to_char(created_at, 'Mon'))"); 
		}
	
		
    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
	}

	public function getpayoutuser($model,$data){
		
		if(!empty($data)){
			$getdata = DB::select("select to_char(created_at, 'Mon') as month, sum(total_amount) as customer from order_payout 
					where payment_status = 0 and
				EXTRACT(YEAR FROM created_at) = '".$data['cust_year']."'
					group by (to_char(created_at, 'Mon'))"); 
		}else{
			$getdata = DB::select("select to_char(created_at, 'Mon') as month, sum(total_amount) as customer from order_payout 
					where payment_status = 0
					group by (to_char(created_at, 'Mon'))"); 
		}
	
		

    	//echo '<pre>'; print_r($getdata); exit;
		return $getdata;				
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
} 

