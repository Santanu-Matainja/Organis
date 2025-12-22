<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Pro_category;
use App\Models\Brand;
use App\Models\Tax;
use App\Models\Attribute;
use App\Models\Pro_image;
use App\Models\Related_product;
use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryType;
use App\Models\Tp_status;
use App\Models\ProductShipping;

class ProductsController extends Controller
{
	
	//Products page load
    public function getProductsPageLoad() {
		
		$user_id = Auth::user()->id;

		$languageslist = DB::table('languages')->where('status', 1)->orderBy('language_name', 'asc')->get();
		$brandlist = Brand::where('is_publish', 1)->orderBy('name','asc')->get();
		$delivarytypes = DeliveryType::orderBy('lable','asc')->get();
		$categorylist = Pro_category::where('is_publish', 1)
			->where(function ($q) {
				$q->whereNull('parent_id')
				->orWhere('parent_id', 0);
			})
			->orderBy('name', 'asc')
			->get();
			
		$publishstatus = Tp_status::get();

		$datalist = DB::table('products')
			->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
			->join('languages', 'products.lan', '=', 'languages.language_code')
			->join('pro_categories', 'products.cat_id', '=', 'pro_categories.id')
			->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
			->select('products.*', 'pro_categories.name as category_name', 'brands.name as brand_name', 'tp_status.status', 'languages.language_name')
			->where('products.user_id', $user_id)
			->orderBy('products.id','desc')
			->paginate(20);

        return view('seller.products', compact('languageslist', 'categorylist', 'brandlist', 'datalist', 'delivarytypes', 'publishstatus'));		
	}
	
	//Get data for Products Pagination
	public function getProductsTableData(Request $request){
		$user_id = Auth::user()->id;
		
		$search = $request->search;
		
		$language_code = $request->language_code;
		$category_id = $request->category_id;
		$brand_id = $request->brand_id;
		$is_publish = $request->is_publish;
		$stock_status_id = $request->stock_status_id;

		if($request->ajax()){

			if($search != ''){
				$datalist = DB::table('products')
					->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
					->join('languages', 'products.lan', '=', 'languages.language_code')
					->join('pro_categories', 'products.cat_id', '=', 'pro_categories.id')
					->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
					->select('products.*', 'pro_categories.name as category_name', 'brands.name as brand_name', 'tp_status.status', 'languages.language_name')
					->where(function ($query) use ($search){
						$query->where('products.title', 'like', '%'.$search.'%')
							->orWhere('pro_categories.name', 'like', '%'.$search.'%')
							->orWhere('brands.name', 'like', '%'.$search.'%')
							->orWhere('languages.language_name', 'like', '%'.$search.'%')
							->orWhere('sale_price', 'like', '%'.$search.'%');
					})
					->where(function ($query) use ($language_code){
						$query->whereRaw("products.lan = '".$language_code."' OR '".$language_code."' = '0'");
					})
					->where(function ($query) use ($category_id){
						$query->whereRaw("products.cat_id = '".$category_id."' OR '".$category_id."' = '0'");
					})
					->where(function ($query) use ($brand_id){
						$query->whereRaw("products.brand_id = '".$brand_id."' OR '".$brand_id."' = 'all'");
					})
					->where(function ($query) use ($is_publish){
						$query->whereRaw("products.is_publish = '".$is_publish."' OR '".$is_publish."' = 'all'");
					})
					->where(function ($query) use ($stock_status_id) {
						if ($stock_status_id === 'all') {
						} elseif ($stock_status_id == 0 || is_null($stock_status_id)) {
							$query->where(function ($q) {
								$q->where('products.stock_status_id', 0)
								->orWhereNull('products.stock_status_id');
							});
						} else {
							$query->where('products.stock_status_id', $stock_status_id);
						}
					})
					->where(function ($query) use ($user_id){
						$query->whereRaw("products.user_id = '".$user_id."'");
					})
					->orderBy('products.id','desc')
					->paginate(20);
			}else{

				$datalist = DB::table('products')
					->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
					->join('languages', 'products.lan', '=', 'languages.language_code')
					->join('pro_categories', 'products.cat_id', '=', 'pro_categories.id')
					->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
					->select('products.*', 'pro_categories.name as category_name', 'brands.name as brand_name', 'tp_status.status', 'languages.language_name')
					->where(function ($query) use ($language_code){
						$query->whereRaw("products.lan = '".$language_code."' OR '".$language_code."' = '0'");
					})
					->where(function ($query) use ($category_id){
						$query->whereRaw("products.cat_id = '".$category_id."' OR '".$category_id."' = '0'");
					})
					->where(function ($query) use ($brand_id){
						$query->whereRaw("products.brand_id = '".$brand_id."' OR '".$brand_id."' = 'all'");
					})
					->where(function ($query) use ($is_publish){
						$query->whereRaw("products.is_publish = '".$is_publish."' OR '".$is_publish."' = 'all'");
					})
					->where(function ($query) use ($stock_status_id) {
						if ($stock_status_id === 'all') {
						} elseif ($stock_status_id == 0 || is_null($stock_status_id)) {
							$query->where(function ($q) {
								$q->where('products.stock_status_id', 0)
								->orWhereNull('products.stock_status_id');
							});
						} else {
							$query->where('products.stock_status_id', $stock_status_id);
						}
					})

					->where(function ($query) use ($user_id){
						$query->whereRaw("products.user_id = '".$user_id."'");
					})
					->orderBy('products.id','desc')
					->paginate(20);
			}

			return view('seller.partials.products_table', compact('datalist'))->render();
		}
	}
	
