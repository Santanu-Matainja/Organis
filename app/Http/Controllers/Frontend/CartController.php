<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
	//Add to Cart
	// public function AddToCart($id, $qty){

	// 	$res = array();
	// 	$datalist = Product::where('id', $id)->first();
	// 	$user = User::where('id', $datalist['user_id'])->first();

	// 	$quantity = $qty == 0 ? 1 : $qty;
	// 	$cart = session()->get('shopping_cart', []);
		
	// 	if(isset($cart[$id])){
	// 		$cart[$id]['qty'] = $cart[$id]['qty'] + $quantity;
	// 	}else{
	// 		$cart[$id] = [
	// 			"id" => $datalist['id'],
	// 			"name" => $datalist['title'],
	// 			"qty" => $quantity,
	// 			"price" => $datalist['sale_price'],
	// 			"weight" => 0,
	// 			"thumbnail" => $datalist['f_thumbnail'],
	// 			"unit" => $datalist['variation_size'],
	// 			"seller_id" => $datalist['user_id'],
	// 			"exdate" => $datalist['exdate'],
	// 			"perisible" => $datalist['perisible'],
	// 			"delivarytypeid" => $datalist['delivarytypeid'],
	// 			"seller_name" => $user['name'],
	// 			"store_name" => $user['shop_name'],
	// 			"store_logo" => $user['photo'],
	// 			"store_url" => $user['shop_url'],
	// 			"seller_email" => $user['email'],
	// 			"seller_phone" => $user['phone'],
	// 			"seller_address" => $user['address']
	// 		];
	// 	}

	// 	session()->put('shopping_cart', $cart);

	// 	$res['msgType'] = 'success';
	// 	$res['msg'] = __('New Data Added Successfully');
		
	// 	return response()->json($res);
	// }
	public function AddToCart($id, $qty)
	{
		$res = [];
		$userId = Auth::id();

		if (!$userId) {
			$res['msgType'] = 'error';
			$res['msg'] = __('Please login to add products to your cart.');
			return response()->json($res);
		}

		$datalist = Product::where('id', $id)->first();
		$user = User::where('id', $datalist['user_id'])->first();

		$quantity = $qty == 0 ? 1 : $qty;

		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();
		$cart = $cartRecord && $cartRecord->cart_data
			? json_decode($cartRecord->cart_data, true)
			: [];

		// $currentType = $datalist['perisible']; 

		// if (!empty($cart)) {
		// 	$hasPerishable = false;
		// 	$hasNonPerishable = false;

		// 	foreach ($cart as $item) {
		// 		if ($item['perisible'] == 1) {
		// 			$hasPerishable = true;
		// 		} else {
		// 			$hasNonPerishable = true;
		// 		}
		// 	}

		// 	// If cart already contains perishable & user tries to add non-perishable
		// 	if ($hasPerishable && $currentType == 0) {
		// 		return response()->json([
		// 			'msgType' => 'error',
		// 			'msg' => __('Cart Contains Perisible Products Cannot Add Non-Perisible Product.')
		// 		]);
		// 	}

		// 	// If cart already contains non-perishable & user tries to add perishable
		// 	if ($hasNonPerishable && $currentType == 1) {
		// 		return response()->json([
		// 			'msgType' => 'error',
		// 			'msg' => __('Cart Contains Non-Perisible Products Cannot Add Perisible Product.')
		// 		]);
		// 	}
		// }
		
		// ✅ Add or update product in cart
		if (isset($cart[$id])) {
			$cart[$id]['qty'] = $cart[$id]['qty'] + $quantity;
		} else {
			$cart[$id] = [
				"id" => $datalist['id'],
				"name" => $datalist['title'],
				"qty" => $quantity,
				"price" => $datalist['sale_price'],
				"weight" => 0,
				"thumbnail" => $datalist['f_thumbnail'],
				"unit" => $datalist['variation_size'],
				"seller_id" => $datalist['user_id'],
				"exdate" => $datalist['exdate'],
				"perisible" => $datalist['perisible'],
				"delivarytypeid" => $datalist['delivarytypeid'],
				"seller_name" => $user['name'],
				"store_name" => $user['shop_name'],
				"store_logo" => $user['photo'],
				"store_url" => $user['shop_url'],
				"seller_email" => $user['email'],
				"seller_phone" => $user['phone'],
				"seller_address" => $user['address']
			];
		}

		// ✅ Update or insert cart record
		DB::table('carts')->updateOrInsert(
			['user_id' => $userId],
			[
				'cart_data' => json_encode($cart),
				'updated_at' => now(),
				'created_at' => $cartRecord ? $cartRecord->created_at : now(),
			]
		);

		$res['msgType'] = 'success';
		$res['msg'] = __('New Data Added Successfully');

		return response()->json($res);
	}
	
	//Add to Cart
	public function ViewCart(){
		$gtext = gtext();
		$gtax = getTax();
		$taxRate = $gtax['percentage'];
		$Path = asset_path('media');

		$userId = Auth::id();
		// $ShoppingCartData = session()->get('shopping_cart');
		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();
		$ShoppingCartData = $cartRecord && $cartRecord->cart_data
			? json_decode($cartRecord->cart_data, true)
			: [];

		$count = 0;
		$Total_Price = 0;
		$Sub_Total = 0;
		$tax = 0;
		$total = 0;
		$items = '';
		// if(session()->get('shopping_cart')){
		// 	foreach ($ShoppingCartData as $row) {
		if (!empty($ShoppingCartData)) {
        	foreach ($ShoppingCartData as $row) {
				$count += $row['qty'];
				$Total_Price += $row['price']*$row['qty'];
				$Sub_Total += $row['price']*$row['qty'];
				
				if($gtext['currency_position'] == 'left'){
					$price = '<span id="product-quatity">'.$row['qty'].'</span> x '.$gtext['currency_icon'].$row['price']; 
				}else{
					$price = '<span id="product-quatity">'.$row['qty'].'</span> x '.$row['price'].$gtext['currency_icon']; 
				}
			
				$items .= '<li>
							<div class="cart-item-card">
								<a data-id="'.$row['id'].'" id="removetocart_'.$row['id'].'" onclick="onRemoveToCart('.$row['id'].')" href="javascript:void(0);" class="item-remove"><i class="bi bi-x"></i></a>
								<div class="cart-item-img">
									<img src="'.$Path.'/'.($row['thumbnail'] ? $row['thumbnail'] : 'no-image.png').'" alt="'.$row['name'].'" />
								</div>
								<div class="cart-item-desc">
									<h6><a href="'.route('frontend.product', [$row['id'], str_slug($row['name'])]).'">'.$row['name'].'</a></h6>
									<p>'.$price.'</p>
								</div>
							</div>
						</li>';
			}
		}
		
		$TotalPrice = NumberFormat($Total_Price);
		$SubTotal = NumberFormat($Sub_Total);
		
		$TaxCal = ($Total_Price*$taxRate)/100;
		$tax = NumberFormat($TaxCal);
		
		$total = $Sub_Total+$TaxCal;
		$GrandTotal = NumberFormat($total);
		$discount = 0;
		
		$datalist = array();
		$datalist['items'] = $items;
		$datalist['total_qty'] = $count;
		if($gtext['currency_position'] == 'left'){
			$datalist['sub_total'] = $gtext['currency_icon'].$SubTotal;
			$datalist['tax'] = $gtext['currency_icon'].$tax;
			$datalist['price_total'] = $gtext['currency_icon'].$TotalPrice;
			$datalist['total'] = $gtext['currency_icon'].$GrandTotal;
		}else{
			$datalist['sub_total'] = $SubTotal.$gtext['currency_icon'];
			$datalist['tax'] = $tax.$gtext['currency_icon'];
			$datalist['price_total'] = $TotalPrice.$gtext['currency_icon'];
			$datalist['total'] = $GrandTotal.$gtext['currency_icon'];
		}

		return response()->json($datalist);
	}
	
	//Remove to Cart
	// public function RemoveToCart($rowid){
	// 	$res = array();

	// 	$cart = session()->get('shopping_cart');
	// 	if(isset($cart[$rowid])){
	// 		unset($cart[$rowid]);
	// 		session()->put('shopping_cart', $cart);
	// 	}

	// 	$res['msgType'] = 'success';
	// 	$res['msg'] = __('Data Removed Successfully');
		
	// 	return response()->json($res);
	// }
	public function RemoveToCart($rowid)
	{
		$res = [];

		$userId = Auth::id();

		// Fetch current cart from DB
		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();

		if ($cartRecord && $cartRecord->cart_data) {
			$cart = json_decode($cartRecord->cart_data, true);

			// Remove the product if exists
			if (isset($cart[$rowid])) {
				unset($cart[$rowid]);
			}

			// Update or clear the cart_data
			if (empty($cart)) {
				DB::table('carts')->where('user_id', $userId)->update(['cart_data' => null]);
			} else {
				DB::table('carts')->where('user_id', $userId)->update(['cart_data' => json_encode($cart)]);
			}
		}

		$res['msgType'] = 'success';
		$res['msg'] = __('Data Removed Successfully');

		return response()->json($res);
	}
	
    //get Cart
    public function getCart(){

		$userId = Auth::id();
		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();
		$ShoppingCartData = $cartRecord && $cartRecord->cart_data
			? json_decode($cartRecord->cart_data, true)
			: [];

        return view('frontend.cart', compact('ShoppingCartData'));
    }
	
    //get Cart
    public function getViewCartData()
	{
		$gtext = gtext();
		$gtax = getTax();
		$taxRate = (float) $gtax['percentage'];

		$userId = Auth::id();
		$cartRecord = DB::table('carts')->where('user_id', $userId)->first();
		$ShoppingCartData = $cartRecord && $cartRecord->cart_data
			? json_decode($cartRecord->cart_data, true)
			: [];

		$count = 0;
		$Total_Price = 0.0;
		$Sub_Total = 0.0;

		if (!empty($ShoppingCartData)) {
			foreach ($ShoppingCartData as $row) {
				$price = (float) str_replace([',', '€', ' '], '', $row['price']); // ensure numeric
				$qty = (float) $row['qty'];
				$count += $qty;
				$Total_Price += $price * $qty;
				$Sub_Total += $price * $qty;
			}
		}

		// Calculate numbers (before formatting)
		$TaxCal = ($Total_Price * $taxRate) / 100;
		$GrandTotal = $Sub_Total + $TaxCal;
		$discount = 0.0;

		// Helper for German formatting
		$formatCurrency = function ($value) use ($gtext) {
			$formatted = number_format($value, 2, ',', '.'); // German format
			return $gtext['currency_position'] == 'left'
				? $gtext['currency_icon'] . $formatted
				: $formatted . $gtext['currency_icon'];
		};

		// Prepare output
		$datalist = [];
		$datalist['total_qty'] = $count;
		$datalist['sub_total'] = $formatCurrency($Sub_Total);
		$datalist['tax'] = $formatCurrency($TaxCal);
		$datalist['price_total'] = $formatCurrency($Total_Price);
		$datalist['total'] = $formatCurrency($GrandTotal);
		$datalist['discount'] = $formatCurrency($discount);

		return response()->json($datalist);
	}

	
	//Add to Wishlist
	public function addToWishlist($id){

		$res = array();
		$userId = Auth::id();

		if (!$userId) {
			$res['msgType'] = 'error';
			$res['msg'] = __('Please login to add products to your Wishlist.');
			return response()->json($res);
		}

		$datalist = Product::where('id', $id)->first();
		$user = User::where('id', $datalist['user_id'])->first();
		
		$quantity = 1;
		// $cart = session()->get('shopping_wishlist', []);
		$cartRecord = DB::table('wishlists')->where('user_id', $userId)->first();
		$cart = $cartRecord && $cartRecord->wishlist_data
			? json_decode($cartRecord->wishlist_data, true)
			: [];
		
		if(isset($cart[$id])){
			$cart[$id]['qty'] = $quantity;
		}else{
			$cart[$id] = [
				"id" => $datalist['id'],
				"name" => $datalist['title'],
				"qty" => $quantity,
				"price" => $datalist['sale_price'],
				"weight" => 0,
				"thumbnail" => $datalist['f_thumbnail'],
				"seller_id" => $datalist['user_id'],
				"exdate" => $datalist['exdate'],
				"perisible" => $datalist['perisible'],
				"delivarytypeid" => $datalist['delivarytypeid'],
				"seller_name" => $user['name'],
				"store_name" => $user['shop_name'],
				"store_logo" => $user['photo'],
				"store_url" => $user['shop_url'],
				"seller_email" => $user['email'],
				"seller_phone" => $user['phone'],
				"seller_address" => $user['address']
			];
		}

		// session()->put('shopping_wishlist', $cart);
		// ✅ Update or insert cart record
		DB::table('wishlists')->updateOrInsert(
			['user_id' => $userId],
			[
				'wishlist_data' => json_encode($cart),
				'updated_at' => now(),
				'created_at' => $cartRecord ? $cartRecord->created_at : now(),
			]
		);

		$res['msgType'] = 'success';
		$res['msg'] = __('New Data Added Successfully');
		
		return response()->json($res);
	}
	
    //get Wishlist
    public function getWishlist(){

		$userId = Auth::id();
		$cartRecord = DB::table('wishlists')->where('user_id', $userId)->first();
		$ShoppingCartData = $cartRecord && $cartRecord->wishlist_data
			? json_decode($cartRecord->wishlist_data, true)
			: [];

		return view('frontend.wishlist', compact('ShoppingCartData'));
	}
	
	//Remove to Wishlist
	public function RemoveToWishlist($rowid){
		$res = array();
		
		// $cart = session()->get('shopping_wishlist');
		// if(isset($cart[$rowid])){
		// 	unset($cart[$rowid]);
		// 	session()->put('shopping_wishlist', $cart);
		// }

		$userId = Auth::id();

		// Fetch current cart from DB
		$cartRecord = DB::table('wishlists')->where('user_id', $userId)->first();

		if ($cartRecord && $cartRecord->wishlist_data) {
			$cart = json_decode($cartRecord->wishlist_data, true);

			// Remove the product if exists
			if (isset($cart[$rowid])) {
				unset($cart[$rowid]);
			}

			// Update or clear the cart_data
			if (empty($cart)) {
				DB::table('wishlists')->where('user_id', $userId)->update(['wishlist_data' => null]);
			} else {
				DB::table('wishlists')->where('user_id', $userId)->update(['wishlist_data' => json_encode($cart)]);
			}
		}

		$res['msgType'] = 'success';
		$res['msg'] = __('Data Removed Successfully');
		
		return response()->json($res);
	}
	
	//Count to Wishlist
	public function countWishlist(){

		// $ShoppingWishlistData = session()->get('shopping_wishlist');
		// $count = 0;
		// if(session()->get('shopping_wishlist')){
		// 	foreach ($ShoppingWishlistData as $row) {
		// 		$count++;
		// 	}
		// }

		$userId = Auth::id();
		$ShoppingWishlistData = [];

		$cartRecord = DB::table('wishlists')->where('user_id', $userId)->first();
		if ($cartRecord && $cartRecord->wishlist_data) {
			$ShoppingWishlistData = json_decode($cartRecord->wishlist_data, true);
		}

		$count = 0;
		if (!empty($ShoppingWishlistData)) {
			foreach ($ShoppingWishlistData as $row) {
				$count++;
			}
		}
		
		return response()->json($count);
	}
}
