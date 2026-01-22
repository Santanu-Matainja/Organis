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
use App\Models\Order_master;
use App\Models\Order_item;
use App\Models\Country;
use App\Models\Shipping;
use App\Models\DeliveryType;
use App\Models\ShippingAddress;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Services\PayPalService;

use Razorpay\Api\Api;
use Mollie\Laravel\Facades\Mollie;

class CheckoutFrontController extends Controller
{
	
    protected $PayPalClient;

    public function __construct(PayPalService $PayPalClient)
    {
        $this->PayPalClient = $PayPalClient;
    }
	
    public function LoadCheckout()
    {
		$country_list = Country::orderBy('country_name', 'ASC')->get();
		// $shipping_list = Shipping::where('is_publish', '=', 1)->get(); 
		$shipping_list = DeliveryType::where('status_id', '=', 1)->get(); 

		$userId = Auth::id();
		// $ShoppingCartData = session()->get('shopping_cart');
		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();
		$ShoppingCartData = $cartRecord && $cartRecord->cart_data
			? json_decode($cartRecord->cart_data, true)
			: [];

		$commision = DB::table('commissions')->limit(1)->first();

		$shippinginfo = ShippingAddress::where('user_id', $userId )->first();
		
        return view('frontend.checkout', compact('country_list', 'shipping_list', 'ShoppingCartData', 'commision', 'shippinginfo'));
    }
	
    public function LoadThank()
    {	
        return view('frontend.thank');
    }
	
