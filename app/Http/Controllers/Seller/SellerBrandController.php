<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Media_option;
use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SellerBrandController extends Controller
{
    //Brands page load
    public function getBrandsPageLoad() {
		
        $id = Auth::user()->id;

		$media_datalist = Media_option::orderBy('id','desc')->paginate(28);
		$languageslist = DB::table('languages')->where('status', 1)->orderBy('language_name', 'asc')->get();
		$statuslist = DB::table('tp_status')->orderBy('id', 'asc')->get();
		
		$datalist = DB::table('brands')
			->join('tp_status', 'brands.is_publish', '=', 'tp_status.id')
			->join('languages', 'brands.lan', '=', 'languages.language_code')
			->select('brands.id', 'brands.name', 'brands.thumbnail', 'brands.is_featured', 'brands.is_publish', 'tp_status.status', 'languages.language_name', 'brands.user_id')
			->orderBy('brands.id','desc')
            ->where('user_id', $id)
			->paginate(10);

        return view('seller.brands', compact('media_datalist', 'languageslist', 'statuslist', 'datalist'));
    }

    //Get data for Brands Pagination
	public function getBrandsTableData(Request $request){

		$search = $request->search;
		$language_code = $request->language_code;
		$id = Auth::user()->id;

		if($request->ajax()){

			if($search != ''){
				$datalist = DB::table('brands')
					->join('tp_status', 'brands.is_publish', '=', 'tp_status.id')
					->join('languages', 'brands.lan', '=', 'languages.language_code')
					->select('brands.id', 'brands.name', 'brands.thumbnail', 'brands.is_featured', 'brands.is_publish', 'tp_status.status', 'languages.language_name', 'brands.user_id')
                    ->where('user_id', $id)
					->where(function ($query) use ($search){
						$query->where('name', 'like', '%'.$search.'%')
							->orWhere('thumbnail', 'like', '%'.$search.'%');
					})
					->where(function ($query) use ($language_code){
						$query->whereRaw("brands.lan = '".$language_code."' OR '".$language_code."' = '0'");
					})
					->orderBy('brands.id','desc')
					->paginate(10);
			}else{
				
				$datalist = DB::table('brands')
					->join('tp_status', 'brands.is_publish', '=', 'tp_status.id')
					->join('languages', 'brands.lan', '=', 'languages.language_code')
					->select('brands.id', 'brands.name', 'brands.thumbnail', 'brands.is_featured', 'brands.is_publish', 'tp_status.status', 'languages.language_name', 'brands.user_id')
                    ->where('user_id', $id)
					->where(function ($query) use ($language_code){
						$query->whereRaw("brands.lan = '".$language_code."' OR '".$language_code."' = '0'");
					})
					->orderBy('brands.id','desc')
					->paginate(10);
			}

			return view('seller.partials.brands_table', compact('datalist'))->render();
		}
	}
	
	//Save data for Brands
    public function saveBrandsData(Request $request){
		$res = array();

        $user_id = Auth::user()->id;
		$id = $request->input('RecordId');
		$name = esc($request->input('name'));
		$lan = $request->input('lan');
		$thumbnail = $request->input('thumbnail');
		$is_featured = $request->input('is_featured');
		// $is_publish = $request->input('is_publish');
		$is_publish = 2;
		
        //image Save
        $destinationPath = public_path('media');
		$dateTime = date('dmYHis');

		$thumbnail = thumbnail($request['media_type']); 
		$width = $thumbnail['width'];
		$height = $thumbnail['height'];

		$file = $request->file('thumbnail');
		$FileName = $dateTime . '-' . $file->getClientOriginalName();
		$ThumFileName = $dateTime . '-' . $width . 'x' . $height . '-' . $file->getClientOriginalName();

		$FileExt = $file->getClientOriginalExtension();
		$Filetype = Str::lower($FileExt);
		$FileRealPath = $file->getRealPath();
		$FileSize = $file->getSize();
		$OriginalFileName = basename($file->getClientOriginalName(), "." . $FileExt);

		if (file_exists($destinationPath . '/' . $FileName)) {
			unlink($destinationPath . '/' . $FileName);
		}

        if (in_array($Filetype, ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'])) {
            if (in_array($Filetype, ['gif', 'ico', 'svg'])) {
                $ThumFileName = $FileName;
            } else {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($FileRealPath);
                $image->scale($width, $height);
                $image->save($destinationPath . '/' . $ThumFileName);
            }       
        }

        
        
		$validator_array = array(
			'name' => $request->input('name'),
			'lan' => $request->input('lan'),
			'is_publish' => $request->input('is_publish')
		);
		
		$validator = Validator::make($validator_array, [
			'name' => 'required|max:191',
			'lan' => 'required',
			'is_publish' => 'required'
		]);

		$errors = $validator->errors();

		if($errors->has('name')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('name');
			return response()->json($res);
		}
		
		if($errors->has('lan')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('lan');
			return response()->json($res);
		}

		if($errors->has('is_publish')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('is_publish');
			return response()->json($res);
		}

		$data = array(
			'name' => $name,
			'lan' => $lan,
			'thumbnail' => $ThumFileName,
			'is_featured' => $is_featured,
			'is_publish' => $is_publish,
			'user_id' => $user_id,
		);

		if($id ==''){
			$response = Brand::create($data);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('New Data Added Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data insert failed');
			}
		}else{
			$response = Brand::where('id', $id)->update($data);
			if($response){
				
				$slug = esc(str_slug($name));
				
				//Update Parent and Child Menu
				gMenuUpdate($id, 'brand', $name, $slug);
				
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
		}
		
		return response()->json($res);
    }
	
	//Get data for Brand by id
    public function getBrandsById(Request $request){

		$id = $request->id;
		
		$data = Brand::where('id', $id)->first();
		
		return response()->json($data);
	}
	
	//Delete data for Brands
	public function deleteBrands(Request $request){
		
		$res = array();

		$id = $request->id;

		if($id != ''){
			$response = Brand::where('id', $id)->delete();
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Removed Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data remove failed');
			}
		}
		
		return response()->json($res);
	}
	
	//Bulk Action for Brands
	public function bulkActionBrands(Request $request){
		
		$res = array();

		$idsStr = $request->ids;
		$idsArray = explode(',', $idsStr);
		
		$BulkAction = $request->BulkAction;

		if($BulkAction == 'publish'){
			$response = Brand::whereIn('id', $idsArray)->update(['is_publish' => 1]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'draft'){
			
			$response = Brand::whereIn('id', $idsArray)->update(['is_publish' => 2]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'delete'){
			$response = Brand::whereIn('id', $idsArray)->delete();
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Removed Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data remove failed');
			}
		}
		
		return response()->json($res);
	}	

    // Image Remove

    public function deleteMediaFile(Request $request)
    {
        $fileName = $request->input('file_name');
        $filePath = public_path('media/' . $fileName);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return response()->json(['status' => 'success']);
    }

}
