<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Subscriber;
use App\Models\Media_option;
use App\Models\Bank_information;
use App\Models\Withdrawal;
use App\Models\Withdrawal_image;
use App\Models\Product;
use App\Models\Pro_image;
use App\Models\Related_product;
use App\Models\Review;
use App\Models\Order_item;
use App\Models\Order_master;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Carbon\Carbon;
use App\Models\PendingUser;

class SellerController extends Controller
{
    public function LoadSellerRegister()
    {
        return view('frontend.seller-register');
    }
	
    public function SellerRegister(Request $request)
    {
		$gtext = gtext();

		$secretkey = $gtext['secretkey'];
		$recaptcha = $gtext['is_recaptcha'];
		if($recaptcha == 1){
			$request->validate([
				'g-recaptcha-response' => 'required',
				'name' => 'required',
				'email' => 'required|email|unique:users',
				'password' => 'required|confirmed|min:6',
				// 'shop_name' => 'required',
				// 'shop_url' => 'required',
				// 'company_address' => 'required',
				// 'shop_phone' => 'required',
			]);
			
			$captcha = $request->input('g-recaptcha-response');

			$ip = $_SERVER['REMOTE_ADDR'];
			$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($secretkey).'&response='.urlencode($captcha).'&remoteip'.$ip;
			$response = file_get_contents($url);
			$responseKeys = json_decode($response, true);
			if($responseKeys["success"] == false) {
				return redirect("seller/register")->withFail(__('The recaptcha field is required'));
			}
		}else{
			$request->validate([
				'name' => 'required',
				'email' => 'required|email|unique:users',
				'password' => 'required|confirmed|min:6',
				// 'shop_name' => 'required',
				// 'shop_url' => 'required',
				// 'company_address' => 'required',
				// 'shop_phone' => 'required',
			]);
		}
		
		$SellerSettings = gSellerSettings();
		if($SellerSettings['seller_auto_active'] == 1){
			$status_id = 1;
		}else{
			$status_id = 2;
		}
		
		$data = array(
			'name' => $request->input('name'),
			'email' => $request->input('email'),
			'password' => Hash::make($request->input('password')),
			'bactive' => base64_encode($request->input('password')),
			'shop_name' => $request->input('shop_name'),
			'shop_url' => $request->input('shop_url'),
			'phone' => $request->input('shop_phone'),
			'address' => $request->input('company_address'),
			'vat_number' => $request->input('vat_number'),
			'trade_register_number' => $request->input('trade_register_number'),
			'status_id' => $status_id,
			'role_id' => 3
		);
		
		$otp = rand(100000, 999999);
		$data['otp_code'] = $otp;
		$data['otp_expires_at'] = Carbon::now()->addMinutes(10);

		// Save to pending_users
		$pending = PendingUser::updateOrCreate(
			['email' => $request->input('email')], 
			$data 
		);

		// Send OTP Mail via PHPMailer
		$this->sendVerificationMail($pending, $otp);
		
		return redirect()->route('frontend.emailverificationseller', ['email' => $pending->email])->withSuccess('OTP Sent successfully.');

		// $response = User::create($data);
		
		// if ($response) {
		// 	$this->registerNotify($response);

		// 	return redirect()->back()->withSuccess(__('Thanks! You have registered successfully. Please login.'));
		// } else {
		// 	return redirect()->back()->withFail(__('Oops! Registration failed. Please try again.'));
		// }
		// if($response){

		// 	if($gtext['is_mailchimp'] == 1){
		// 		$name = $request->input('name');
		// 		$email_address = $request->input('email');

		// 		$HTTP_Status = self::MailChimpSubscriber($name, $email_address);
		// 		if($HTTP_Status == 200){
		// 			$SubscriberCount = Subscriber::where('email_address', '=', $email_address)->count();
		// 			if($SubscriberCount == 0){
		// 				$data = array(
		// 					'email_address' => $email_address,
		// 					'first_name' => $name,
		// 					'last_name' => $name,
		// 					'status' => 'subscribed'
		// 				);
		// 				Subscriber::create($data);
		// 			}
		// 		}
		// 	}
			
		// 	if($status_id == 1){
		// 		return redirect()->back()->withSuccess(__('Thanks! You have register successfully. Please login.'));
		// 	}else{
		// 		return redirect()->back()->withSuccess(__('Thanks! You have register successfully. Your account is pending for review.'));
		// 	}

		// }else{
		// 	return redirect()->back()->withFail(__('Oops! You are failed registration. Please try again.'));
		// }
    }
	