    public function LoadMakeOrder(Request $request)
    {
		$res = array();
		$gtext = gtext();
		// $gtax = getTax();
		// $tax_rate = $gtax['percentage'];
		$taxSlabs = getTax(); // must return ALL rows

		Session::forget('pt_payment_error');
		
		$base_url = url('/');
		
		// $CartDataList = session()->get('shopping_cart');
		$userId = Auth::id();
		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();
		$CartDataList = $cartRecord && $cartRecord->cart_data
			? json_decode($cartRecord->cart_data, true)
			: [];
		
		
		$total_qty = 0;
		$TotalPrice = 0;

		$categoryIds = array_column($CartDataList, 'category_id');

		$categoryParents = DB::table('pro_categories')
			->whereIn('id', $categoryIds)
			->pluck('parent_id', 'id')
			->toArray();

		$getTaxRateForProduct = function ($categoryId) use ($taxSlabs, $categoryParents) {
			foreach ($taxSlabs as $slab) {
				if (!empty($slab['category']) && in_array($categoryId, $slab['category'])) {
					return (float) $slab['percentage'];
				}
			}
			$parentId = $categoryParents[$categoryId] ?? null;
			if ($parentId) {
				foreach ($taxSlabs as $slab) {
					if (!empty($slab['category']) && in_array($parentId, $slab['category'])) {
						return (float) $slab['percentage'];
					}
				}
			}
			foreach ($taxSlabs as $slab) {
				if (empty($slab['category'])) {
					return (float) $slab['percentage'];
				}
			}
			return 0;
		};
	
		// if(session()->get('shopping_cart')){
		if($CartDataList){
			foreach ($CartDataList as $row) {
				$total_qty += $row['qty'];
				$TotalPrice += $row['price']*$row['qty'];
			}
		}
		
		// $TaxCal = ($TotalPrice*$tax_rate)/100;
		// $total_amount = $TotalPrice+$TaxCal;
		
		if($total_qty == 0){
			$res['msgType'] = 'error';
			$res['msg'] = array('oneError' => array(__('Oops! Your order is failed. Please product add to cart.')));
			return response()->json($res);
		}
		
		$CustomerId = '';
		
		$new_account = $request->has('new_account') ? 1 : 0;
		
		$payment_method_id = $request->input('payment_method');
		// $shipping_method_id = $request->input('shipping_method');
		
		$shipping_methods_by_product = $request->input('shipping_method', []); 
		$shipping_id = $request->input('shipping_id', []); 

		$countryname = DB::table('countries')->where('id', $request->input('country'))->first();

		if($new_account == 0){
			
			$validator = Validator::make($request->all(),[
				'name' => 'required',
				'email' => 'required|email',
				'phone' => 'required',
				'country' => 'required',
				'state' => 'required',
				'zip_code' => 'required',
				'city' => 'required',
				'address' => 'required',
				'payment_method' => 'required',
				'shipping_method' => 'required',
				'shipping_id' => 'required'
				// 'email' => 'required|email|unique:users',
				// 'password' => 'required|confirmed',
			]);

			if(!$validator->passes()){
				$res['msgType'] = 'error';
				$res['msg'] = $validator->errors()->toArray();
				return response()->json($res);
			}

			$userData = array(
				'user_id' => $request->input('customer_id'),
				'name' => $request->input('name'),
				'email' => $request->input('email'),
				'phone' => $request->input('phone'),
				'address' => $request->input('address'),
				'state' => $request->input('state'),
				'zip_code' => $request->input('zip_code'),
				'city' => $request->input('city'),
				'country' => $request->input('country'),
			);
			
			$shipping = ShippingAddress::updateOrCreate(
				['user_id' => $request->input('customer_id')],
				$userData
			);

			$CustomerId = $shipping->user_id;
			
		}else{
			
			$validator = Validator::make($request->all(),[
				'name' => 'required',
				'email' => 'required',
				'phone' => 'required',
				'country' => 'required',
				'state' => 'required',
				'zip_code' => 'required',
				'city' => 'required',
				'address' => 'required',
				'payment_method' => 'required',
				'shipping_method' => 'required',
				'shipping_id' => 'required'
			]);
			
			if(!$validator->passes()){
				$res['msgType'] = 'error';
				$res['msg'] = $validator->errors()->toArray();
				return response()->json($res);
			}

			$userData = array(
				'user_id' => $request->input('customer_id'),
				'name' => $request->input('name'),
				'email' => $request->input('email'),
				'phone' => $request->input('phone'),
				'address' => $request->input('address'), 
				'state' => $request->input('state'),
				'zip_code' => $request->input('zip_code'),
				'city' => $request->input('city'),
				'country' => $request->input('country'),
			);
			
			$user = User::where('id', $request->customer_id)->firstOrFail();
			$user->update($userData);

			$CustomerId = $user->id;
		}
		
		if($CustomerId == '') {
			$customer_id = NULL;
		}else {
			$customer_id = $CustomerId;
		}
		
		// $shipping_list = DeliveryType::where('id', '=', $shipping_method_id)->get();
		// $shipping_title = NULL;
		// $shipping_fee = NULL;
		// foreach ($shipping_list as $row){
		// 	$shipping_title = $row->lable;
		// 	$shipping_fee = comma_remove($row->shipping_fee);
		// }

		$UniqueDataArray = array();
		$key = 0;
		foreach($CartDataList as $row){
			
			$UniqueDataArray[$key] = $row['seller_id'];
			
			$key++;
		}
		
		$UniqueDataList = array_unique($UniqueDataArray);
		
		$perSellerShippingFee = [];
		$perSellerShippingTitle = [];
		$perSellerTotals = [];

		foreach ($UniqueDataList as $seller_id) {
			$seller_fee = 0;
			$titles = [];
			$shippingfee = [];
			$seller_total_qty = 0;
			$seller_total_price = 0;
			$seller_tax = 0;
			$seller_discount = 0;
			$seller_subtotal = 0;

			// $selected_shipids = $shipping_id[$seller_id] ?? [];

			foreach ($CartDataList as $cartRow) {

				if ($cartRow['seller_id'] != $seller_id) {
					continue;
				}
				$pId = $cartRow['id'];

				$productShipIds = array_map('trim', explode(',', $cartRow['delivarytypeid']));

				// seller selected shipping IDs
				$sellerSelected = $shipping_id[$seller_id] ?? [];

				// find first match
				$matchedShipId = null;
				foreach ($sellerSelected as $sid) {
					if (in_array($sid, $productShipIds)) {
						$matchedShipId = $sid;
						break;
					}
				}
				if (!$matchedShipId && count($productShipIds) > 0) {
					$matchedShipId = $productShipIds[0];
				}
				$shipping = DeliveryType::find($matchedShipId);
				$titles[] = $pId . ': ' . ($shipping->lable ?? 'N/A');
			}



			// return $selected_shipid = $shipping_id[$seller_id] ?? null;
			// $selected_shipping = DeliveryType::find($selected_shipid);
			// $titles[] = $selected_shipping->lable ?? '';
		
			$methodIds = $shipping_methods_by_product[$seller_id] ?? [];

			$finalShippingFee = 0;

			foreach ($methodIds as $id) {
				$finalShippingFee += floatval($id);
			}


			foreach ($CartDataList as $cartRow) {
				if ($cartRow['seller_id'] == $seller_id) {
					
					$qty = comma_remove($cartRow['qty']);
					$price = comma_remove($cartRow['price']);
					$total_price = $qty * $price;
					// $tax = ($total_price * $tax_rate) / 100;

					$seller_total_qty += $qty;
					$seller_total_price += $total_price;
					// $seller_tax += $tax;

					$categoryId = $cartRow['category_id'] ?? null;
					$taxRate = $getTaxRateForProduct($categoryId);

					$tax = ($total_price * $taxRate) / 100;

					$seller_tax += $tax;

					// shipping price 
					$seller_fee = $finalShippingFee;

    				$shippingfee = $finalShippingFee;
				}
			}
		
			$perSellerShippingFee[$seller_id] = $seller_fee;
			$perSellerShippingTitle[$seller_id] = implode(', ', $titles);
			$ShippingTitle[$seller_id] = $shippingfee;

			$seller_subtotal = $seller_total_price + $seller_tax;
			
			$commision = DB::table('commissions')->limit(1)->first();
			$seller_total_amount = $seller_subtotal + ($perSellerShippingFee[$seller_id] ?? 0) - $seller_discount;

			$perSellerTotals[$seller_id] = [
				'total_qty' => $seller_total_qty,
				'total_price' => $seller_total_price,
				'discount' => $seller_discount,
				'tax' => $seller_tax,
				'commission' => $commision->commission,
				'subtotal' => $seller_subtotal,
				'total_amount' => $seller_total_amount,
			];
		}

		$OrderNoArr = array();
		$master_order_no = 'MORD-' . random_int(100000, 999999);
		$i = 1;
		foreach($UniqueDataList as $row){
			
			$random_code = random_int(100000, 999999);
			
			$order_no = 'ORD-'.$random_code.$i;
			$OrderNoArr[] = $order_no;
			
			$seller_id = $row;
			
			$shipping_title = $perSellerShippingTitle[$seller_id] ?? null;
			$shipping_fee = $ShippingTitle[$seller_id] ?? 0;

			$totals = $perSellerTotals[$seller_id];

			$data = array(
				'master_order_no' => $master_order_no,
				'order_no' => $order_no,
				'customer_id' => $customer_id,
				'seller_id' => $seller_id,
				'payment_method_id' => $payment_method_id,
				'payment_status_id' => 2,
				'order_status_id' => 1,
				'shipping_title' => $shipping_title,
				'shipping_fee' => $shipping_fee,
				'name' => $request->input('name'),
				'email' => $request->input('email'),
				'phone' => $request->input('phone'),
				'country' => $countryname->country_name,
				'state' => $request->input('state'),
				'zip_code' => $request->input('zip_code'),
				'city' => $request->input('city'),
				'address' => $request->input('address'),
				'comments' => $request->input('comments'),

				'total_qty' => $totals['total_qty'],
				'total_price' => $totals['total_price'],
				'discount' => $totals['discount'],
				'tax' => $totals['tax'],
				'commission' => $totals['commission'],
				'subtotal' => $totals['subtotal'],
				'total_amount' => $totals['total_amount'],
			);
			
			$order_master_id = Order_master::create($data)->id;
			
			$i++;
			
			$MasterData[$seller_id] = $order_master_id;
		}

		//set order master ids into session
		Session::put('order_master_ids', $MasterData);

		$index = 0;
		$total_tax = 0;
		foreach($CartDataList as $row){

			$seller_id = $row['seller_id'];
			$order_master_id = $MasterData[$seller_id];
			
			$total_price = $row['price']*$row['qty'];
			
			// $total_tax = (($total_price*$tax_rate)/100);
			$categoryId = $row['category_id'] ?? null;
			$taxRate = $getTaxRateForProduct($categoryId);
			$total_tax = ($total_price * $taxRate) / 100;

			$OrderItemData = array(
				'order_master_id' => $order_master_id,
				'customer_id' => $customer_id,
				'seller_id' => $seller_id,
				'product_id' => $row['id'],
				'variation_size' => $row['unit'],
				'quantity' => comma_remove($row['qty']),
				'price' => comma_remove($row['price']),
				'total_price' => comma_remove($total_price),
				'tax' => comma_remove($total_tax),

				'shipping_fee' => $perSellerShippingFee[$seller_id] ?? 0,
    			'commission'   => $totals['commission'] ?? 0
			);
			
			Order_item::create($OrderItemData);
			
			$index++;
		}
		
		if($index>0){
			$intent = '';
			
			$sellerCount = 0;
			
			$OrderNoStr = implode(', ', $OrderNoArr);
			$total_qty = comma_remove($total_qty);
			$description = 'Total Quantity:'.$total_qty.', Order No:'. $OrderNoStr;

			$sellerCount = count($UniqueDataList);
		
			// if($shipping_fee ==''){
			// 	$shippingFee = 0; 
			// }else{
			// 	$shippingFee = $sellerCount * $shipping_fee; // this shipping fee will be in array
			// }
			
				// sum per-seller shipping fees
			$shippingFeeTotal = array_sum($perSellerShippingFee);
			// $t_amount = comma_remove($total_amount);
			$t_amount = array_sum(array_column($perSellerTotals, 'total_amount'));
			$commisiontable = DB::table('commissions')->limit(1)->first();
			$totalCommission = $commisiontable->commission;
			
			// $totalAmount = $t_amount + $shippingFee;
			
			$totalAmount = $t_amount + $shippingFeeTotal + $totalCommission;

			//Stripe
			if($payment_method_id == 3){
				if($gtext['stripe_isenable'] == 1){
					$stripe_secret = $gtext['stripe_secret'];
					
					// Enter Your Stripe Secret
					\Stripe\Stripe::setApiKey($stripe_secret);
							
					$amount = $totalAmount;
					$amount *= 100;
					$amount = (int) $amount;
					if($gtext['stripe_currency'] !=''){
						$currency = $gtext['stripe_currency'];
					}else{
						$currency = 'usd';
					}
					
					$payment_intent = \Stripe\PaymentIntent::create([
						'amount' => $amount,
						'currency' => $currency,
						'description' => $description,
						'payment_method_types' => ['card']
					]);
					$intent = $payment_intent->client_secret;
				}
				
			//Paypal
			}elseif($payment_method_id == 4){
				
				if($gtext['isenable_paypal'] == 1){
					
					$PayPalData = [
						'intent' => 'CAPTURE',
						"application_context" => [
							"return_url" => route('success.PayPalPayment'),
							"cancel_url" => route('cancel.PayPalPayment'),
						],
					   "purchase_units" => [
							0 => [
								"amount" => [
									"currency_code" => $gtext['paypal_currency'],
									"value" => "{$totalAmount}",
								],
								"description" => $description
							]
						]
					];

					$accessToken = $this->PayPalClient->generateAccessToken();
					$PayPalResponse = $this->PayPalClient->createOrder($accessToken, $PayPalData);
					
					if (isset($PayPalResponse['id']) && $PayPalResponse['id'] != null){
						foreach ($PayPalResponse['links'] as $links) {
							if ($links['rel'] == 'approve') {
								$redirect_url = $links['href'];
								break;
							}
						}
						
						if(isset($redirect_url)) {
							$intent = $redirect_url;
						}
					}else{
						
						Order_item::whereIn('order_master_id', $MasterData)->delete();
						Order_master::whereIn('id', $MasterData)->delete();
						
						$res['msgType'] = 'error';
						$res['msg'] = array('oneError' => array(__('Unknown error occurred')));
						return response()->json($res);
					}
				}
			
			//Razorpay
			}elseif($payment_method_id == 5){
				$intent = '';
				
				if($gtext['isenable_razorpay'] == 1){
					
					$razorpay_payment_id = $request->input('razorpay_payment_id');
					
					if($razorpay_payment_id == ''){
						$res['msgType'] = 'error';
						$res['msg'] = array('oneError' => array(__('Payment failed')));
						return response()->json($res);
					}
			
					$razorpay_key_id = $gtext['razorpay_key_id'];
					$razorpay_key_secret = $gtext['razorpay_key_secret'];
					
					$api = new Api($razorpay_key_id, $razorpay_key_secret);
					
					$payment = $api->payment->fetch($razorpay_payment_id);

					if(!empty($razorpay_payment_id)){
						
						try {
							$response = $api->payment->fetch($razorpay_payment_id)->capture(array('amount'=>$payment['amount'])); 
							
							$api->payment->fetch($razorpay_payment_id)->edit(array('notes'=> array('description'=> $description)));
							
						}catch (\Exception $e){
							
							Order_item::whereIn('order_master_id', $MasterData)->delete();
							Order_master::whereIn('id', $MasterData)->delete();
						
							$res['msgType'] = 'error';
							$res['msg'] = array('oneError' => array(__('Payment failed')));
							return response()->json($res);
						}            
					}
				}
			
			//Mollie
			}elseif($payment_method_id == 6){
	
				if($gtext['isenable_mollie'] == 1){

					$priceString = number_format($totalAmount, 2);
					$price = str_replace(",","", $priceString);
					$amount = (string) $price;
					// $amount = strval($price);

					$mollie_currency = $gtext['mollie_currency'];
						
					$mollie_api_key = $gtext['mollie_api_key'];
					Mollie::api()->setApiKey($mollie_api_key); // your mollie test api key

					$makePayment = [
						"amount" => [
							"currency" => $mollie_currency, //'EUR', // Type of currency you want to send
							"value" => $amount, //'30.00' You must send the correct number of decimals, thus we enforce the use of strings
						],
						"description" => $description, 
						"redirectUrl" => route('frontend.thank') // after the payment completion where you to redirect
					];
					
					$payment = Mollie::api()->payments->create($makePayment);
				
					$payment = Mollie::api()->payments->get($payment->id);
					
					$intent = $payment->getCheckoutUrl();
				}
				
			}else{
				$intent = '';
			}
			
			if($payment_method_id != 4){

				Session::forget('shopping_cart');
				
				if($gtext['ismail'] == 1){
					self::orderNotify($MasterData);
				}
			}
			
			
			$userId = Auth::id();
			
			$cartRecord = DB::table('carts')->where('user_id', $userId)->first();

			if ($cartRecord && $cartRecord->cart_data) {
				DB::table('carts')->where('user_id', $userId)->update(['cart_data' => null]);
			}

			$res['msgType'] = 'success';
			$res['msg'] = __('Your order is successfully.');
			$res['intent'] = $intent;
			return response()->json($res);
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Oops! Your order is failed. Please try again.');
			return response()->json($res);
		}
    }
		
