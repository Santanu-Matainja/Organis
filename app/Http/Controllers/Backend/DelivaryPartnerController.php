<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryPartner;
use App\Models\Media_option;

class DelivaryPartnerController extends Controller
{
    
    //Delivary Partner page load
    public function getdelivarypartnerPageLoad(){

		$statuslist = DB::table('user_status')->orderBy('id', 'asc')->get();

		$countrylist = DB::table('countries')->where('is_publish', '=', 1)->orderBy('country_name', 'asc')->get();
		$media_datalist = Media_option::orderBy('id','desc')->paginate(28);
		
		$AllCount = DeliveryPartner::count();
		$ActiveCount = DeliveryPartner::where('status_id', '=', 1)->count();
		$InactiveCount = DeliveryPartner::where('status_id', '=', 2)->count();
		
		$datalist = DB::table('delivery_partners')
			->orderBy('id', 'asc')
			->paginate(20);
			
        return view('backend.delivarypartner', compact('AllCount', 'ActiveCount', 'InactiveCount', 'statuslist', 'countrylist', 'media_datalist', 'datalist'));
    }

	//Get data for Sellers Pagination
	public function getDelivaryPartnersTableData(Request $request)
	{
		$status = $request->status;
		$search = $request->search;

		if ($request->ajax()) {

			$datalist = DB::table('delivery_partners')
				->when($search != '', function ($query) use ($search) {
					$query->where(function ($q) use ($search) {
						$q->where('name', 'like', '%' . $search . '%')
							->orWhere('email', 'like', '%' . $search . '%')
							->orWhere('phone', 'like', '%' . $search . '%')
							->orWhere('address', 'like', '%' . $search . '%')
							->orWhere('city', 'like', '%' . $search . '%')
							->orWhere('state', 'like', '%' . $search . '%')
							->orWhere('country_id', 'like', '%' . $search . '%')
							->orWhere('vehicle_type', 'like', '%' . $search . '%')
							->orWhere('delivery_range', 'like', '%' . $search . '%')
							->orWhere('license_number', 'like', '%' . $search . '%');
					});
				})
				->when($status == 1, function ($query) {
					$query->where('status_id', 1); // Active
				})
				->when($status == 2, function ($query) {
					$query->where('status_id', 2); // Inactive
				})
				->orderBy('id', 'desc')
				->paginate(20);

			return view('backend.partials.delivarypartners_table', compact('datalist'))->render();
		}
	}


	//Bulk Action for Delivary Partners
	public function bulkActiondelivarypartners(Request $request){
		
		$res = array();

		$idsStr = $request->ids;
		$idsArray = explode(',', $idsStr);
		
		$BulkAction = $request->BulkAction;

		if($BulkAction == 'active'){
			$response = DeliveryPartner::whereIn('id', $idsArray)->update(['status_id' => 1]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'inactive'){
			
			$response = DeliveryPartner::whereIn('id', $idsArray)->update(['status_id' => 2]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'delete'){
			

			$response = DeliveryPartner::whereIn('id', $idsArray)->delete();
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

	//Save data for Sellers
    public function saveDelivaryPartnersData(Request $request){
		$res = array();
		// return $request;

		$id = $request->input('RecordId');
		$name = $request->input('name');
		$email = $request->input('email');
		
		$phone = $request->input('phone');
		$address = $request->input('address');
		$city = $request->input('city');
		$state = $request->input('state');
		$zip_code = $request->input('zip_code');
		$country_id = $request->input('country_id'); 
		$delivery_range = $request->input('delivery_range');
		$vehicle_type = $request->input('vehicle_type'); 
		$license_number = $request->input('license_number');  
		$status_id = $request->input('status_id');
		$photo = $request->input('photo');
		
		$validator_array = array(
			'name' => $request->input('name'),
			'email' => $request->input('email'),
			'phone' => $request->input('phone'),
			'address' => $request->input('address'),
			'city' => $request->input('city'),
			'state' => $request->input('state'),
			'zip_code' => $request->input('zip_code'),
			'country_id' => $request->input('country_id'),
			'delivery_range' => $request->input('delivery_range'),
			'vehicle_type' => $request->input('vehicle_type'),
			'license_number' => $request->input('license_number'),
		);
		
		$validator = Validator::make($validator_array, [
			'name' => 'required|max:191',
			'email' => 'required|max:191',
			'phone' => 'required',
			'address' => 'required',
			'city' => 'required',
			'state' => 'required',
			'zip_code' => 'required',
			'country_id' => 'required',
			'delivery_range' => 'required',
			'vehicle_type' => 'required',
			'license_number' => 'required',
		]);

		$errors = $validator->errors();

		if($errors->has('name')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('name');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('email')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('email');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('phone')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('phone');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('address')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('address');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('city')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('city');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('state')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('state');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('zip_code')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('zip_code');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('country_id')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('country_id');
			$res['id'] = '';
			return response()->json($res);
		}

		if($errors->has('delivery_range')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('delivery_range');
			$res['id'] = '';
			return response()->json($res);
		}

		if($errors->has('vehicle_type')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('vehicle_type');
			$res['id'] = '';
			return response()->json($res);
		}

		if($errors->has('license_number')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('license_number');
			$res['id'] = '';
			return response()->json($res);
		}

		$data = array(
			'name' => $name,
			'email' => $email,
			'phone' => $phone,
			'address' => $address,
			'city' => $city,
			'state' => $state,	
			'zip_code' => $zip_code,
			'country_id' => $country_id,
			'status_id' => $status_id,
			'photo' => $photo,
			'delivery_range' => $delivery_range,
			'vehicle_type' => $vehicle_type,
			'license_number' => $license_number,
		);

		if($id ==''){
			$response = DeliveryPartner::create($data)->id;
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('New Data Added Successfully');
				$res['id'] = $response;
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data insert failed');
				$res['id'] = '';
			}
		}else{
			$response = DeliveryPartner::where('id', $id)->update($data);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
				$res['id'] = $id;
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
				$res['id'] = '';
			}
		}
		
		return response()->json($res);
    }

	//Get data for Sellers by id
    public function getDelivaryPatnersById(Request $request){
		
		$datalist = array(
			'delivarypartners_data' => '', 
		);
		
		$id = $request->id;
		
		$data = DB::table('delivery_partners')->where('id', $id)->first();
		$data->created_at = date('d F, Y', strtotime($data->created_at));
		
		$datalist['delivarypartners_data'] = $data;
		
		return response()->json($datalist);
	}

	//Delete data for Sellers
	public function deletedelivarypartners(Request $request){
		
		$res = array();

		$id = $request->id;

		if($id != ''){

			$response = DeliveryPartner::where('id', $id)->delete();
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
}