	//MailChimp Subscriber
    public function MailChimpSubscriber($name, $email){
		$gtext = gtext();

		$apiKey = $gtext['mailchimp_api_key'];
		$listId = $gtext['audience_id'];
		
        //Create mailchimp API url
        $memberId = md5(strtolower($email));
        $dataCenter = substr($apiKey, strpos($apiKey, '-')+1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId; 

        //Member info
        $data = array(
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields'  => [
                'FNAME'     => $name,
                'LNAME'     => $name
            ]
        );

        $jsonString = json_encode($data);

        // send a HTTP POST request with curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
		
		return $httpCode;
    }

	public function registerNotify($user)
	{
		$gtext = gtext();
		$base_url = url('/');

		if ($gtext['ismail'] == 1) {
			try {
				$mail = new PHPMailer(true);
				$mail->CharSet = "UTF-8";

				if ($gtext['mailer'] == 'smtp') {
					$mail->SMTPDebug = 0;
					$mail->isSMTP();
					$mail->Host       = $gtext['smtp_host'];
					$mail->SMTPAuth   = true;
					$mail->Username   = $gtext['smtp_username'];
					$mail->Password   = $gtext['smtp_password'];
					$mail->SMTPSecure = $gtext['smtp_security'];
					$mail->Port       = $gtext['smtp_port'];
				}

				$mail->setFrom($gtext['from_mail'], $gtext['from_name']);
				$mail->addAddress($user->email, $user->name);
				$mail->isHTML(true);
				$mail->CharSet = "utf-8";
				$mail->Subject = __('Welcome to ') . $gtext['company'];

				$mail->Body = '
				<table style="background-color:#edf2f7;color:#111;padding:40px 0;line-height:24px;font-size:14px;" border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td>
							<table style="background-color:#fff;max-width:800px;margin:0 auto;padding:30px;" border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr><td style="font-size:35px;border-bottom:1px solid #ddd;padding-bottom:20px;font-weight:bold;text-align:center;">'.$gtext['company'].'</td></tr>
								<tr><td style="font-size:22px;font-weight:bold;padding:30px 0 10px 0;">'.__('Hi').' '.$user->name.',</td></tr>
								<tr><td>'.__('Welcome to').' '.$gtext['company'].'! '.__('Your Seller account has been successfully created.').'</td></tr>
								<tr><td style="padding-top:20px;">'.__('You can now log in and start exploring our platform.').'</td></tr>
								<tr><td style="padding-top:30px;padding-bottom:40px;text-align:center;">
									<a href="'.$base_url.'/login" style="background:'.$gtext['theme_color'].';color:#fff;padding:12px 25px;text-decoration:none;border-radius:5px;">'.__('Login Now').'</a>
								</td></tr>
								<tr><td style="border-top:1px solid #ddd;padding-top:15px;text-align:center;">'.__('If you have any questions, contact us at').' <a href="mailto:'.$gtext['invoice_email'].'">'.$gtext['invoice_email'].'</a></td></tr>
								<tr><td style="padding-top:5px;text-align:center;"><a href="'.$base_url.'">'.$base_url.'</a></td></tr>
							</table>
						</td>
					</tr>
				</table>';

				$mail->send();

				return 1;
			} catch (Exception $e) {
				return 0;
			}
		}

		return 0;
	}

	private function sendVerificationMail($user, $otp)
	{
		$gtext = gtext();
		$base_url = url('/');

		if ($gtext['ismail'] == 1) {
			try {
				$mail = new PHPMailer(true);
				$mail->CharSet = "UTF-8";

				if ($gtext['mailer'] == 'smtp') {
					$mail->SMTPDebug = 0;
					$mail->isSMTP();
					$mail->Host       = $gtext['smtp_host'];
					$mail->SMTPAuth   = true;
					$mail->Username   = $gtext['smtp_username'];
					$mail->Password   = $gtext['smtp_password'];
					$mail->SMTPSecure = $gtext['smtp_security'];
					$mail->Port       = $gtext['smtp_port'];
				}

				$mail->setFrom($gtext['from_mail'], $gtext['from_name']);
				$mail->addAddress($user->email, $user->name);
				$mail->isHTML(true);
				$mail->CharSet = "utf-8";
				$mail->Subject = __('Verify your email - ') . $gtext['company'];

				$mail->Body = '
				<table style="background-color:#edf2f7;color:#111;padding:40px 0;line-height:24px;font-size:14px;" border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td>
							<table style="background-color:#fff;max-width:800px;margin:0 auto;padding:30px;" border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr><td style="font-size:35px;border-bottom:1px solid #ddd;padding-bottom:20px;font-weight:bold;text-align:center;">'.$gtext['company'].'</td></tr>
								<tr><td style="font-size:22px;font-weight:bold;padding:30px 0 10px 0;">Hi '.$user->name.',</td></tr>
								<tr><td>Please verify your email address to complete your registration.</td></tr>
								<tr><td style="padding-top:20px;">Your verification code is:</td></tr>
								<tr><td style="padding-top:10px;font-size:28px;font-weight:bold;text-align:center;">'.$otp.'</td></tr>
								<tr><td style="padding-top:30px;text-align:center;">This code will expire in 10 minutes.</td></tr>
								<tr><td style="padding-top:40px;text-align:center;font-size:13px;color:#777;">If you did not register on '.$gtext['company'].', please ignore this email.</td></tr>
							</table>
						</td>
					</tr>
				</table>';

				$mail->send();

			} catch (Exception $e) {
				\Log::error('Email not sent: '.$e->getMessage());
			}
		}
	}

