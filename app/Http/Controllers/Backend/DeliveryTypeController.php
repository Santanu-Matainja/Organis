<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Media_option;
use App\Models\DeliveryType;


class DeliveryTypeController extends Controller
{
    //Delivary Partner page load
    public function getdelivarytypePageLoad(){
	
		$statuslist = DB::table('user_status')->orderBy('id', 'asc')->get();
		
		$AllCount = DeliveryType::count();
		$ActiveCount = DeliveryType::where('status_id', '=', 1)->count();
		$InactiveCount = DeliveryType::where('status_id', '=', 2)->count();
		
		$datalist = DB::table('delivery_types')
			->orderBy('id', 'asc')
			->paginate(20);
			
        return view('backend.delivarytype', compact('AllCount', 'ActiveCount', 'InactiveCount', 'statuslist',  'datalist'));
    }

    //Get data for Sellers Pagination
	public function getDelivarytypeTableData(Request $request)
	{
		$status = $request->status;
		$search = $request->search;

		if ($request->ajax()) {

			$datalist = DB::table('delivery_types')
				->when($search != '', function ($query) use ($search) {
					$query->where(function ($q) use ($search) {
						$q->where('lable', 'like', '%' . $search . '%')
							->orWhere('slug', 'like', '%' . $search . '%')
							->orWhere('perisible', 'like', '%' . $search . '%');
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

			return view('backend.partials.delivarytype_table', compact('datalist'))->render();
		}
	}

    //Bulk Action for Delivary Partners
	public function bulkActiondelivarytype(Request $request){
		
		$res = array();

		$idsStr = $request->ids;
		$idsArray = explode(',', $idsStr);
		
		$BulkAction = $request->BulkAction;

		if($BulkAction == 'active'){
			$response = DeliveryType::whereIn('id', $idsArray)->update(['status_id' => 1]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'inactive'){
			
			$response = DeliveryType::whereIn('id', $idsArray)->update(['status_id' => 2]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'delete'){
			

			$response = DeliveryType::whereIn('id', $idsArray)->delete();
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
    public function saveDelivarytypeData(Request $request){
		$res = array();
		// return $request;

		$id = $request->input('RecordId');
		$lable = $request->input('lable');
		$slug = $request->input('slug');
		
		$perisible = $request->input('perisible');
 
		$status_id = $request->input('status_id'); 
		$shipping_fee = $request->input('shipping_fee');
	
		
		$validator_array = array(
			'lable' => $request->input('lable'),
			'slug' => $request->input('slug'),
			'perisible' => $request->input('perisible'),
		);
		
		$validator = Validator::make($validator_array, [
			'lable' => 'required|max:191',
			'slug' => 'required|max:191',
			'perisible' => 'required',
		]);

		$errors = $validator->errors();

		if($errors->has('lable')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('lable');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('slug')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('slug');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('perisible')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('perisible');
			$res['id'] = '';
			return response()->json($res);
		}
		
		$data = array(
			'lable' => $lable,
			'slug' => $slug,
			'perisible' => $perisible,
			'status_id' => $status_id,
			'shipping_fee' => $shipping_fee,
		);
		
		if($id ==''){
			$response = DeliveryType::create($data)->id;
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
			$response = DeliveryType::where('id', $id)->update($data);
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
    public function getDelivarytypeById(Request $request){
		
		$datalist = array(
			'delivarytype_data' => '', 
		);
		
		$id = $request->id;
		
		$data = DB::table('delivery_types')->where('id', $id)->first();
		$data->created_at = date('d F, Y', strtotime($data->created_at));
		
		$datalist['delivarytype_data'] = $data;
		
		return response()->json($datalist);
	}

    //Delete data for Sellers
	public function deletedelivarytype(Request $request){
		
		$res = array();

		$id = $request->id;

		if($id != ''){

			$response = DeliveryType::where('id', $id)->delete();
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