    public function PayPalPaymentSuccess(Request $request){
		$gtext = gtext();
		
		$order_master_ids = Session::get('order_master_ids');

        Session::forget('order_master_ids');
		
		$accessToken = $this->PayPalClient->generateAccessToken();
		$OrderId = $request['token'];

        if (empty($request['PayerID']) || empty($request['token'])) {
			
			Order_item::whereIn('order_master_id', $order_master_ids)->delete();
			Order_master::whereIn('id', $order_master_ids)->delete();

            \Session::put('pt_payment_error', __('Payment failed'));
            return Redirect::route('frontend.checkout');
        }
		
		$response = $this->PayPalClient->capturePaymentOrder($accessToken, $OrderId);
		$resArr = json_decode($response->getBody(), true); 

        // Handle the response as needed
        if ($response->getStatusCode() === 201) {
			if (isset($resArr['status']) && $resArr['status'] == 'COMPLETED') {
				
				// $TransactionID = $resArr['purchase_units'][0]['payments']['captures'][0]['id'];
				
				Session::forget('shopping_cart');
				
				 if($gtext['ismail'] == 1){
					self::orderNotify($order_master_ids);
				}
				
				return Redirect::route('frontend.thank');
			}
        } else {
			Order_item::whereIn('order_master_id', $order_master_ids)->delete();
			Order_master::whereIn('id', $order_master_ids)->delete();
			
			\Session::put('pt_payment_error', __('Payment failed'));
			return Redirect::route('frontend.checkout');
        }
    }
	