	public function emailverification($email)
    {
		$pending = PendingUser::where('email', $email)->first();
		$expires_at = $pending->otp_expires_at;
        return view('auth.email.emailverificationseller', compact('email', 'expires_at'));
    }

	public function verifyemailOtp(Request $request)
	{
		$request->validate([
			'email' => 'required|email',
			'otp' => 'required|numeric',
		]);

		$pending = PendingUser::where('email', $request->email)
			->first();

		if (! $pending) {
			return redirect()->back()->withFail('Invalid Email.');
		}

		if ($pending->otp_code != $request->otp) {
			return redirect()->back()->withFail('Oops! Invalid OTP.');
		}

		if ($pending->otp_expires_at < now()) {
			return redirect()->back()->withFail('Oops! OTP Expired.');
		}

		// Move data to users table
		$response = User::create([
			'name' => $pending->name,
			'email' => $pending->email,
			'password' => $pending->password,
			'bactive' => $pending->bactive,
			'shop_name' => $pending->shop_name,
			'shop_url' => $pending->shop_url,
			'phone' => $pending->phone,
			'address' => $pending->address,
			'vat_number' => $pending->vat_number,
			'trade_register_number' => $pending->trade_register_number,
			'status_id' => $pending->status_id,
			'role_id' => $pending->role_id,
		]);

		if ($response) {
			$this->registerNotify($response);
			
			// Delete pending record
			$pending->delete();
			
			return redirect()->route('frontend.login')->withSuccess(__('Thanks! You have registered successfully. Please login.'));
		} else {
			return redirect()->back()->withFail(__('Oops! Registration failed. Please try again.'));
		}
	}

	public function resendOtp(Request $request)
	{
		$request->validate([
			'email' => 'required|email'
		]);

		$pending = PendingUser::where('email', $request->email)->first();

		if (!$pending) {
			return response()->json(['status' => 'fail', 'msg' => 'User not found']);
		}

		$otp = rand(100000, 999999);

		// update
		$pending->otp_code = $otp;
		$pending->otp_expires_at = now()->addMinutes(10);
		$pending->save();

		$this->sendVerificationMail($pending, $otp);

		return response()->json([
			'status' => 'success',
			'msg' => 'OTP Sent successfully.'
		]);
	}
	
	//has shop url Slug
    public function hasShopSlug(Request $request){
		$res = array();
		
		$slug = str_slug($request->shop_url);
        $count = User::where('shop_url', $slug) ->count();
		if($count == 0){
			$res['slug'] = $slug;
			$res['count'] = 0;
		}else{
			$res['slug'] = $slug;
			$res['count'] = 1;
		}
		
		return response()->json($res);
	}
	
	//Sellers page load
    public function getSellersPageLoad(){
		$statuslist = DB::table('user_status')->orderBy('id', 'asc')->get();
		$countrylist = DB::table('countries')->where('is_publish', '=', 1)->orderBy('country_name', 'asc')->get();
		$media_datalist = Media_option::orderBy('id','desc')->paginate(28);
		
		$AllCount = User::where('role_id', '=', 3)->count();
		$ActiveCount = User::where('status_id', '=', 1)->where('role_id', '=', 3)->count();
		$InactiveCount = User::where('status_id', '=', 2)->where('role_id', '=', 3)->count();
		
		$datalist = DB::table('users')
			->join('user_roles', 'users.role_id', '=', 'user_roles.id')
			->join('user_status', 'users.status_id', '=', 'user_status.id')
			->select('users.*', 'user_roles.role', 'user_status.status')
			->where('users.role_id', 3)
			->orderBy('users.id','desc')
			->paginate(20);
			
        return view('backend.sellers', compact('AllCount', 'ActiveCount', 'InactiveCount', 'statuslist', 'countrylist', 'media_datalist', 'datalist'));
    }
	