	public function getSubCategories($id)
	{
		$subCategories = Pro_category::where('parent_id', $id)
			->where('is_publish', 1)
			->orderBy('name', 'asc')
			->get(['id', 'name']);

		return response()->json($subCategories);
	}

	//Save data for Products
    public function saveProductsData(Request $request){
		$res = array();

		$id = $request->input('RecordId');
		$title = esc($request->input('title'));
		$slug = esc(str_slug($request->input('slug')));
		$lan = $request->input('lan');
		$cat_id = $request->filled('categoryid') ? $request->input('categoryid') : $request->input('parent_category');
		$brand_id = $request->input('brandid');
		$user_id = $request->input('user_id');
		$exdate = $request->input('exdate');
		$manufacture_date = $request->input('manufacture_date');
		// $perisible = $request->has('perisible') ? 1 : 0;
		// $delivarytypeid = $request->input('delivarytypeid');

		$validator_array = array(
			'product_name' => $request->input('title'),
			'slug' => $slug,
			'language' => $request->input('lan'),
			'category' => $request->input('categoryid'),
			'brand' => $request->input('brandid'),
			// 'delivarytypeid' => $request->input('delivarytypeid')
		);
		
		$rId = $id == '' ? '' : ','.$id;
		$validator = Validator::make($validator_array, [
			'product_name' => 'required',
			'slug' => 'required|max:191|unique:products,slug' . $rId,
			'language' => 'required',
			'category' => 'required',
			'brand' => 'required',
			// 'delivarytypeid' => 'required',
		]);

		$errors = $validator->errors();

		if($errors->has('product_name')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('product_name');
			return response()->json($res);
		}
		
		if($errors->has('slug')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('slug');
			return response()->json($res);
		}

		if($errors->has('language')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('language');
			return response()->json($res);
		}
		
		if($errors->has('category')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('category');
			return response()->json($res);
		}
		
		if($errors->has('brand')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('brand');
			return response()->json($res);
		}
		
		$SellerSettings = gSellerSettings();
		if($SellerSettings['product_auto_publish'] == 1){
			$is_publish = 1;
		}else{
			$is_publish = 2;
		}

		// if($errors->has('delivarytypeid')){
		// 	$res['msgType'] = 'error';
		// 	$res['msg'] = $errors->first('delivarytypeid');
		// 	return response()->json($res);
		// }
		
		$data = array(
			'title' => $title,
			'slug' => $slug,
			'cat_id' => $cat_id,
			'category_ids' => $cat_id,
			'brand_id' => $brand_id,
			'lan' => $lan,
			'user_id' => $user_id,
			'is_publish' => $is_publish,
			'exdate' => $exdate,
			'manufacture_date' => $manufacture_date,
			// 'perisible' => $perisible,
			// 'delivarytypeid' => $delivarytypeid,
		);

		if($id ==''){
			$response = Product::create($data)->id;
			if($response){
				$res['id'] = $response;
				$res['msgType'] = 'success';
				$res['msg'] = __('New Data Added Successfully');
			}else{
				$res['id'] = '';
				$res['msgType'] = 'error';
				$res['msg'] = __('Data insert failed');
			}
		}else{
			$response = Product::where('id', $id)->update($data);
			if($response){
				$res['id'] = $id;
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['id'] = '';
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
		}
		
		return response()->json($res);
    }

	// Bulk Save 

	public function bulksaveProductsData(Request $request)
	{
		$res = array();
		$lan = 'en';
		$cat_id = $request->categoryid;
		$user_id = Auth::id() ?? $request->user_id;
		$excelData = $request->excelData ?? [];
		$brand_id = $request->brand_id2;

		if (empty($excelData)) {
			$res['msgType'] = 'error';
			$res['msg'] = __('No data found in Excel file');
			return response()->json($res);
		}

		// Validate top-level
		$validator_array = [
			'language' => $lan,
			'category' => $cat_id,
		];

		$validator = Validator::make($validator_array, [
			'language' => 'required',
			'category' => 'required',
		]);

		$errors = $validator->errors();
		if ($errors->has('category')) {
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('category');
			return response()->json($res);
		}

		// Seller publish settings
		$SellerSettings = gSellerSettings();
		$is_publish = ($SellerSettings['product_auto_publish'] == 1) ? 1 : 2;

		DB::beginTransaction();
		try {
			$insertCount = 0;
			$updateCount = 0;

		$existingSlugs = DB::table('products')->pluck('slug')->toArray();
		$existingSkus  = DB::table('products')->pluck('sku')->toArray();

			foreach ($excelData as $data) {
				if (empty($data['sku'])) continue;

				$sku = trim($data['sku']);
				$title = esc($data['title'] ?? '');
				$slug = esc(str_slug($title));

				// Slug Uniq
				$originalSlug = $slug;
				$i = 2;
				while (in_array($slug, $existingSlugs)) {
					$slug = $originalSlug . '-' . $i;
					$i++;
				}
				$existingSlugs[] = $slug;

				// SKU Uniq
				$originalSku = $sku;
				$j = 2;
				while (in_array($sku, $existingSkus)) {
					$sku = $originalSku . '-' . $j;
					$j++;
				}
				$existingSkus[] = $sku;

				if($data['stock_qty'])
				{
					$stock_status_id = 1;
					$is_stock = 1;
				}
				else{
					$stock_status_id = 0;
					$is_stock = 0;
				}	

				$productData = [
					'title' => $title,
					'slug' => $slug,
					'short_desc' => $data['short_desc'] ?? '',
					'description' => $data['description'] ?? '',
					'extra_desc' => $data['extra_desc'] ?? '',
					'cost_price' => $data['cost_price'] ?? 0,
					'sale_price' => $data['sale_price'] ?? 0,
					'old_price' => $data['old_price'] ?? 0,
					'stock_qty' => $data['stock_qty'] ?? 0,
					'f_thumbnail' => $data['image'] ?? '',
					'category_ids' => $cat_id,
					'cat_id' => $cat_id,
					'tax_id' => 38,
					'user_id' => $user_id,
					'lan' => $lan,
					'is_publish' => $is_publish,
					'stock_status_id' => $stock_status_id,
					'is_stock' => $is_stock,
					'brand_id' => $brand_id,
				];
			
				$existing = Product::where('sku', $sku)->first();
				if ($existing) {
					$existing->update($productData);
					$updateCount++;
				} else {
					$productData['sku'] = $sku;
					Product::create($productData);
					$insertCount++;
				}
			}

			DB::commit();

			if ($insertCount > 0 || $updateCount > 0) {
				$res['msgType'] = 'success';
				$res['msg'] = __(
					':insert new and :update updated products processed successfully.',
					['insert' => $insertCount, 'update' => $updateCount]
				);
			} else {
				$res['msgType'] = 'error';
				$res['msg'] = __('No valid rows found or SKU missing.');
			}
		} catch (\Exception $e) {
			DB::rollBack();
			$res['msgType'] = 'error';
			$res['msg'] = __('Error: ') . $e->getMessage();
		}

		return response()->json($res);
	}


	
	//Delete data for Products
	public function deleteProducts(Request $request){
		
		$res = array();

		$id = $request->id;

		if($id != ''){
			Pro_image::where('product_id', $id)->delete();
			Related_product::where('product_id', $id)->delete();
			$response = Product::where('id', $id)->delete();
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
	
	//Bulk Action for Products
	public function bulkActionProducts(Request $request){
		
		$res = array();

		$idsStr = $request->ids;
		$idsArray = explode(',', $idsStr);
		
		$BulkAction = $request->BulkAction;

		if($BulkAction == 'publish'){
			$response = Product::whereIn('id', $idsArray)->update(['is_publish' => 1]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'draft'){
			
			$response = Product::whereIn('id', $idsArray)->update(['is_publish' => 2]);
			if($response){
				$res['msgType'] = 'success';
				$res['msg'] = __('Data Updated Successfully');
			}else{
				$res['msgType'] = 'error';
				$res['msg'] = __('Data update failed');
			}
			
		}elseif($BulkAction == 'delete'){
			
			Pro_image::whereIn('product_id', $idsArray)->delete();
			Related_product::whereIn('product_id', $idsArray)->delete();
			
			$response = Product::whereIn('id', $idsArray)->delete();
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
	
	//has Product Slug
    public function hasProductSlug(Request $request){
		$res = array();

		$slug = str_slug($request->slug);
        $count = Product::where('slug', $slug) ->count();
		if($count == 0){
			$res['slug'] = $slug;
		}else{
			$incr = $count+1;
			$res['slug'] = $slug.'-'.$incr;
		}
		
		return response()->json($res);
	}
	
    //get Product
    public function getProductPageData($id){

		$datalist = Product::where('id', $id)->first();
		
		$lan = $datalist->lan;
		
		$statuslist = DB::table('tp_status')->orderBy('id', 'asc')->get();
		$languageslist = DB::table('languages')->where('status', 1)->orderBy('id', 'asc')->get();
		
		$brandlist = Brand::where('lan', '=', $lan)->where('is_publish', '=', 1)->orderBy('name','asc')->get();
		$categorylist = Pro_category::where('is_publish', 1)
			->where(function ($q) {
				$q->whereNull('parent_id')
				->orWhere('parent_id', 0);
			})
			->orderBy('name', 'asc')
			->get();

		$selectedCategory = Pro_category::where('id', $datalist->cat_id)->first();

		$parentCategoryId = null;
		$selectedSubCategoryId = null;

		if ($selectedCategory && $selectedCategory->parent_id) {
			$parentCategoryId = $selectedCategory->parent_id;
			$selectedSubCategoryId = $selectedCategory->id;
		}	
		
		$unitlist = Attribute::orderBy('name','asc')->get();
		$taxlist = Tax::orderBy('title','asc')->get();

        return view('seller.product', compact('datalist', 'statuslist', 'languageslist', 'brandlist', 'categorylist', 'unitlist', 'taxlist', 'parentCategoryId', 'selectedSubCategoryId'));
    }
	
	public function getSubCategoryList(Request $request)
	{
		return Pro_category::where('parent_id', $request->parent_id)
			->where('is_publish', 1)
			->orderBy('name', 'asc')
			->get(['id', 'name']);
	}

	//Update data for Products
    public function updateProductsData(Request $request){
		$res = array();

		$id = $request->input('RecordId');
		$title = esc($request->input('title'));
		$slug = esc(str_slug($request->input('slug')));
		$short_desc = $request->input('short_desc');
		$description = $request->input('description');
		$brand_id = $request->input('brand_id');
		$tax_id = $request->input('tax_id');
		$collection_id = $request->input('collection_id');
		$is_featured = $request->input('is_featured');
		$lan = $request->input('lan');
		$f_thumbnail = $request->input('f_thumbnail');
		$category_ids = $request->filled('categoryid') ? $request->input('categoryid') : $request->input('cat_id');
		$cat_id = $request->filled('categoryid') ? $request->input('categoryid') : $request->input('cat_id');
		$variation_size = $request->input('variation_size');
		$sale_price = $request->input('sale_price');

		$exdate = $request->input('exdate');
		$manufacture_date = $request->input('manufacture_date');
		$maxorderqty = $request->input('maxorderqty');
		
		$validator_array = array(
			'product_name' => $request->input('title'),
			'slug' => $slug,
			'featured_image' => $request->input('f_thumbnail'),
			'category' => $request->input('cat_id'),
			'language' => $request->input('lan'),
			'variation_size' => $request->input('variation_size'),
			'sale_price' => $request->input('sale_price'),
			'maxorderqty' => $request->input('maxorderqty'),
		);
		
		$rId = $id == '' ? '' : ','.$id;
		$validator = Validator::make($validator_array, [
			'product_name' => 'required',
			'slug' => 'required|max:191|unique:products,slug' . $rId,
			'featured_image' => 'required',
			'language' => 'required',
			'category' => 'required',
			'variation_size' => 'required',
			'sale_price' => 'required',
			'maxorderqty' => 'required',
		]);

		$errors = $validator->errors();

		if($errors->has('product_name')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('product_name');
			return response()->json($res);
		}
		
		if($errors->has('slug')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('slug');
			return response()->json($res);
		}

		if($errors->has('language')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('language');
			return response()->json($res);
		}
		
		if($errors->has('category')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('category');
			return response()->json($res);
		}
		
		if($errors->has('featured_image')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('featured_image');
			return response()->json($res);
		}
		
		if($errors->has('variation_size')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('variation_size');
			return response()->json($res);
		}
		
		if($errors->has('sale_price')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('sale_price');
			return response()->json($res);
		}
		
		if($errors->has('maxorderqty')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('maxorderqty');
			return response()->json($res);
		}

		$data = array(
			'title' => $title,
			'slug' => $slug,
			'f_thumbnail' => $f_thumbnail,
			'short_desc' => $short_desc,
			'description' => $description,
			'category_ids' => $category_ids,
			'cat_id' => $cat_id,
			'brand_id' => $brand_id,
			'tax_id' => $tax_id,
			'collection_id' => $collection_id,
			'is_featured' => $is_featured,
			'variation_size' => $variation_size,
			'sale_price' => $sale_price,
			'lan' => $lan,
			'exdate' => $exdate,
			'manufacture_date' => $manufacture_date,
			'maxorderqty' => $maxorderqty,
		);
		
		$response = Product::where('id', $id)->update($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Updated Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data update failed');
		}
		
		return response()->json($res);
    }
	
    //get Price
    public function getPricePageData($id){
		
		$datalist = Product::where('id', $id)->first();

        return view('seller.price', compact('datalist'));
    }
	
	//Save data for Price
    public function savePriceData(Request $request){
		$res = array();

		$id = $request->input('RecordId');
		$cost_price = $request->input('cost_price');
		$sale_price = $request->input('sale_price');
		$old_price = $request->input('old_price');
		$start_date = date("Y-m-d");
		$end_date = $request->input('end_date');
		$is_discount = $request->input('is_discount');

		$validator_array = array(
			'sale_price' => $sale_price
		);
		
		$validator = Validator::make($validator_array, [
			'sale_price' => 'required'
		]);

		$errors = $validator->errors();
		
		if($errors->has('sale_price')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('sale_price');
			return response()->json($res);
		}
		
		if($end_date == ''){
			$data = array(
				'cost_price' => $cost_price,
				'sale_price' => $sale_price,
				'old_price' => $old_price,
				'start_date' => NULL,
				'end_date' => NULL,
				'is_discount' => $is_discount
			);
		}else{
			$data = array(
				'cost_price' => $cost_price,
				'sale_price' => $sale_price,
				'old_price' => $old_price,
				'start_date' => $start_date,
				'end_date' => $end_date,
				'is_discount' => $is_discount
			);
		}
		
		$response = Product::where('id', $id)->update($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Updated Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data update failed');
		}
		
		return response()->json($res);
    }
	
    //get Inventory
    public function getInventoryPageData($id){
		
		$datalist = Product::where('id', $id)->first();

        return view('seller.inventory', compact('datalist'));
    }

	// Check SKU
	public function checkSku(Request $request)
	{
		$sku = trim($request->get('sku'));

		$exists = DB::table('products')->where('sku', $sku)->exists();

		return response()->json(['exists' => $exists]);
	}

	
	//Save data for Inventory
    public function saveInventoryData(Request $request){
		$res = array();

		$id = $request->input('RecordId');
		$is_stock = $request->input('is_stock');
		$stock_status_id = $request->input('stock_status_id');
		$sku = $request->input('sku');
		$stock_qty = $request->input('stock_qty');

		$data = array(
			'is_stock' => $is_stock,
			'stock_status_id' => $stock_status_id,
			'sku' => $sku,
			'stock_qty' => $stock_qty
		);
		
		$response = Product::where('id', $id)->update($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Updated Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data update failed');
		}
		
		return response()->json($res);
    }

	// Shipping 
	public function getShippingPageData($id){
		
		$datalist = Product::where('id', $id)->first();
		$delivarytypes = DeliveryType::orderBy('lable','asc')->get();

		$selectedDeliveryTypes = explode(',', $datalist['delivarytypeid'] ?? '');

		$shippingMethod = ProductShipping::where('product_id', $id)->first();

        return view('seller.shipping', compact('datalist', 'selectedDeliveryTypes', 'delivarytypes', 'shippingMethod'));
    }

	public function saveShippingData(Request $request)
	{
		$res = [];

		$productId = $request->input('RecordId');
		$deliveryTypes = $request->input('delivarytypeid'); // array
		$slabs = $request->input('slabs'); 
		$perisible = $request->has('perisible') ? 1 : 0;
		$pincode = $request->input('pincode');

		// Step 1: Update delivarytypeid in products table
		$deliveryTypeString = is_array($deliveryTypes) ? implode(',', $deliveryTypes) : null;

		$productUpdate = Product::where('id', $productId)
			->update(['delivarytypeid' => $deliveryTypeString, 'perisible' => $perisible]);

		// Step 2: Filter valid slabs (where all fields are filled)
		$validSlabs = [];
		if (!empty($slabs)) {
			foreach ($slabs as $slab) {
				if (
					isset($slab['min_qty'], $slab['max_qty'], $slab['price']) &&
					$slab['min_qty'] != '' &&
					$slab['max_qty'] != '' &&
					$slab['price'] != ''
				) {
					$validSlabs[] = [
						'min_qty' => (int)$slab['min_qty'],
						'max_qty' => (int)$slab['max_qty'],
						'price' => (float)$slab['price'],
					];
				}
			}
		}

		// Step 3: Save or update product shipping data
		if (!empty($validSlabs)) {
			ProductShipping::updateOrCreate(
				['product_id' => $productId],
				[
					'slab' => json_encode($validSlabs),
					'pincode' => $pincode,
				]
			);
		}

		if ($productUpdate || !empty($validSlabs)) {
			$res['msgType'] = 'success';
			$res['msg'] = __('Shipping data saved successfully.');
		} else {
			$res['msgType'] = 'error';
			$res['msg'] = __('No data to save.');
		}

		return response()->json($res);
	}

	
    //get Product Images
    public function getProductImagesPageData($id){
		
		$datalist = Product::where('id', $id)->first();
		$imagelist = Pro_image::where('product_id', $id)->orderBy('id','desc')->paginate(15);
		
        return view('seller.product-images', compact('datalist', 'imagelist'));
    }
	
	//Get data for Product Images Pagination
	public function getProductImagesTableData(Request $request){

		$id = $request->id;
		
		if($request->ajax()){
			$imagelist = Pro_image::where('product_id', $id)->orderBy('id','desc')->paginate(15);

			return view('seller.partials.product_images_list', compact('imagelist'))->render();
		}
	}
	
	//Save data for Product Images
    public function saveProductImagesData(Request $request){
		$res = array();

		$product_id = $request->input('product_id');
		$thumbnail = $request->input('thumbnail');
		$large_image = $request->input('large_image');
		
		$validator_array = array(
			'image' => $request->input('thumbnail')
		);
		
		$validator = Validator::make($validator_array, [
			'image' => 'required'
		]);

		$errors = $validator->errors();

		if($errors->has('image')){
			$res['msgType'] = 'error';
			$res['msg'] = $errors->first('image');
			return response()->json($res);
		}
		
		$data = array(
			'product_id' => $product_id,
			'thumbnail' => $thumbnail,
			'large_image' => $large_image
		);
		
		$response = Pro_image::create($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('New Data Added Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data insert failed');
		}
		
		return response()->json($res);
    }
	
	//Delete data for Product Images
	public function deleteProductImages(Request $request){
		$res = array();

		$id = $request->id;

		if($id != ''){
			$response = Pro_image::where('id', $id)->delete();
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

    //get Variations
    public function getVariationsPageData($id){
		
		$datalist = Product::where('id', $id)->first();
		$sizelist = Attribute::where('att_type', 'Size')->orderBy('id','asc')->get();
		$colorlist = Attribute::where('att_type', 'Color')->orderBy('id','asc')->get();
		
        return view('seller.variations', compact('datalist', 'sizelist', 'colorlist'));
    }
	
	//Save data for Variations
    public function saveVariationsData(Request $request){
		$res = array();

		$id = $request->input('RecordId');
		$sizes = $request->input('variation_size');
		$colors = $request->input('variation_color');

		$variation_size = NULL;
		$i = 0;
		if($sizes !=''){
			foreach ($sizes as $key => $size) {
				if($i++){
					$variation_size .= ',';
				}
				$variation_size .= $size;
			}
		}
		
		$variation_color = NULL;
		$f = 0;
		if($colors !=''){
			foreach ($colors as $key => $color) {
				if($f++){
					$variation_color .= ',';
				}
				$variation_color .= $color;
			}
		}
		$data = array(
			'variation_size' => $variation_size,
			'variation_color' => $variation_color
		);
		
		$response = Product::where('id', $id)->update($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Updated Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data update failed');
		}
		
		return response()->json($res);
    }
	
    //get Product SEO
    public function getProductSEOPageData($id){
		
		$datalist = Product::where('id', $id)->first();

        return view('seller.product-seo', compact('datalist'));
	}
	
	//Save data for Product SEO
    public function saveProductSEOData(Request $request){
		$res = array();

		$id = $request->input('RecordId');
		$og_title = $request->input('og_title');
		$og_image = $request->input('og_image');
		$og_description = $request->input('og_description');
		$og_keywords = $request->input('og_keywords');

		$data = array(
			'og_title' => $og_title,
			'og_image' => $og_image,
			'og_description' => $og_description,
			'og_keywords' => $og_keywords
		);
		
		$response = Product::where('id', $id)->update($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Updated Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data update failed');
		}
		
		return response()->json($res);
    }
	
    //get Related Products
    public function getRelatedProductsPageData($id){
		$user_id = Auth::user()->id;
		
		$datalist = Product::where('id', $id)->first();
		
		$productlist = DB::table('products')
			->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
			->join('languages', 'products.lan', '=', 'languages.language_code')
			->select('products.id', 'products.title', 'products.f_thumbnail', 'products.cost_price', 'products.sale_price', 'products.is_stock', 'products.is_publish', 'tp_status.status', 'languages.language_name')
			->whereNotIn('products.id', [$id])
			->where('products.is_publish', 1)
			->where('products.user_id', $user_id)
			->orderBy('products.id','desc')
			->paginate(20);
			
		$relateddatalist = DB::table('products')
			->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
			->join('languages', 'products.lan', '=', 'languages.language_code')
			->join('related_products', 'products.id', '=', 'related_products.related_item_id')
			->select('related_products.id', 'products.title', 'products.f_thumbnail', 'products.is_publish', 'tp_status.status', 'languages.language_name')
			->where('related_products.product_id', $id)
			->where('products.is_publish', 1)
			->where('products.user_id', $user_id)
			->orderBy('related_products.id','desc')
			->paginate(20);
			
        return view('seller.related-products', compact('datalist', 'productlist', 'relateddatalist'));
    }
	
	//Get data for Products Pagination Related Products
	public function getProductListForRelatedTableData(Request $request){
		$user_id = Auth::user()->id;
		$search = $request->search;
		$id = $request->product_id;
		
		if($request->ajax()){

			if($search != ''){
				$productlist = DB::table('products')
					->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
					->join('languages', 'products.lan', '=', 'languages.language_code')
					->select('products.id', 'products.title', 'products.f_thumbnail', 'products.cost_price', 'products.sale_price', 'products.is_stock', 'products.is_publish', 'tp_status.status', 'languages.language_name')
					->where(function ($query) use ($search){
						$query->where('title', 'like', '%'.$search.'%')
							->orWhere('cost_price', 'like', '%'.$search.'%');
					})
					->whereNotIn('products.id', [$id])
					->where('products.is_publish', 1)
					->where('products.user_id', $user_id)
					->orderBy('products.id','desc')
					->paginate(20);
			}else{
				$productlist = DB::table('products')
					->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
					->join('languages', 'products.lan', '=', 'languages.language_code')
					->select('products.id', 'products.title', 'products.f_thumbnail', 'products.cost_price', 'products.sale_price', 'products.is_stock', 'products.is_publish', 'tp_status.status', 'languages.language_name')
					->whereNotIn('products.id', [$id])
					->where('products.is_publish', 1)
					->where('products.user_id', $user_id)
					->orderBy('products.id','desc')
					->paginate(20);
			}

			return view('seller.partials.products_list_for_related_product', compact('productlist'))->render();
		}
	}
	
	//Get data for Related Products Pagination
	public function getRelatedProductTableData(Request $request){
		$user_id = Auth::user()->id;
		$search = $request->search;
		$id = $request->product_id;
		
		if($request->ajax()){

			if($search != ''){
				$relateddatalist = DB::table('products')
					->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
					->join('languages', 'products.lan', '=', 'languages.language_code')
					->join('related_products', 'products.id', '=', 'related_products.related_item_id')
					->select('related_products.id', 'products.title', 'products.f_thumbnail', 'products.is_publish', 'tp_status.status', 'languages.language_name')
					->where(function ($query) use ($search){
						$query->where('title', 'like', '%'.$search.'%')
							->orWhere('languages.language_name', 'like', '%'.$search.'%');
					})
					->where('related_products.product_id', $id)
					->where('products.is_publish', 1)
					->where('products.user_id', $user_id)
					->orderBy('related_products.id','desc')
					->paginate(20);
			}else{
				$relateddatalist = DB::table('products')
					->join('tp_status', 'products.is_publish', '=', 'tp_status.id')
					->join('languages', 'products.lan', '=', 'languages.language_code')
					->join('related_products', 'products.id', '=', 'related_products.related_item_id')
					->select('related_products.id', 'products.title', 'products.f_thumbnail', 'products.is_publish', 'tp_status.status', 'languages.language_name')
					->where('related_products.product_id', $id)
					->where('products.is_publish', 1)
					->where('products.user_id', $user_id)
					->orderBy('related_products.id','desc')
					->paginate(20);
			}

			return view('seller.partials.related_products_table', compact('relateddatalist'))->render();
		}
	}
	
	//Save data for Related Products
    public function saveRelatedProductsData(Request $request){
		$res = array();

		$product_id = $request->input('product_id');
		$related_item_id = $request->input('related_item_id');

		$data = array(
			'product_id' => $product_id,
			'related_item_id' => $related_item_id
		);
		
		$response = Related_product::create($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('New Data Added Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data insert failed');
		}
		
		return response()->json($res);
    }
	
	//Delete data for Related Product
	public function deleteRelatedProduct(Request $request){
		$res = array();

		$id = $request->id;

		$response = Related_product::where('id', $id)->delete();
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Removed Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data remove failed');
		}
		
		return response()->json($res);
	}
}