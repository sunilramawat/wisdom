<?php

namespace App\Http\Controllers\admin;

use Route;
use Auth;
use Validator;
use App\User;
use App\Models\TradeManage;
use App\Models\ChipData;
use App\Models\Collection;
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
use Storage;



class CollectionController extends Controller{
		
	public function add(Request $request){
		$CrudRepository = new CrudRepository();
		
		$model 		= "App\Models\Gender";	
		$moduleType =  3; 
		$gender = $CrudRepository->view($model,$moduleType);
		
		$model1 		= "App\Models\Categories";	
		$moduleType1 =  4; 
		$category = $CrudRepository->view($model1,$moduleType1);
		//echo '<pre>'; print_r($category); exit;
		$model 		= "App\Models\SubCategories";	
		$moduleType =  8; 
		$subcategories = $CrudRepository->view($model,$moduleType);
		
		
		$model 		= "App\Models\PartnerType";	
		$moduleType =  16; 
		$eventType = $CrudRepository->view($model,$moduleType);


		/*$model3		= "App\Models\Region";	
		$moduleType =  18; 
		$region = $CrudRepository->view($model3,$moduleType);*/
		//dd($eventType); exit;
		//$gender = Gender::where('status',1);
		//echo '<pre>'; print_r($region); exit;
		return view('admin.Collecion.add',compact('category','subcategories','eventType'));
	}
	public function subCat(Request $request)
    {
        $parent_id = $request->cat_id; 
        $subcategories =DB::table('sub_categories')->select('sc_id','sc_name')
        	->where('sc_c_id',$parent_id)
        	->get();                    
       // print_r($subcategories); exit;
        return response()->json([
            'subcategories' => $subcategories
        ]);
        
    }
	public function save(Request $request){

		$data = $request->all();
		//echo '<pre>'; print_r($data); exit;
		$request->validate([

            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

    
        $imageName = time().'.'.$request->image->extension();  

     
        $path = Storage::disk('s3')->put('images', $request->image);

        $path = Storage::disk('s3')->url($path);
		//echo '<pre>';	print_r($path); exit;
		$rules = array(
                    'title'         =>  'required',
                    'desc'      =>  'required',
                    'author' => "required",
                    /*'photo'      =>  'required',*/
                    /*'location'      =>  'required',
                    'opening' => 'nullable',
            		'closing' => 'nullable',
                    'suitable'      =>  'required|in:1,2,3',
                    'event_type'      =>  'required|in:1,2,3,4',*/
                );

        $validate = Validator::make($data,$rules);
		$filename = time();
		/*if (@$data['image'] != "") {
			//echo 'das'; exit;
			$extension = $data['image']->getClientOriginalExtension();
			if(strtolower($extension) == 'jpg' || strtolower($extension) == 'png' || strtolower($extension) == 'jpeg' ) {
				
				$FileLogo = $filename . '.' .$data['image']->getClientOriginalExtension();
				$destinationPath = 'public/images';
				$data['image']->move($destinationPath, $FileLogo);
				$documentFile = $destinationPath . '/' . $FileLogo;
				$upload = $FileLogo;
			}
		}		
		*/
		
 		//echo '<pre>'; print_r($upload); exit;
	   
 		//echo '<pre>'; print_r($validate); exit;
 		
 		if($validate->fails()){ 
 			return redirect()->back()->withInput()->withErrors($validate);  
 		}else{
 			//echo 'dasda'; exit;
 			$crudRepository = new CrudRepository();
			//$modelName 		= "App\Models\TradeManage";	
			$form_data 	= array(
							'title' => $request->title ?? null,
							'photo' => $path ?? null,
							'desc' => $request->desc ?? null,
							'author' => $request->author ?? null,
							'type' => $request->type ?? 1,
							'category' => $request->category ?? null,
							'amazon_link' => $request->amazon_link ?? null,
							'ebay_link' => $request->ebay_link ?? null,
							'wordery' => $request->wordery ?? null,
							'other_link1' => $request->other_link1 ?? null,
							'other_link2' => $request->other_link2 ?? null,
							'added_at' => date('Y-m-d H:i:s'),
							);
			//echo '<pre>'; print_r($form_data); exit;
			//echo rand(); exit;
			$model     =  "App\Models\Collection";	
			$Users 		=  $crudRepository->addsave($model,$form_data);
			$Message 	= "Added successfully";
			return redirect('admin/collection/view')->with('success',$Message);
 		}
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
		$model 		= "App\Models\Collection";	
		$moduleType =  2; 
		$Partner = $CrudRepository->view($model,$moduleType);
		$current_page = $request->page?$request->page:1;
		$total_count = $Partner->total_count;
		if($total_count<10){
			$row_count = $total_count;	
		}else{
			$row_count = 10;
		}
		//$Trades_manage = $Trades_manage::paginate(5);

		/*echo '<pre>';
		print_r($Trades_manage);die;*/
		return view('admin.Collection.view',compact('Partner'));

	} 
    
   
    public function changestatus(Request $request){
    	//print_r($request->status); exit;

    	$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Collection"; 
		$id 		= $request->id;
		$form_data 	= array('status' => $request->status);
		//print_r($form_data); exit;
		$Changestatus = $CrudRepository->changestatus($model,$id,$form_data);
    		

	} 


	public function edit(Request $request){
		
		$CrudRepository = new CrudRepository();
		
		$model 		= "App\Models\Gender";	
		$moduleType =  3; 
		$gender = $CrudRepository->view($model,$moduleType);
		
		$model1 		= "App\Models\Categories";	
		$moduleType1 =  4; 
		$category = $CrudRepository->view($model1,$moduleType1);
		//echo '<pre>'; print_r($category); exit;
		$model 		= "App\Models\SubCategories";	
		$moduleType =  8; 
		$subcategories = $CrudRepository->view($model,$moduleType);
		
		
		

		
		//dd($eventType); exit;
		//$gender = Gender::where('status',1);
		//echo '<pre>'; print_r($region); exit;
		
		$model 		= "App\Models\Collection";
		$id 		= $request->id;
		$Partner 		= $CrudRepository->edit($model,$id);
		//loecho '<pre>'; print_r($Partner); exit;
		return view('admin.Collection.edit',compact('Partner','category','subcategories'));
	
	}


	public function editsave(Request $request){
		//dd($request); exit;
		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Collection";
		$id 		= $request->id;
		$user 		= $model::findorfail($id);
		$data = $request->all();
		//echo '<pre>'; print_r($user); exit;
		if($request->image != ''){	
			$request->validate([

	            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

	        ]);

	    
	        $imageName = time().'.'.$request->image->extension();  

	     
	        $path = Storage::disk('s3')->put('images', $request->image);

	        $path = Storage::disk('s3')->url($path);

		}			
		//echo '<pre>';	dd($request); exit;
		$rules = array(
                    'title'         =>  'required',
                    'desc'      =>  'required',
                    'author' => "required",
                );

        $validate = Validator::make($data,$rules);
		$filename = time();
		
 		//echo '<pre>'; print_r('dasd'); exit;
 		//echo '<pre>'; print_r($upload); exit;
	   
 		//echo '<pre>'; print_r($validate); exit;
 		
 		if($validate->fails()){ 
 			return redirect()->back()->withInput()->withErrors($validate);  
 		}else{
 			//echo 'dasda'; exit;
 			$crudRepository = new CrudRepository();
			//$modelName 		= "App\Models\TradeManage";	
		
		
			//$trade_logo = $supp_photo?@$supp_photo: null;
	
   			$form_data 	= array(
							'id' => $request->id ?? $user->id,
							'title' => $request->title ?? $user->title,
							'photo' => $path ?? $user->photo,
							'desc' => $request->desc ?? $user->desc,
							'author' => $request->author ?? $user->title,
							'type' => $request->type ?? $user->type,
							'category' => $request->category ?? $user->category,
							'amazon_link' => $request->amazon_link ?? $user->amazon_link,
							'ebay_link' => $request->ebay_link ?? $user->ebay_link,
							'wordery' => $request->wordery ?? $user->wordery,
							'other_link1' => $request->other_link1 ?? $user->other_link1,
							'other_link2' => $request->other_link2 ?? $user->other_link2,
							
							);
			//echo '<pre>'; print_r($form_data); exit;
			//echo rand(); exit;
			$model     =  "App\Models\Collection";	
			$Users 		= $CrudRepository->editsave($model,$id,$form_data);
			//$Users 		=  $crudRepository->addsave($model,$form_data);
			$Message 	= "Updated successfully";
			return redirect('admin/collection/view')->with('success',$Message);
 		}
	
	}

	public function delete(Request $request){

		$CrudRepository = new CrudRepository();
			$model     =  "App\Models\Collection";	
			$id 		= $request->id;
			$Users 		= $CrudRepository->harddelete($model,$id);
			$Message 	= "Delete successfully";
			return redirect('admin/collection/view')->with('error',$Message);
		
	}


	public function detail(Request $request){

	
		$CrudRepository = new CrudRepository();
		
		$model 		= "App\Models\Gender";	
		$moduleType =  3; 
		$gender = $CrudRepository->view($model,$moduleType);
		
		$model1 		= "App\Models\Categories";	
		$moduleType1 =  4; 
		$category = $CrudRepository->view($model1,$moduleType1);
		//echo '<pre>'; print_r($category); exit;
		$model 		= "App\Models\SubCategories";	
		$moduleType =  8; 
		$subcategories = $CrudRepository->view($model,$moduleType);
		
		
		$model 		= "App\Models\PartnerType";	
		$moduleType =  16; 
		$eventType = $CrudRepository->view($model,$moduleType);


		$model3		= "App\Models\Region";	
		$moduleType =  18; 
		$region = $CrudRepository->view($model3,$moduleType);
		//dd($eventType); exit;
		//$gender = Gender::where('status',1);
		//echo '<pre>'; print_r($region); exit;
		
		$model 		= "App\Models\Partner";
		$id 		= $request->id;
		$Partner 		= $CrudRepository->edit($model,$id);
		//echo '<pre>'; print_r($Partner); exit;
		return view('admin.Partner.detail',compact('Partner','category','subcategories','eventType','region'));
	
	}



}