	//Get data for Sellers Pagination
	public function getSellersTableData(Request $request){
		
		$status = $request->status;
		$search = $request->search;
		
		if($request->ajax()){

			if($search != ''){
						
				$datalist = DB::table('users')
					->join('user_roles', 'users.role_id', '=', 'user_roles.id')
					->join('user_status', 'users.status_id', '=', 'user_status.id')
					->select('users.*', 'user_roles.role', 'user_status.status')
					->where(function ($query) use ($search){
						$query->where('name', 'like', '%'.$search.'%')
							->orWhere('email', 'like', '%'.$search.'%')
							->orWhere('phone', 'like', '%'.$search.'%')
							->orWhere('shop_name', 'like', '%'.$search.'%')
							->orWhere('shop_url', 'like', '%'.$search.'%')
							->orWhere('address', 'like', '%'.$search.'%')
							->orWhere('city', 'like', '%'.$search.'%')
							->orWhere('state', 'like', '%'.$search.'%');
					})
					->where(function ($query) use ($status){
						$query->whereRaw("users.status_id = '".$status."' OR '".$status."' = '0'");
					})
					->where(function ($query) use ($status){
						$query->whereRaw("users.role_id = 3");
					})
					->orderBy('users.id','desc')
					->paginate(20);
			}else{
				
			$datalist = DB::table('users')
				->join('user_roles', 'users.role_id', '=', 'user_roles.id')
				->join('user_status', 'users.status_id', '=', 'user_status.id')
				->select('users.*', 'user_roles.role', 'user_status.status')
				->where(function ($query) use ($status){
					$query->whereRaw("users.status_id = '".$status."' OR '".$status."' = '0'");
				})
				->where(function ($query) use ($status){
					$query->whereRaw("users.role_id = 3");
				})
				->orderBy('users.id','desc')
				->paginate(20);
			}

			return view('backend.partials.sellers_table', compact('datalist'))->render();
		}
	}
	