    public function PayPalPaymentCancel(){
		
		$order_master_ids = Session::get('order_master_ids');

        Session::forget('order_master_ids');
		
		Order_item::whereIn('order_master_id', $order_master_ids)->delete();
		Order_master::whereIn('id', $order_master_ids)->delete();
		
		\Session::put('pt_payment_error', __('You have canceled the transaction'));
		return Redirect::route('frontend.checkout');
    }
	

    public function orderNotify($MasterData) {
        $gtext = gtext();
    
        // Fetch items for all affected order_masters (MasterData is array of a.id)
        $datalist = DB::table('order_masters as a')
            ->join('order_items as b', 'a.id', '=', 'b.order_master_id')
            ->join('users as c', 'a.seller_id', '=', 'c.id')
            ->join('payment_method as d', 'a.payment_method_id', '=', 'd.id')
            ->join('payment_status as e', 'a.payment_status_id', '=', 'e.id')
            ->leftJoin('order_status as os', 'a.order_status_id', '=', 'os.id')
            ->join('products as g', 'b.product_id', '=', 'g.id')
            ->leftJoin('delivery_types as dt', DB::raw("
				SUBSTRING_INDEX(
					TRIM(SUBSTRING(a.shipping_title, LOCATE(':', a.shipping_title) + 1)),
					',',
					1
				)
			"), '=', 'dt.lable')
            ->select(
                'a.id',
                'a.master_order_no',
                'a.customer_id',
                'a.seller_id',
                'a.payment_status_id',
                'a.order_status_id',
                'a.order_no',
                'a.created_at',
                'a.shipping_title',
                'a.shipping_fee',
                'g.title',
                'b.quantity',
                'b.price',
                'b.total_price',
                'b.tax',
                'b.discount',
                'b.variation_color',
                'b.variation_size',
                'a.email as customer_email',
                'a.name as customer_name',
                'a.phone as customer_phone',
                'a.country',
                'a.state',
                'a.zip_code',
                'a.city',
                'a.address as customer_address',
                'c.shop_name',
                'c.shop_url',
                'c.email as seller_email',
                'd.method_name',
                'e.pstatus_name',
                // per-item order status & shipping mode
                'os.ostatus_name as item_status',
                'dt.lable as shipping_mode',
                'a.master_order_no',
				'b.product_id'
            )
            ->whereIn('a.id', $MasterData)
            ->orderBy('a.seller_id', 'ASC')
            ->get();
    
        // init
        $index = 0;
        $mdata = array();
        $orderDataArr = array();
        $tempSellerId = '';
        $SellerCount = 0;
        $totalAmount = 0;
        $totalTax = 0;
        $totalDiscount = 0;
        $shippingTitles = [];
        $shippingFees = [];
    
        $item_list = '';
    
        foreach ($datalist as $row) {
            // fill master-level info from first row
            if ($index == 0) {
                $mdata['payment_status_id'] = $row->payment_status_id;
                $mdata['order_status_id'] = $row->order_status_id;
                $mdata['customer_name'] = $row->customer_name;
                $mdata['customer_email'] = $row->customer_email;
                $mdata['customer_address'] = $row->customer_address;
                $mdata['city'] = $row->city;
                $mdata['state'] = $row->state;
                $mdata['zip_code'] = $row->zip_code;
                $mdata['country'] = $row->country;
                $mdata['customer_phone'] = $row->customer_phone;
                $mdata['master_order_no'] = $row->master_order_no ?? null;
                $mdata['created_at'] = $row->created_at;
                $mdata['method_name'] = $row->method_name;
                $mdata['pstatus_name'] = $row->pstatus_name;
                $mdata['ostatus_name'] = $row->item_status ?? $row->ostatus_name ?? '';
                // don't set shipping arrays here
            }
    
            // collect shipping titles and fees (per row)
            $shippingTitles[] = $row->shipping_title;
            // shipping_fee might be stored as string or numeric, convert to float safely
            if (!isset($shippingFees[$row->seller_id])) {
                $shippingFees[$row->seller_id] = floatval($row->shipping_fee ?? 0);
            }
    
            // totals
            $totalAmount += floatval($row->total_price);
            $totalTax += floatval($row->tax);
            $totalDiscount += floatval($row->discount);
    
            // format prices exactly like your original code
            if ($gtext['currency_position'] == 'left') {
                $price = $gtext['currency_icon'] . NumberFormat($row->price);
                $total_price = $gtext['currency_icon'] . NumberFormat($row->total_price);
            } else {
                $price = NumberFormat($row->price) . $gtext['currency_icon'];
                $total_price = NumberFormat($row->total_price) . $gtext['currency_icon'];
            }
    
            // variation size text
            if ($row->variation_size == '0' || $row->variation_size === null) {
                $size = '';
            } else {
                $size = $row->quantity . ' ' . $row->variation_size;
            }
    
            // Build seller sections (your existing logic: a header row when seller changes)
            if ($tempSellerId != $row->seller_id) {
                $orderDataArr[$row->seller_id]['id'] = $row->id;
                $orderDataArr[$row->seller_id]['order_no'] = $row->master_order_no;
                $orderDataArr[$row->seller_id]['seller_id'] = $row->seller_id;
                $orderDataArr[$row->seller_id]['seller_email'] = $row->seller_email;
                $orderDataArr[$row->seller_id]['shop_name'] = $row->shop_name;
    
                // seller header: keep same format but change invoice link to master_order_no
                $item_list .= '<tr>
                    <td colspan="3" style="width:100%;text-align:left;border:1px solid #ddd;background-color:#f7f7f7;font-weight:bold;">'
                    . __('Sold By') . ': <a href="' . route('frontend.stores', [$row->seller_id, str_slug($row->shop_url)]) . '"> ' . $row->shop_name . '</a>, '
                    . __('Order#') . ': <a href="' . route('frontend.order-invoice', $row->master_order_no) . '"> ' . $row->master_order_no . '</a></td>
                    </tr>';
    
                $tempSellerId = $row->seller_id;
                $SellerCount++;
            }
    
			$shippingMode = 'N/A';
			$pairs = explode(',', $row->shipping_mode);
			foreach ($pairs as $pair) {
				$pair = trim($pair);
				if (strpos($pair, $row->product_id . ':') === 0) {
					$shippingMode = trim(substr($pair, strlen($row->product_id) + 1));
					break;
				}
			}
            // product row: insert SOLD BY, ITEM STATUS, SHIPPING MODE inside same product cell (no layout change)
            $item_list .= '<tr>
                <td style="width:70%;text-align:left;border:1px solid #ddd;">'
                    . $row->title . '<br>' . $size
                    . '<br><strong>' . __('Sold By') . ':</strong> ' . $row->shop_name
                    . '<br><strong>' . __('Order Status') . ':</strong> ' . ($row->item_status ?? '')
                    
                . '</td>
                <td style="width:15%;text-align:center;border:1px solid #ddd;">' . $price . ' x ' . $row->quantity . '</td>
                <td style="width:15%;text-align:right;border:1px solid #ddd;">' . $total_price . '</td>
            </tr>';
    
            $index++;
        } // end foreach
    
        // Build shipping title display (human readable)
        $shippingDetails = [];
        foreach ($shippingTitles as $i => $title) {
            $fee = isset($shippingFees[$i]) ? $shippingFees[$i] : 0;
            $shippingDetails[] = "{$title} (" . $gtext['currency_icon'] . NumberFormat($fee) . ")";
        }
        $shippingDisplay = implode(', ', $shippingDetails);
    
        // numeric sum of shipping fees (use for arithmetic)
        $totalShippingFee = array_sum($shippingFees);
    
        $commisiontable = DB::table('commissions')->limit(1)->first();
        $commissionset = $commisiontable->commission ?? 0;
    
        // TOTAL: use numeric totalShippingFee (not mdata['shipping_fee'] string)
        $total_amount_shipping_fee = $totalAmount + $commissionset + $totalShippingFee + $totalTax;
    
        // Format displayed values (currency position)
        if ($gtext['currency_position'] == 'left') {
            $shippingFee = $shippingDisplay; // human readable titles
            $shipping_fee = $gtext['currency_icon'] . NumberFormat($totalShippingFee);
            $tax = $gtext['currency_icon'] . NumberFormat($totalTax);
            $discount = $gtext['currency_icon'] . NumberFormat($totalDiscount);
            $subtotal = $gtext['currency_icon'] . NumberFormat($totalAmount);
            $total_amount = $gtext['currency_icon'] . NumberFormat($total_amount_shipping_fee);
            $commission = $gtext['currency_icon'] . NumberFormat($commissionset);
        } else {
            $shippingFee = $shippingDisplay;
            $shipping_fee = NumberFormat($totalShippingFee) . $gtext['currency_icon'];
            $tax = NumberFormat($totalTax) . $gtext['currency_icon'];
            $discount = NumberFormat($totalDiscount) . $gtext['currency_icon'];
            $subtotal = NumberFormat($totalAmount) . $gtext['currency_icon'];
            $total_amount = NumberFormat($total_amount_shipping_fee) . $gtext['currency_icon'];
            $commission = NumberFormat($commissionset) . $gtext['currency_icon'];
        }
    
        // status color badges: keep your logic
        if ($mdata['payment_status_id'] == 1) {
            $pstatus = '#26c56d';
        } elseif ($mdata['payment_status_id'] == 2) {
            $pstatus = '#fe9e42';
        } elseif ($mdata['payment_status_id'] == 3) {
            $pstatus = '#f25961';
        } else {
            $pstatus = '#f25961';
        }
    
        if ($mdata['order_status_id'] == 1) {
            $ostatus = '#fe9e42';
        } elseif ($mdata['order_status_id'] == 2) {
            $ostatus = '#fe9e42';
        } elseif ($mdata['order_status_id'] == 3) {
            $ostatus = '#fe9e42';
        } elseif ($mdata['order_status_id'] == 4) {
            $ostatus = '#26c56d';
        } else {
            $ostatus = '#f25961';
        }
    
        $base_url = url('/');
    
        // Build invoice download buttons (use master_order_no as requested)
        $InvoiceDownloads = '<a href="' . route('frontend.order-invoice', $mdata['master_order_no']) . '" 
    		style="background:' . $gtext['theme_color'] . ';display:block;text-align:center;padding:7px 15px;
    		margin:0 10px 10px 0;border-radius:3px;text-decoration:none;color:#fff;float:left;">
    		' . __('Invoice') . ' (' . $mdata['master_order_no'] . ')
    		</a>';
    
        // SEND MAIL (same format as before)
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
    
                // set recipients
                $mail->setFrom($gtext['from_mail'], $gtext['from_name']);
                $mail->addAddress($mdata['customer_email'], $mdata['customer_name']);
                foreach ($orderDataArr as $row) {
                    $mail->addAddress($row['seller_email'], $row['shop_name']);
                }
    
                $mail->isHTML(true);
                $mail->CharSet = "utf-8";
                $mail->Subject = $mdata['master_order_no'] . ' - ' . __('Your order is successfully.');
    
                // Build the same email body — replace only the $item_list and totals variables (I keep your HTML)
                $mail->Body = '<table style="background-color:#edf2f7;color:#111111;padding:40px 0px;line-height:24px;font-size:14px;" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <table style="background-color:#fff;max-width:1000px;margin:0 auto;padding:30px;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr><td style="font-size:40px;border-bottom:1px solid #ddd;padding-bottom:25px;font-weight:bold;text-align:center;">' . $gtext['company'] . '</td></tr>
                                <tr><td style="font-size:25px;font-weight:bold;padding:30px 0px 5px 0px;">' . __('Hi') . ' ' . $mdata['customer_name'] . '</td></tr>
                                <tr><td>' . __('We have received your order and will contact you as soon as your package is shipped. You can find your purchase information below.') . '</td></tr>
                                <tr>
                                    <td style="padding-top:30px;padding-bottom:20px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="vertical-align: top;">
                                                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                                                        <tr><td style="font-size:16px;font-weight:bold;">' . __('BILL TO') . ':</td></tr>
                                                        <tr><td><strong>' . $mdata['customer_name'] . '</strong></td></tr>
                                                        <tr><td>' . $mdata['customer_address'] . '</td></tr>
                                                        <tr><td>' . $mdata['city'] . ', ' . $mdata['state'] . ', ' . $mdata['zip_code'] . ', ' . $mdata['country'] . '</td></tr>
                                                        <tr><td>' . $mdata['customer_email'] . '</td></tr>
                                                        <tr><td>' . $mdata['customer_phone'] . '</td></tr>
                                                    </table>
                                                    <table style="padding:30px 0px;" border="0" cellpadding="3" cellspacing="0" width="100%">
                                                        <tr><td style="font-size:16px;font-weight:bold;">' . __('BILL FROM') . ':</td></tr>
                                                        <tr><td><strong>' . $gtext['company'] . '</strong></td></tr>
                                                        <tr><td>' . $gtext['invoice_address'] . '</td></tr>
                                                        <tr><td>' . $gtext['invoice_email'] . '</td></tr>
                                                        <tr><td>' . $gtext['invoice_phone'] . '</td></tr>
                                                    </table>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <table style="text-align:right;" border="0" cellpadding="3" cellspacing="0" width="100%">
                                                        <tr><td><strong>' . __('Order Date') . '</strong>: ' . date('d-m-Y', strtotime($mdata['created_at'])) . '</td></tr>
                                                        <tr><td><strong>' . __('Payment Method') . '</strong>: ' . $mdata['method_name'] . '</td></tr>
                                                        <tr><td><strong>' . __('Payment Status') . '</strong>: <span style="color:' . $pstatus . '">' . $mdata['pstatus_name'] . '</span></td></tr>
                                                       
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table style="border-collapse:collapse;" border="0" cellpadding="5" cellspacing="0" width="100%">
                                            <tr>
                                                <th style="width:70%;text-align:left;border:1px solid #ddd;">' . __('Product') . '</th>
                                                <th style="width:15%;text-align:center;border:1px solid #ddd;">' . __('Price') . '</th>
                                                <th style="width:15%;text-align:right;border:1px solid #ddd;">' . __('Total') . '</th>
                                            </tr>'
                                            . $item_list .
                                        '</table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top:5px;padding-bottom:20px;">
                                        <table style="font-weight:bold;" border="0" cellpadding="5" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="width:85%;text-align:right;">' . __('Shipping Fee') . ':</td>
                                                <td style="width:15%;text-align:right;">' . $shipping_fee . '</td>
                                            </tr>
                                            <tr>
                                                <td style="width:85%;text-align:right;">' . __('Tax') . ':</td>
                                                <td style="width:15%;text-align:right;">' . $tax . '</td>
                                            </tr>
                                            <tr>
                                                <td style="width:85%;text-align:right;">' . __('Subtotal') . ':</td>
                                                <td style="width:15%;text-align:right;">' . $subtotal . '</td>
                                            </tr>
                                            <tr>
                                                <td style="width:85%;text-align:right;">' . __('Commission') . ':</td>
                                                <td style="width:15%;text-align:right;">' . $commission . '</td>
                                            </tr>
                                            <tr>
                                                <td style="width:85%;text-align:right;">' . __('Total') . ':</td>
                                                <td style="width:15%;text-align:right;">' . $total_amount . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding-top:30px;padding-bottom:50px;">' . $InvoiceDownloads . '</td></tr>
                                <tr><td style="padding-top:10px;border-top:1px solid #ddd;text-align:center;">' . __('Thank you for purchasing our products.') . '</td></tr>
                                <tr><td style="padding-top:5px;text-align:center;">' . __('If you have any questions about this invoice, please contact us') . '</td></tr>
                                <tr><td style="padding-top:5px;text-align:center;"><a href="' . $base_url . '">' . $base_url . '</a></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>';
    
                $mail->send();
    
                return 1;
            } catch (Exception $e) {
                // logging is recommended:
                // \Log::error('OrderNotify mail error: '.$e->getMessage());
                return 0;
            }
        } // end ismail
    } // end function
}
