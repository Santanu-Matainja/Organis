<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Commission;

class CommissionController extends Controller
{
        //tax page load
    public function getCommissionPageLoad() {
		
		$statuslist = DB::table('tp_status')->orderBy('id', 'asc')->get();
		
		$results = Commission::offset(0)->limit(1)->get();

		$datalist = array('id' => '', 'title' => '', 'commission' => '', 'is_publish' => '');
		foreach ($results as $row){
			$datalist['id'] = $row->id;
			$datalist['title'] = $row->title;
			$datalist['commission'] = $row->commission;
			$datalist['is_publish'] = $row->is_publish;
		}
		
        return view('backend.commission', compact('statuslist', 'datalist'));
    }
	
	//Save data for Tax
    public function saveCommissionData(Request $request){
		$res = array();

		$title = $request->input('title');
		$commission = $request->input('commission');
		$is_publish = $request->input('is_publish');
		
		$validator_array = array(
			'title' => $request->input('title'),
			'commission' => $request->input('commission'),
			'is_publish' => $request->input('is_publish')
		);
		
		$validator = Validator::make($validator_array, [
			'title' => 'required|max:191',
			'commission' => 'required',
			'is_publish' => 'required'
		]);

		$errors = $validator->errors();

		if($errors->has('title')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('title');
			return response()->json($res);
		}
		
		if($errors->has('commission')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('commission');
			return response()->json($res);
		}

		if($errors->has('is_publish')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('is_publish');
			return response()->json($res);
		}

		$data = array(
			'title' => $title,
			'commission' => $commission,
			'is_publish' => $is_publish
		);
		
		$results = Commission::offset(0)->limit(1)->get();
		$id = '';
		foreach ($results as $row){
			$id = $row->id;
		}
		if($id ==''){
			$response = Commission::create($data);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('New Data Added Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data insert failed');
			}
		}else{
			$response = Commission::where('id', $id)->update($data);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
		}
		
		return response()->json($res);
    }
}