	//Save data for Sellers
    public function saveSellersData(Request $request){
		$res = array();
		
		$id = $request->input('RecordId');
		$name = $request->input('name');
		$email = $request->input('email');
		$password = $request->input('password');
		$shop_name = $request->input('shop_name');
		$shop_url = str_slug($request->input('shop_url'));
		$phone = $request->input('phone');
		$address = $request->input('address');
		$city = $request->input('city');
		$state = $request->input('state');
		$zip_code = $request->input('zip_code');
		$country_id = $request->input('country_id');
		$status_id = $request->input('status_id');
		$photo = $request->input('photo');
		$vat_number = $request->input('vat_number');
		$trade_register_number = $request->input('trade_register_number');
		$shipping_fee = $request->input('shipping_fee');
		
		$validator_array = array(
			'name' => $request->input('name'),
			'email' => $request->input('email'),
			'password' => $request->input('password'),
			// 'shop_name' => $request->input('shop_name'),
			// 'shop_url' => $request->input('shop_url'),
			'phone' => $request->input('phone'),
			'address' => $request->input('address'),
			'city' => $request->input('city'),
			'state' => $request->input('state'),
			'zip_code' => $request->input('zip_code'),
			'country_id' => $request->input('country_id'),
			'vat_number' => $request->input('vat_number'),
			'trade_register_number' => $request->input('trade_register_number'),
			'shipping_fee' => $request->input('shipping_fee'),
		);
		$rId = $id == '' ? '' : ','.$id;
		$validator = Validator::make($validator_array, [
			'name' => 'required|max:191',
			'email' => 'required|max:191|unique:users,email' . $rId,
			'password' => 'required|max:191',
			// 'shop_name' => 'required',
			// 'shop_url' => 'required',
			'phone' => 'required',
			'address' => 'required',
			'city' => 'required',
			'state' => 'required',
			'zip_code' => 'required',
			'country_id' => 'required',
			'trade_register_number' => 'required',
			'vat_number' => 'required',
			'shipping_fee' => 'required',
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
		
		if($errors->has('password')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('password');
			$res['id'] = '';
			return response()->json($res);
		}
		
		// if($errors->has('shop_name')){
		// 	$res['msgType'] = 'error';
		// 	$res['msg'] = $errors->first('shop_name');
		// 	$res['id'] = '';
		// 	return response()->json($res);
		// }
		
		// if($errors->has('shop_url')){
		// 	$res['msgType'] = 'error';
		// 	$res['msg'] = $errors->first('shop_url');
		// 	$res['id'] = '';
		// 	return response()->json($res);
		// }
		
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
		
		if($errors->has('trade_register_number')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('trade_register_number');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('vat_number')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('vat_number');
			$res['id'] = '';
			return response()->json($res);
		}
		
		if($errors->has('shipping_fee')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('shipping_fee');
			$res['id'] = '';
			return response()->json($res);
		}

		$data = array(
			'name' => $name,
			'email' => $email,
			'password' => Hash::make($password),
			'shop_name' => $shop_name,
			'shop_url' => $shop_url,
			'phone' => $phone,
			'address' => $address,
			'city' => $city,
			'state' => $state,
			'zip_code' => $zip_code,
			'country_id' => $country_id,
			'status_id' => $status_id,
			'photo' => $photo,
			'role_id' => 3,
			'trade_register_number' => $trade_register_number,
			'vat_number' => $vat_number,
			'bactive' => base64_encode($password)
		);

		if ($id == '') {

			$response = User::create($data)->id;  
			$sellerId = $response;

		} else {

			$response = User::where('id', $id)->update($data); 
			$sellerId = $id;
		}

		$shippingfeeinsert = DB::table('sellerdelivaryfees')->updateOrInsert(
			['seller_id' => $sellerId],
			[
				'shipping_fee' => $shipping_fee,
				'updated_at' => now(),
				'created_at' => now()
			]
		);

		if ($response && $shippingfeeinsert) {
			$res['msgType'] = 'success';
			$res['msg'] = ($id == '') ? __('New Data Added Successfully') : __('Data Updated Successfully');
			$res['id'] = $sellerId;
		} else {
			$res['msgType'] = 'error';
			$res['msg'] = __('Operation failed');
			$res['id'] = '';
		}

		
		return response()->json($res);
    }
	
	//Save data for Bank Information
    public function saveBankInformationData(Request $request){
		$res = array();
		
		$id = $request->input('bank_information_id');
		$seller_id = $request->input('seller_id');
		$bank_name = $request->input('bank_name');
		$bank_code = $request->input('bank_code');
		$account_number = $request->input('account_number');
		$account_holder = $request->input('account_holder');
		$paypal_id = $request->input('paypal_id');
		$description = $request->input('description');
		
		$data = array(
			'seller_id' => $seller_id,
			'bank_name' => $bank_name,
			'bank_code' => $bank_code,
			'account_number' => $account_number,
			'account_holder' => $account_holder,
			'paypal_id' => $paypal_id,
			'description' => $description
		);

		if($id ==''){
			$response = Bank_information::create($data)->id;
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
			$response = Bank_information::where('id', $id)->update($data);
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
    public function getSellerById(Request $request){
		$gtext = gtext();
		$lan = glan();
		
		$datalist = array(
			'seller_data' => '', 
			'bank_information' => '', 
			'CurrentBalance' => 0,
			'OrderBalance' => 0,
			'WithdrawalBalance' => 0,
			'TotalProducts' => 0
		);
		
		$id = $request->id;
		
		$data = DB::table('users')->where('id', $id)->first();
		$data->bactive = base64_decode($data->bactive);
		$data->created_at = date('d F, Y', strtotime($data->created_at));
		
		$bankInfoData = DB::table('bank_informations')->where('seller_id', $id)->first();
		
		$datalist['seller_data'] = $data;
		$datalist['bank_information'] = $bankInfoData;
		
		$sql = "SELECT (IFNULL(SUM(b.total_price), 0) + IFNULL(SUM(b.tax), 0)) AS OrderBalance
		FROM order_masters a
		INNER JOIN order_items b ON a.id = b.order_master_id
		WHERE a.payment_status_id = 1
		AND a.order_status_id = 4
		AND a.seller_id = '".$id."';";
		$aRow = DB::select($sql);
		$OrderBalance = $aRow[0]->OrderBalance;
		
		$sql1 = "SELECT (IFNULL(SUM(amount), 0) + IFNULL(SUM(fee_amount), 0)) AS WithdrawalBalance
		FROM withdrawals 
		WHERE seller_id = '".$id."'
		AND status_id = 3;";
		$aRow1 = DB::select($sql1);
		$WithdrawalBalance = $aRow1[0]->WithdrawalBalance;
		$OrderWithdrawalBalance = ($OrderBalance - $WithdrawalBalance);

		if($gtext['currency_position'] == 'left'){
			$datalist['CurrentBalance'] = $gtext['currency_icon'].NumberFormat($OrderWithdrawalBalance);
			$datalist['OrderBalance'] = $gtext['currency_icon'].NumberFormat($OrderBalance);
			$datalist['WithdrawalBalance'] = $gtext['currency_icon'].NumberFormat($WithdrawalBalance);
		}else{
			$datalist['CurrentBalance'] = NumberFormat($OrderWithdrawalBalance).$gtext['currency_icon'];
			$datalist['OrderBalance'] = NumberFormat($OrderBalance).$gtext['currency_icon'];
			$datalist['WithdrawalBalance'] = NumberFormat($WithdrawalBalance).$gtext['currency_icon'];
		}
		
		$sql2 = "SELECT COUNT(id) AS TotalProducts
		FROM products 
		WHERE user_id = '".$id."'
		AND is_publish = 1
		AND lan = '".$lan."';";
		$aRow2 = DB::select($sql2);
		$datalist['TotalProducts'] = $aRow2[0]->TotalProducts;

		$sellerdelivaryfees = DB::table('sellerdelivaryfees')->where('seller_id', $id)->first();
		$sellerdelivaryfee = $sellerdelivaryfees->shipping_fee ?? '' ;

		$datalist['sellerdelivaryfee'] = $sellerdelivaryfee;
		
		return response()->json($datalist);
	}
	
	//Delete data for Sellers
	public function deleteSeller(Request $request){
		
		$res = array();

		$id = $request->id;

		if($id != ''){
			
			$aRows = Product::where('user_id', $id)->get();
			$idsArray = array();
			foreach($aRows as $key => $row){
				$idsArray[$key] = $row->id;
			}
			
			$withdrawalsRows = Withdrawal::where('seller_id', $id)->get();
			$withdrawalIdsArray = array();
			foreach($withdrawalsRows as $key => $row){
				$withdrawalIdsArray[$key] = $row->id;
			}
			
			Order_item::where('seller_id', $id)->delete();
			Order_master::where('seller_id', $id)->delete();
			
			Withdrawal_image::whereIn('withdrawal_id', $withdrawalIdsArray)->delete();
			Withdrawal::where('seller_id', $id)->delete();

			Review::whereIn('item_id', $idsArray)->delete();
			Related_product::whereIn('product_id', $idsArray)->delete();
			Pro_image::whereIn('product_id', $idsArray)->delete();
			Product::where('user_id', $id)->delete();
			
			Bank_information::where('seller_id', $id)->delete();
			$response = User::where('id', $id)->delete();
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
	
	//Bulk Action for Sellers
	public function bulkActionSellers(Request $request){
		
		$res = array();

		$idsStr = $request->ids;
		$idsArray = explode(',', $idsStr);
		
		$BulkAction = $request->BulkAction;

		if($BulkAction == 'active'){
			$response = User::whereIn('id', $idsArray)->update(['status_id' => 1]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'inactive'){
			
			$response = User::whereIn('id', $idsArray)->update(['status_id' => 2]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'delete'){
			
			$aRows = Product::whereIn('user_id', $idsArray)->get();
			$itemIdsArray = array();
			foreach($aRows as $key => $row){
				$itemIdsArray[$key] = $row->id;
			}
			
			$withdrawalsRows = Withdrawal::whereIn('seller_id', $idsArray)->get();
			$withdrawalIdsArray = array();
			foreach($withdrawalsRows as $key => $row){
				$withdrawalIdsArray[$key] = $row->id;
			}
			
			Order_item::whereIn('seller_id', $idsArray)->delete();
			Order_master::whereIn('seller_id', $idsArray)->delete();
			
			Withdrawal_image::whereIn('withdrawal_id', $withdrawalIdsArray)->delete();
			Withdrawal::whereIn('seller_id', $idsArray)->delete();

			Review::whereIn('item_id', $itemIdsArray)->delete();
			Related_product::whereIn('product_id', $itemIdsArray)->delete();
			Pro_image::whereIn('product_id', $itemIdsArray)->delete();
			
			Product::whereIn('user_id', $idsArray)->delete();

			Bank_information::whereIn('seller_id', $idsArray)->delete();
			$response = User::whereIn('id', $idsArray)->delete();
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
