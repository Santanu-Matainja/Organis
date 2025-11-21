<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MyDashboardController extends Controller
{
    public function LoadMyDashboard()
    {
        return view('frontend.my-dashboard');
    }
	
    public function LoadMyOrders()
    {
		$userid = 0;
		if(isset(Auth::user()->id)){
			$userid = Auth::user()->id;
		}
		
		// $datalist = DB::table('order_masters as a')
		// 	->join('order_items as b', 'a.id', '=', 'b.order_master_id')
		// 	->join('payment_method as d', 'a.payment_method_id', '=', 'd.id')
		// 	->join('payment_status as e', 'a.payment_status_id', '=', 'e.id')
		// 	->join('order_status as f', 'a.order_status_id', '=', 'f.id')			
		// 	->select(
		// 		'a.id', 
		// 		'a.customer_id', 
		// 		'a.payment_status_id', 
		// 		'a.order_status_id', 
		// 		'a.order_no', 
		// 		'a.created_at', 
		// 		'a.shipping_title', 
		// 		'a.shipping_fee', 
		// 		DB::raw("SUM(b.total_price) as total_amount"), 
		// 		DB::raw("SUM(b.tax) as tax"), 
		// 		DB::raw("SUM(b.quantity) as total_qty"), 
		// 		'd.method_name', 
		// 		'e.pstatus_name', 
		// 		'f.ostatus_name')
		// 	->where('a.customer_id', '=', $userid)
		// 	->groupBy(
		// 		'a.customer_id', 
		// 		'a.payment_status_id', 
		// 		'a.order_status_id', 
		// 		'a.created_at', 
		// 		'f.ostatus_name', 
		// 		'e.pstatus_name', 
		// 		'd.method_name', 
		// 		'a.shipping_title',
		// 		'a.shipping_fee',  
		// 		'a.order_no', 
		// 		'a.id')
		// 	->orderBy('a.created_at','desc')
		// 	->paginate(20);

		$datalist = DB::table('order_masters as a')
			->join('payment_method as d', 'a.payment_method_id', '=', 'd.id')
			->join('payment_status as e', 'a.payment_status_id', '=', 'e.id')
			->join('order_status as f', 'a.order_status_id', '=', 'f.id')
			->select(
				'a.master_order_no',

				DB::raw("MIN(a.id) as id"),

				DB::raw("MIN(a.customer_id) as customer_id"),
				DB::raw("MIN(a.payment_status_id) as payment_status_id"),
				DB::raw("MIN(a.order_status_id) as order_status_id"),  

				DB::raw("GROUP_CONCAT(f.ostatus_name SEPARATOR ', ') as ostatus_name"), 

				DB::raw("MIN(a.created_at) as created_at"),
				DB::raw("MIN(a.shipping_title) as shipping_title"),
				DB::raw("SUM(a.shipping_fee) as shipping_fee"),

				DB::raw("SUM(a.total_amount) as total_amount"),
				DB::raw("SUM(a.tax) as tax"),
				DB::raw("SUM(a.total_qty) as total_qty"),

				DB::raw("MIN(d.method_name) as method_name"),
				DB::raw("MIN(e.pstatus_name) as pstatus_name"),
			)
			->where('a.customer_id', $userid)
			->groupBy('a.master_order_no')
			->orderBy(DB::raw("MIN(a.created_at)"), 'desc')
			->paginate(20);



		$commisiontable = DB::table('commissions')->limit(1)->first();
		$commissions = $commisiontable->commission;
	
        return view('frontend.my-orders', compact('datalist', 'commissions'));
    }
	
    // public function MyOrderDetails($order_no)
    // {

	// 	$mdata = DB::table('order_masters as a')
	// 		->join('order_items as b', 'a.id', '=', 'b.order_master_id')
	// 		->join('users as c', 'a.seller_id', '=', 'c.id')
	// 		->join('payment_method as d', 'a.payment_method_id', '=', 'd.id')
	// 		->join('payment_status as e', 'a.payment_status_id', '=', 'e.id')
	// 		->join('order_status as f', 'a.order_status_id', '=', 'f.id')			
	// 		->select(
	// 			'a.id', 
	// 			'a.customer_id', 
	// 			'a.payment_status_id', 
	// 			'a.order_status_id', 
	// 			'a.order_no', 
	// 			'a.created_at', 
	// 			'a.shipping_title', 
	// 			'a.shipping_fee', 
	// 			DB::raw("SUM(b.total_price) as total_amount"), 
	// 			DB::raw("SUM(b.tax) as tax"), 
	// 			'a.email as customer_email', 
	// 			'a.name as customer_name', 
	// 			'a.phone as customer_phone', 
	// 			'a.country', 
	// 			'a.state', 
	// 			'a.city', 
	// 			'a.address as customer_address',  
	// 			'd.method_name', 
	// 			'e.pstatus_name', 
	// 			'f.ostatus_name',
	// 			'c.shop_name')
	// 		// ->where('a.id', $id)
	// 		->where('a.master_order_no', $order_no)
	// 		->groupBy(
	// 			'a.customer_id', 
	// 			'a.payment_status_id', 
	// 			'a.order_status_id', 
	// 			'a.created_at',
	// 			'c.shop_name',
	// 			'f.ostatus_name', 
	// 			'e.pstatus_name', 
	// 			'd.method_name', 
	// 			'a.shipping_title', 
	// 			'a.name', 
	// 			'a.phone', 
	// 			'a.country', 
	// 			'a.state', 
	// 			'a.city', 
	// 			'a.email', 
	// 			'a.address', 
	// 			'a.shipping_fee',  
	// 			'a.order_no', 
	// 			'a.id')
	// 		->first();
		
	// 	$datalist = DB::table('order_items')
	// 		->join('products', 'order_items.product_id', '=', 'products.id')
	// 		->select('order_items.*', 'products.title', 'products.f_thumbnail', 'products.id')
			
	// 		->get();
		
	// 	$commisiontable = DB::table('commissions')->limit(1)->first();
	// 	$commissions = $commisiontable->commission;

    //     return view('frontend.order-details', compact('mdata', 'datalist', 'commissions'));
    // }
	public function MyOrderDetails($order_no)
	{
		// ========== ORDER HEADER (AGGREGATED) ==========
		$mdata = DB::table('order_masters as a')
			->leftJoin('users as c', 'a.seller_id', '=', 'c.id')
			->leftJoin('payment_method as d', 'a.payment_method_id', '=', 'd.id')
			->leftJoin('payment_status as e', 'a.payment_status_id', '=', 'e.id')
			->leftJoin('order_status as f', 'a.order_status_id', '=', 'f.id')
			->select(
				DB::raw("MIN(a.id) AS id"),
				'a.master_order_no',
				DB::raw("MIN(a.order_no) AS order_no"),
				DB::raw("MIN(a.customer_id) AS customer_id"),
				DB::raw("MIN(a.payment_status_id) AS payment_status_id"),
				DB::raw("MIN(a.order_status_id) AS order_status_id"),
				DB::raw("MIN(a.created_at) AS created_at"),
				DB::raw("MIN(a.shipping_title) AS shipping_title"),
				DB::raw("SUM(a.shipping_fee) AS shipping_fee"),
				DB::raw("SUM(a.total_amount) AS total_amount"),
				DB::raw("SUM(a.tax) AS tax"),
				DB::raw("MIN(a.email) AS customer_email"),
				DB::raw("MIN(a.name) AS customer_name"),
				DB::raw("MIN(a.phone) AS customer_phone"),
				DB::raw("MIN(a.country) AS country"),
				DB::raw("MIN(a.state) AS state"),
				DB::raw("MIN(a.city) AS city"),
				DB::raw("MIN(a.address) AS customer_address"),
				DB::raw("MIN(d.method_name) AS method_name"),
				DB::raw("MIN(e.pstatus_name) AS pstatus_name"),
				DB::raw("MIN(f.ostatus_name) AS main_ostatus"),
				DB::raw("GROUP_CONCAT(DISTINCT f.ostatus_name SEPARATOR ', ') AS all_statuses"),
				DB::raw("GROUP_CONCAT(DISTINCT c.shop_name SEPARATOR ', ') AS all_sellers")
			)
			->where('a.master_order_no', $order_no)
			->groupBy('a.master_order_no')
			->first();


		// ========== PRODUCT LIST (per-item seller, status, and shipping title) ==========
		$datalist = DB::table('order_items as b')
			->join('order_masters as a', 'b.order_master_id', '=', 'a.id')
			->join('products as p', 'b.product_id', '=', 'p.id')
			->leftJoin('users as u', 'b.seller_id', '=', 'u.id')            // seller of the item
			->leftJoin('delivery_types as dt', 'a.shipping_title', '=', 'dt.lable')            // seller of the item
			->leftJoin('order_status as s', 'a.order_status_id', '=', 's.id') // order status for that order_master row
			->select(
				'b.*',
				'p.title',
				'p.f_thumbnail',
				'p.id as product_id',
				'u.shop_name',
				's.ostatus_name',
				's.id as ostatus_id',
				'a.order_status_id',
				'dt.id as delivaryid',
				'a.shipping_title as item_shipping_title',
				'u.lat',
				'u.lng',
				DB::raw('b.price * b.quantity as total_price') // use quantity column
			)
			->where('a.master_order_no', $order_no)
			->get();


		// ========== COMMISSION ==========
		$commissiontable = DB::table('commissions')->first();
		$commissions = $commissiontable->commission ?? 0;

		return view('frontend.order-details', compact('mdata', 'datalist', 'commissions'));
	}



    public function LoadMyProfile()
    {
		$countries = DB::table('countries')->get();
        return view('frontend.my-profile', compact('countries'));
    }
	
	public function UpdateProfile(Request $request)
    {
		$gtext = gtext();
		
		$id = $request->input('user_id');
		
		$secretkey = $gtext['secretkey'];
		$recaptcha = $gtext['is_recaptcha'];
		if($recaptcha == 1){
			$request->validate([
				'g-recaptcha-response' => 'required',
				'name' => 'required',
				'email' => 'required',
			]);
			
			$captcha = $request->input('g-recaptcha-response');

			$ip = $_SERVER['REMOTE_ADDR'];
			$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($secretkey).'&response='.urlencode($captcha).'&remoteip'.$ip;
			$response = file_get_contents($url);
			$responseKeys = json_decode($response, true);
			if($responseKeys["success"] == false) {
				return redirect("user/register")->withFail(__('The recaptcha field is required'));
			}
		}else{
			$request->validate([
				'name' => 'required',
				'email' => 'required',
			]);
		}
		
		$data = array(
			'name' => $request->input('name'),
			'phone' => $request->input('phone'),
			'address' => $request->input('address'),
			'city' => $request->input('city'),
			'state' => $request->input('state'),
			'zip_code' => $request->input('zip_code'),
			'country_id' => $request->input('country_id'),
			'lat' => $request->input('lat'),
			'lng' => $request->input('lng'),
		);

		$response = User::where('id', $id)->update($data);
		
		if($response){
			return redirect()->back()->withSuccess(__('Data Updated Successfully'));
		}else{
			return redirect()->back()->withFail(__('Data update failed'));
		}
    }
	
    public function LoadChangePassword()
    {
        return view('frontend.change-password');
    }
	
	public function ChangePassword(Request $request)
    {
		$gtext = gtext();

		$secretkey = $gtext['secretkey'];
		$recaptcha = $gtext['is_recaptcha'];
		if($recaptcha == 1){
			$request->validate([
				'g-recaptcha-response' => 'required',
				'current_password' => 'required',
				'password' => 'required|confirmed|min:6',
				'password_confirmation' => 'required'
			]);
			
			$captcha = $request->input('g-recaptcha-response');

			$ip = $_SERVER['REMOTE_ADDR'];
			$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($secretkey).'&response='.urlencode($captcha).'&remoteip'.$ip;
			$response = file_get_contents($url);
			$responseKeys = json_decode($response, true);
			if($responseKeys["success"] == false) {
				return redirect("user/register")->withFail(__('The recaptcha field is required'));
			}
		}else{
			$request->validate([
				'current_password' => 'required',
				'password' => 'required|confirmed|min:6',
				'password_confirmation' => 'required'
			]);
		}

       $hashedPassword = Auth::user()->password;
 
       if (\Hash::check($request->input('current_password'), $hashedPassword )) {
 
			if (!\Hash::check($request->input('password'), $hashedPassword)) {

				$id = Auth::user()->id;

				$data = array(
					'password' => Hash::make($request->input('password')),
					'bactive' => base64_encode($request->input('password'))
				);
				
				$response = User::where('id', $id)->update($data);
				
				if($response){
					return redirect()->back()->withSuccess(__('Your password changed successfully'));
				}else{
					return redirect()->back()->withFail(__('Oops! You are failed change password. Please try again'));
				}
			}else{
				
				return redirect()->back()->withFail(__('New password can not be the old password!'));
			}
 
        }else{
			return redirect()->back()->withFail(__('Current password does not match.'));
		}
	}	
}
