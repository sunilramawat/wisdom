<?php

namespace App\Http\Controllers\admin;

use Route;
use Auth;
use Validator;
use App\User;
use App\Models\TradeManage;
use App\Models\ChipData;
use App\Models\Partner;
use App\Models\Categories;
use App\Models\SubCategories;
use App\Models\Gender;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Utility\CustomVerfication;
use App\Http\Controllers\Utility\SendEmails;
use App\Http\Controllers\Repository\CrudRepository;
use Illuminate\Pagination\Paginator;
use Charts;
use DB;



class ReportController extends Controller{

	public function add(Request $request){

		return view('admin.Cms.add');
	}

	public function save(Request $request){

		$data = $request->all();
		$rules = array('trade_name' => 'required|unique:trade_manage,trade');
 		$validate = Validator::make($data,$rules);
		//echo '<pre>';	print_r($validate); exit;
		
		
 		//echo '<pre>'; print_r($supp_photo ); exit;
	   
 		/*echo '<pre>'; print_r($validate); exit;
 		
 		if($validate->fails()){ 		
 			return redirect()->back()->withInput()->withErrors($validate);  
 		}else{
 		*/	//echo 'dasda'; exit;
 			$crudRepository = new CrudRepository();
			//$modelName 		= "App\Models\TradeManage";	
		
			$trade_name = $request->trade_name ?? null;
			$trade_logo = $supp_photo?@$supp_photo: null;
			$form_data 	= array(
							'trade' => $trade_name,
							'trade_logo' => $trade_logo,
							'block_status' =>false,
							'created_at' => date('Y-m-d H:i:s'),
							'modify_at' => date('Y-m-d H:i:s'),
							);
			//print_r($form_data); 
			//echo rand(); exit;
			$model     =  "App\Models\TradeManage";	
			$Users 		=  $crudRepository->addsave($model,$form_data);
			$Message 	= "Added successfully";
			return redirect('admin/trade/view')->with('success',$Message);
 		
		   /* $Check = $companyservices->companysave($data);

		    if($Check->error_code == 200){
		    	$Message = "successfully added";
				return redirect('admin/Trade/view')->with('success',$Message);
		    }else{
		    	$Message = "something went wrong";
				return redirect()->back()->with('error', $Message);
		    }*/
		//}    
	} 

	public function view(Request $request){

		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Report";	
		$moduleType =  19; 
		$Trades_manage = $CrudRepository->view($model,$moduleType);
		$current_page = $request->page?$request->page:1;
		$total_count = $Trades_manage->total_count;
		if($total_count<10){
			$row_count = $total_count;	
		}else{
			$row_count = 10;
		}
		//$Trades_manage = $Trades_manage::paginate(5);

		//echo '<pre>';print_r($Trades_manage);die;
		return view('admin.Report.view',compact('Trades_manage','current_page','total_count','row_count'));

	} 
    
   
    public function changestatus(Request $request){


    	$CrudRepository = new CrudRepository();
		$model 		= "App\Models\TradeManage"; 
		$id 		= $request->id;
		$form_data 	= array('block_status' => $request->status);
		$Changestatus = $CrudRepository->changestatus($model,$id,$form_data);
    		

	} 


	public function edit(Request $request){
		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Cms";
		$id 		= $request->id;
		$Cms 		= $CrudRepository->edit($model,$id);
		return view('admin.Cms.edit',compact('Cms'));
	
	}


	public function editsave(Request $request){
		//dd($request); exit;
		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Cms";
		$id 		= $request->id;
		$user 		= $model::findorfail($id);

		$form_data 	= array(
						'p_title' => $request->p_title ? $request->p_title : $user->p_title,
						'p_description'  => $request->p_description ? $request->p_description  : $user->p_description);
	
		$Users 		= $CrudRepository->editsave($model,$id,$form_data);
		$Message 	= "update successfully";
		return redirect('admin/cms/view')->with('success',$Message);
	
	}

	public function delete(Request $request){
		//echo '<pre>'; print_r($request); exit;
		$CrudRepository = new CrudRepository();
		
		$model 		= "App\Models\Post";	
		$id 		= $request->id;
		$Users 		= $CrudRepository->postdodelete($model,$id);
		$Message 	= "Delete successfully";
		return redirect('admin/report/view')->with('error',$Message);
		
	}


	public function chipdetail(Request $request){

		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\ChipData";
		$id 		= $request->id;
		$Chips 		= $CrudRepository->getdetail($model,$id);

		return view('Admin.Chips.chip_detail',compact('Chips'));
	}



}
