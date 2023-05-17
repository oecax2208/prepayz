<?php

namespace App\Http\Controllers;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\Cart;
use App\Models\Brand;
use App\User;
use App\Models\OrdersFaspay;
use Auth;
use Session;
use Newsletter;
use DB;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Helper;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Faspay\Credit\Payment\FaspayPaymentCredit;
use App\Faspay\Credit\Payment\FaspayPaymentCreditWrapperProd;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditBillData;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditCardData;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditConfigApp;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditDomicileData;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditItemData;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditShippingdata;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditShopperData;
use App\Faspay\Credit\Payment\Wrapper\FaspayPaymentCreditTransactionData;
class FrontendController extends Controller
{

    public function index(Request $request){
        return redirect()->route($request->user()->role);
    }

    public function home(){
        $featured=Product::where('status','active')->where('is_featured',1)->orderBy('price','DESC')->limit(2)->get();
        $posts=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        $banners=Banner::where('status','active')->limit(3)->orderBy('id','DESC')->get();
        // return $banner;
        $products=Product::where('status','active')->orderBy('id','DESC')->limit(8)->get();
        $category=Category::where('status','active')->where('is_parent',1)->orderBy('title','ASC')->get();
        // return $category;
        return view('frontend.index')
                ->with('featured',$featured)
                ->with('posts',$posts)
                ->with('banners',$banners)
                ->with('product_lists',$products)
                ->with('category_lists',$category);
    }

    public function aboutUs(){
        return view('frontend.pages.about-us');
    }

    public function contact(){
        return view('frontend.pages.contact');
    }

    public function productDetail($slug){
        $product_detail= Product::getProductBySlug($slug);
        // dd($product_detail);
        return view('frontend.pages.product_detail')->with('product_detail',$product_detail);
    }

    public function productGrids(){
        $products=Product::query();

        if(!empty($_GET['category'])){
            $slug=explode(',',$_GET['category']);
            // dd($slug);
            $cat_ids=Category::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
            // dd($cat_ids);
            $products->whereIn('cat_id',$cat_ids);
            // return $products;
        }
        if(!empty($_GET['brand'])){
            $slugs=explode(',',$_GET['brand']);
            $brand_ids=Brand::select('id')->whereIn('slug',$slugs)->pluck('id')->toArray();
            return $brand_ids;
            $products->whereIn('brand_id',$brand_ids);
        }
        if(!empty($_GET['sortBy'])){
            if($_GET['sortBy']=='title'){
                $products=$products->where('status','active')->orderBy('title','ASC');
            }
            if($_GET['sortBy']=='price'){
                $products=$products->orderBy('price','ASC');
            }
        }

        if(!empty($_GET['price'])){
            $price=explode('-',$_GET['price']);
            // return $price;
            // if(isset($price[0]) && is_numeric($price[0])) $price[0]=floor(Helper::base_amount($price[0]));
            // if(isset($price[1]) && is_numeric($price[1])) $price[1]=ceil(Helper::base_amount($price[1]));

            $products->whereBetween('price',$price);
        }

        $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        // Sort by number
        if(!empty($_GET['show'])){
            $products=$products->where('status','active')->paginate($_GET['show']);
        }
        else{
            $products=$products->where('status','active')->paginate(9);
        }
        // Sort by name , price, category


        return view('frontend.pages.product-grids')->with('products',$products)->with('recent_products',$recent_products);
    }
    public function productLists(){
        $products=Product::query();

        if(!empty($_GET['category'])){
            $slug=explode(',',$_GET['category']);
            // dd($slug);
            $cat_ids=Category::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
            // dd($cat_ids);
            $products->whereIn('cat_id',$cat_ids)->paginate;
            // return $products;
        }
        if(!empty($_GET['brand'])){
            $slugs=explode(',',$_GET['brand']);
            $brand_ids=Brand::select('id')->whereIn('slug',$slugs)->pluck('id')->toArray();
            return $brand_ids;
            $products->whereIn('brand_id',$brand_ids);
        }
        if(!empty($_GET['sortBy'])){
            if($_GET['sortBy']=='title'){
                $products=$products->where('status','active')->orderBy('title','ASC');
            }
            if($_GET['sortBy']=='price'){
                $products=$products->orderBy('price','ASC');
            }
        }

        if(!empty($_GET['price'])){
            $price=explode('-',$_GET['price']);
            // return $price;
            // if(isset($price[0]) && is_numeric($price[0])) $price[0]=floor(Helper::base_amount($price[0]));
            // if(isset($price[1]) && is_numeric($price[1])) $price[1]=ceil(Helper::base_amount($price[1]));

            $products->whereBetween('price',$price);
        }

        $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        // Sort by number
        if(!empty($_GET['show'])){
            $products=$products->where('status','active')->paginate($_GET['show']);
        }
        else{
            $products=$products->where('status','active')->paginate(6);
        }
        // Sort by name , price, category


        return view('frontend.pages.product-lists')->with('products',$products)->with('recent_products',$recent_products);
    }
    public function productFilter(Request $request){
            $data= $request->all();
            // return $data;
            $showURL="";
            if(!empty($data['show'])){
                $showURL .='&show='.$data['show'];
            }

            $sortByURL='';
            if(!empty($data['sortBy'])){
                $sortByURL .='&sortBy='.$data['sortBy'];
            }

            $catURL="";
            if(!empty($data['category'])){
                foreach($data['category'] as $category){
                    if(empty($catURL)){
                        $catURL .='&category='.$category;
                    }
                    else{
                        $catURL .=','.$category;
                    }
                }
            }

            $brandURL="";
            if(!empty($data['brand'])){
                foreach($data['brand'] as $brand){
                    if(empty($brandURL)){
                        $brandURL .='&brand='.$brand;
                    }
                    else{
                        $brandURL .=','.$brand;
                    }
                }
            }
            // return $brandURL;

            $priceRangeURL="";
            if(!empty($data['price_range'])){
                $priceRangeURL .='&price='.$data['price_range'];
            }
            if(request()->is('e-shop.loc/product-grids')){
                return redirect()->route('product-grids',$catURL.$brandURL.$priceRangeURL.$showURL.$sortByURL);
            }
            else{
                return redirect()->route('product-lists',$catURL.$brandURL.$priceRangeURL.$showURL.$sortByURL);
            }
    }
    public function productSearch(Request $request){
        $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        $products=Product::orwhere('title','like','%'.$request->search.'%')
                    ->orwhere('slug','like','%'.$request->search.'%')
                    ->orwhere('description','like','%'.$request->search.'%')
                    ->orwhere('summary','like','%'.$request->search.'%')
                    ->orwhere('price','like','%'.$request->search.'%')
                    ->orderBy('id','DESC')
                    ->paginate('9');
        return view('frontend.pages.product-grids')->with('products',$products)->with('recent_products',$recent_products);
    }

    public function productBrand(Request $request){
        $products=Brand::getProductByBrand($request->slug);
        $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        if(request()->is('e-shop.loc/product-grids')){
            return view('frontend.pages.product-grids')->with('products',$products->products)->with('recent_products',$recent_products);
        }
        else{
            return view('frontend.pages.product-lists')->with('products',$products->products)->with('recent_products',$recent_products);
        }

    }
    public function productCat(Request $request){
        $products=Category::getProductByCat($request->slug);
        // return $request->slug;
        $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();

        if(request()->is('e-shop.loc/product-grids')){
            return view('frontend.pages.product-grids')->with('products',$products->products)->with('recent_products',$recent_products);
        }
        else{
            return view('frontend.pages.product-lists')->with('products',$products->products)->with('recent_products',$recent_products);
        }

    }
    public function productSubCat(Request $request){
        $products=Category::getProductBySubCat($request->sub_slug);
        // return $products;
        $recent_products=Product::where('status','active')->orderBy('id','DESC')->limit(3)->get();

        if(request()->is('e-shop.loc/product-grids')){
            return view('frontend.pages.product-grids')->with('products',$products->sub_products)->with('recent_products',$recent_products);
        }
        else{
            return view('frontend.pages.product-lists')->with('products',$products->sub_products)->with('recent_products',$recent_products);
        }

    }

    public function blog(){
        $post=Post::query();

        if(!empty($_GET['category'])){
            $slug=explode(',',$_GET['category']);
            // dd($slug);
            $cat_ids=PostCategory::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
            return $cat_ids;
            $post->whereIn('post_cat_id',$cat_ids);
            // return $post;
        }
        if(!empty($_GET['tag'])){
            $slug=explode(',',$_GET['tag']);
            // dd($slug);
            $tag_ids=PostTag::select('id')->whereIn('slug',$slug)->pluck('id')->toArray();
            // return $tag_ids;
            $post->where('post_tag_id',$tag_ids);
            // return $post;
        }

        if(!empty($_GET['show'])){
            $post=$post->where('status','active')->orderBy('id','DESC')->paginate($_GET['show']);
        }
        else{
            $post=$post->where('status','active')->orderBy('id','DESC')->paginate(9);
        }
        // $post=Post::where('status','active')->paginate(8);
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts',$post)->with('recent_posts',$rcnt_post);
    }

    public function blogDetail($slug){
        $post=Post::getPostBySlug($slug);
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        // return $post;
        return view('frontend.pages.blog-detail')->with('post',$post)->with('recent_posts',$rcnt_post);
    }

    public function blogSearch(Request $request){
        // return $request->all();
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        $posts=Post::orwhere('title','like','%'.$request->search.'%')
            ->orwhere('quote','like','%'.$request->search.'%')
            ->orwhere('summary','like','%'.$request->search.'%')
            ->orwhere('description','like','%'.$request->search.'%')
            ->orwhere('slug','like','%'.$request->search.'%')
            ->orderBy('id','DESC')
            ->paginate(8);
        return view('frontend.pages.blog')->with('posts',$posts)->with('recent_posts',$rcnt_post);
    }

    public function blogFilter(Request $request){
        $data=$request->all();
        // return $data;
        $catURL="";
        if(!empty($data['category'])){
            foreach($data['category'] as $category){
                if(empty($catURL)){
                    $catURL .='&category='.$category;
                }
                else{
                    $catURL .=','.$category;
                }
            }
        }

        $tagURL="";
        if(!empty($data['tag'])){
            foreach($data['tag'] as $tag){
                if(empty($tagURL)){
                    $tagURL .='&tag='.$tag;
                }
                else{
                    $tagURL .=','.$tag;
                }
            }
        }
        // return $tagURL;
            // return $catURL;
        return redirect()->route('blog',$catURL.$tagURL);
    }

    public function blogByCategory(Request $request){
        $post=PostCategory::getBlogByCategory($request->slug);
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts',$post->post)->with('recent_posts',$rcnt_post);
    }

    public function blogByTag(Request $request){
        // dd($request->slug);
        $post=Post::getBlogByTag($request->slug);
        // return $post;
        $rcnt_post=Post::where('status','active')->orderBy('id','DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts',$post)->with('recent_posts',$rcnt_post);
    }

    // Quick checkout
    public function quickCheckout(Product $product_detail){
        return view('frontend.pages.quick-checkout', compact('product_detail'));
    }

    public function paymentFaspayX(Request $request,$id){

        // $merchant_id    = env('FP_CREDIT_MERCHANT_ID');
        // $password 	    = env('FP_CREDIT_PASSWORD');
        $amount 	    = str_replace(",", "", $request->amount);
        $email          = 'ronipaslan4@gmail.com';
        $orderId        = date("YmdGis");
        $date_md5       = md5(date("YmdGis"));
        $uuid_random    = strtoupper(substr($date_md5,0,8)). '' . strtoupper(substr($date_md5,8,4)) . '4'. strtoupper(substr($date_md5,13,3)). ''.substr(md5(uniqid()),0,4) . ''. substr(md5(uniqid()),4,12);
        $transId        = $uuid_random;
        // $signaturecc    = sha1('##'.strtoupper($merchant_id).'##'.strtoupper($password).'##'.$orderId.'##'. $amount .'##'.$transId.'##'.$email.'##');

        // Konfigurasi koneksi ke API Faspay
        $signaturecc=sha1('##'.strtoupper(env('FP_CREDIT_MERCHANT_ID')).'##'.strtoupper(env('FP_CREDIT_PASSWORD')).'##'.$orderId.'##'.$amount.'##'.$transId.'##');
        $post = array(
            "TRANSACTIONTYPE"      => '1',
            "RESPONSE_TYPE"        => '3',
            "MERCHANTID"           => env('FP_CREDIT_MERCHANT_ID'),
            "PAYMENT_METHOD"       => '1',
            "MERCHANT_TRANID"      => $orderId,
            "TRANSACTIONID"        => $transId,
            "AMOUNT"               => $amount,
            "SIGNATURE"            => $signaturecc,
            "CURRENCYCODE"                  => 'IDR',
            "AMOUNT"                        => $amount,
            "CUSTNAME"                      => 'Roni Paslan',
            "CUSTEMAIL"                     => $email,
            "DESCRIPTION"                   => 'transaski test',
            "RETURN_URL"                    => '',
            "BILLING_ADDRESS"               => 'Jl. pintu air raya',
            "BILLING_ADDRESS_CITY"          => 'Jakarta',
            "BILLING_ADDRESS_REGION"        => 'DKI Jakarta',
            "BILLING_ADDRESS_STATE"         => 'DKI Jakarta',
            "BILLING_ADDRESS_POSCODE"       => '10710',
            "BILLING_ADDRESS_COUNTRY_CODE"  => 'ID',
            "RECEIVER_NAME_FOR_SHIPPING"    => 'Faspay test',
            "SHIPPING_ADDRESS"              => 'Jl. pintu air raya',
            "SHIPPING_ADDRESS_CITY"         => 'Jakarta',
            "SHIPPING_ADDRESS_REGION"       => 'DKI Jakarta',
            "SHIPPING_ADDRESS_STATE"        => 'DKI Jakarta',
            "SHIPPING_ADDRESS_POSCODE"      => '10710',
            "SHIPPING_ADDRESS_COUNTRY_CODE" => 'ID',
            "SHIPPINGCOST"                  => '0.00',
            "PHONE_NO"                      => '0897867688989',
            "MPARAM1"                       => '',
            "MPARAM2"                       => '',
            "PYMT_IND"                      => '',
            "PYMT_CRITERIA"                 => '',
            "PYMT_TOKEN"                    => '',
        );

        $post   = http_build_query($post);
        $url    = "https://fpg.faspay.co.id/payment/api";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        print_r($result);
        curl_close($ch);


    }

    public function paymentFaspayXXXX(Request $request,$id){

        $merchant_id    = env('FP_CREDIT_MERCHANT_ID');
        $password 	    = env('FP_CREDIT_PASSWORD');
        $amount 	    = str_replace(",", "", $request->amount);
        $email          = 'ronipaslan4@gmail.com';
        $orderId        = date("YmdGis");
        $date_md5       = md5(date("YmdGis"));
        $uuid_random    = strtoupper(substr($date_md5,0,8)). '' . strtoupper(substr($date_md5,8,4)) . '4'. strtoupper(substr($date_md5,13,3)). ''.substr(md5(uniqid()),0,4) . ''. substr(md5(uniqid()),4,12);
        $transId        = $uuid_random;
        $signaturecc    = sha1('##'.strtoupper($merchant_id).'##'.strtoupper($password).'##'.$orderId.'##'. $amount .'##'.$transId.'##'.$email.'##');
        // $d =[$orderId, $transId, $signaturecc];
        // dd($d);
        $trxtype        = "1";

        // $transId = date("YmdGis");
        // $signaturecc=sha1('##'.strtoupper('aggregator_tes').'##'.strtoupper('ejeussad').'##'.$tranid.'##1000.00##'.'0'.'##');

        $post = array(
        "TRANSACTIONTYPE"               => $trxtype,
        "RESPONSE_TYPE"                 => '3',
        "LANG"                          => '',
        "MERCHANTID"                    => $merchant_id,
        "PAYMENT_METHOD"                => '1',
        // "TXN_PASSWORD"                  => $password,
        "MERCHANT_TRANID"               => $transId,
        "CURRENCYCODE"                  => 'IDR',
        "AMOUNT"                        => $amount,
        "CUSTNAME"                      => 'Roni Paslan',
        "CUSTEMAIL"                     => $email,
        "DESCRIPTION"                   => 'transaski test',
        "RETURN_URL"                    => '',
        "SIGNATURE"                     => $signaturecc,
        "BILLING_ADDRESS"               => 'Jl. pintu air raya',
        "BILLING_ADDRESS_CITY"          => 'Jakarta',
        "BILLING_ADDRESS_REGION"        => 'DKI Jakarta',
        "BILLING_ADDRESS_STATE"         => 'DKI Jakarta',
        "BILLING_ADDRESS_POSCODE"       => '10710',
        "BILLING_ADDRESS_COUNTRY_CODE"  => 'ID',
        "RECEIVER_NAME_FOR_SHIPPING"    => 'Faspay test',
        "SHIPPING_ADDRESS"              => 'Jl. pintu air raya',
        "SHIPPING_ADDRESS_CITY"         => 'Jakarta',
        "SHIPPING_ADDRESS_REGION"       => 'DKI Jakarta',
        "SHIPPING_ADDRESS_STATE"        => 'DKI Jakarta',
        "SHIPPING_ADDRESS_POSCODE"      => '10710',
        "SHIPPING_ADDRESS_COUNTRY_CODE" => 'ID',
        "SHIPPINGCOST"                  => '0.00',
        "PHONE_NO"                      => '0897867688989',
        "MPARAM1"                       => '',
        "MPARAM2"                       => '',
        "PYMT_IND"                      => '',
        "PYMT_CRITERIA"                 => '',
        "PYMT_TOKEN"                    => '',

        /* ==== customize input card page ===== */
        "style_merchant_name"         => 'black',
        "style_order_summary"         => 'black',
        "style_order_no"              => 'black',
        "style_order_desc"            => 'black',
        "style_amount"                => 'black',
        "style_background_left"       => '#fff',
        "style_button_cancel"         => 'grey',
        "style_font_cancel"           => 'white',
        /* ==== logo directly to your url source ==== */
        "style_image_url"           => 'http://url_merchant/image.png',
        );

        //Dev ke = https://fpgdev.faspay.co.id/payment
        $string = '<form method="post" name="form" action="https://fpg.faspay.co.id/payment/api">';
        if ($post != null) {
        foreach ($post as $name=>$value) {
        $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }

        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';
        echo $string;
        exit;
    }

    public function paymentFaspay(Request $request,$id){

        $merchant_id    = env('FP_CREDIT_MERCHANT_ID');
        $password 	    = env('FP_CREDIT_PASSWORD');
        $amount 	    = str_replace(",", "", $request->amount);
        $orderId        = date("YmdGis");
        $date_md5       = md5(date("YmdGis"));
        $uuid_random    = strtoupper(substr($date_md5,0,8)). '-' . strtoupper(substr($date_md5,8,4)) . '-4'. strtoupper(substr($date_md5,13,3)). '-'.substr(md5(uniqid()),0,4) . '-'. substr(md5(uniqid()),4,12);
        $transId        = $uuid_random;
        $signaturecc    = sha1('##'.strtoupper($merchant_id).'##'.strtoupper($password).'##'.$orderId.'##'. $amount .'##'.$transId.'##');
        // $d =[$orderId, $transId, $signaturecc];
        // dd($d);
        $trxtype        = "1";

        $post = array(
            "TRANSACTIONTYPE"               => $trxtype,
            "RESPONSE_TYPE"                 => '3',
            "LANG"                          => 'ID',
            "MERCHANTID"                    => $merchant_id,
            "PAYMENT_METHOD"                => '1',
            // "TXN_PASSWORD"                  => $password,
            "MERCHANT_TRANID"               => $orderId,
            "TRANSACTIONID" 	            => $transId,
            "CURRENCYCODE"                  => 'IDR',
            "AMOUNT"                        => $amount,
            "CUSTNAME"                      => 'merhcant test CC',
            "CUSTEMAIL"                     => 'testing@faspay.co.id',
            "DESCRIPTION"                   => $request->description,
            // "RETURN_URL"                    => redirect()->route('quick.checkout.pay'),
            "SIGNATURE"                     => $signaturecc,
            "BILLING_ADDRESS"               => 'Jl. pintu air raya',
            "BILLING_ADDRESS_CITY"          => 'Jakarta',
            "BILLING_ADDRESS_REGION"        => 'DKI Jakarta',
            "BILLING_ADDRESS_STATE"         => 'DKI Jakarta',
            "BILLING_ADDRESS_POSCODE"       => '10710',
            "BILLING_ADDRESS_COUNTRY_CODE"  => 'ID',
            "RECEIVER_NAME_FOR_SHIPPING"    => 'Faspay test',
            "SHIPPING_ADDRESS"              => 'Jl. pintu air raya',
            "SHIPPING_ADDRESS_CITY"         => 'Jakarta',
            "SHIPPING_ADDRESS_REGION"       => 'DKI Jakarta',
            "SHIPPING_ADDRESS_STATE"        => 'DKI Jakarta',
            "SHIPPING_ADDRESS_POSCODE"      => '10710',
            "SHIPPING_ADDRESS_COUNTRY_CODE" => 'ID',
            "SHIPPINGCOST"                  => '0.00',
            "PHONE_NO"                      => '0897867688989',
            "MPARAM1"                       => '',
            "MPARAM2"                       => '',
            "PYMT_IND"                      => '',
            // "TOKEN_TYPE"                    => '1',
            "PYMT_CRITERIA"                 => '',
            "PYMT_TOKEN"                    => '',

        );

        $string = '<form method="post" name="form" action="https://fpg.faspay.co.id/payment/api">';
        $string .= '@csrf'; // Add this line
        if ($post != null) {
            foreach ($post as $name=>$value) {
                $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }

        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';
        // echo $string;
        return view('frontend.pages.quick_checkout_response', compact('string'));

        // exit;

    }

    public function quickCheckoutResponsePay(){
        $result = "Your order has been succeed";
        return view('frontend.pages.payment-success', compact('result'));

    }

    function convertToIDR($amount, $from) {
        // Set API endpoint URL and access key
        $url = "https://api.apilayer.com/exchangerates_data/convert?to=IDR&from=$from&amount=$amount";
        $apikey = "g8j5zsiOZv8gJGrgC8OGjRduZsXsHppc"; // Replace with your own API key

        // Call API to get exchange rate data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("apikey: $apikey"));
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse API response to get IDR amount
        $data = json_decode($response, true);
        $idr_amount = $data['result'];

        // Format IDR amount with thousands separator and decimal places
        $idr_amount_formatted = number_format($idr_amount, 2, ',', '.');

        // Return IDR amount with currency symbol
        return "Rp$idr_amount_formatted";
      }


    public function paymentFaspay1(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required',
                Rule::unique('transactions', 'order_id')
            ],
            'amount' => 'required|numeric',
            'description' => 'required|string|max:255',
            'card_number' => 'required|numeric',
            'expiration_month' => 'required|regex:/^\d{2}\/\d{2}$/',
            'expiration_year' => [
                'required',
                'numeric',
                'digits:4',
                'after_or_equal:' . date('Y')
            ],
            'card_cvv' => 'required|numeric',
            'cardholder_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            $request->session()->flash('error', $validator->errors()->first());
            return redirect()->back()->withInput();
        }

        $merchant_id	= '35258';
        $user_id 		= 'bot'.$merchant_id;
        $passw 			= 'jQbpI1mv';
        $bill_no 		= date('Ymdhis');
        $bill_date 		= date('Y-m-d H:i:s');
        $bill_expired 	= date("Y-m-d H:i:s", strtotime ("+1 hour"));
        $payment_channel = '402';
        $signature		= sha1(md5(($user_id.$passw.$bill_no)));
        $env			= 'dev';
        dd($signature);

        $data = array('request' 			=> 'Transmisi Info Detil Pembelian' ,
                        'merchant_id' 		=> $merchant_id ,
                        'merchant'			=> 'Internet Digital Media' ,
                        'bill_no'			=> $bill_no,
                        'bill_reff'			=> 'AZ'.$bill_no ,
                        'bill_date'			=> $bill_date,
                        'bill_expired'		=> $bill_expired,
                        "bill_desc"			=> "Pembayaran #".$bill_no,
                        "bill_currency"		=> "IDR",
                        "bill_gross"		=> "1000000",
                        "bill_miscfee"		=> "500000",
                        "bill_total"		=> "1500000",
                        "cust_no"			=> "A001",
                        "cust_name"			=> "faspay",
                        "cust_lastname"		=> "test",
                        "payment_channel"	=> $payment_channel,
                        "pay_type"			=> "1",
                        "bank_userid"		=> "",
                        "msisdn"			=> "08123456789",
                        "email"				=> "test@test.com",
                        "terminal"			=> "10",
                        "billing_name"		=> "test faspay",
                        "billing_lastname"	=> "0",
                        "billing_address"	=> "jalan pintu air raya",
                        "billing_address_city"		=> "Jakarta Pusat",
                        "billing_address_region"	=> "DKI Jakarta",
                        "billing_address_state"		=> "Indonesia",
                        "billing_address_poscode"	=> "10710",
                        "billing_msisdn"			=> "08123456789",
                        "billing_address_country_code"	=> "ID",
                        "receiver_name_for_shipping"	=> "Faspay Test",
                        "shipping_lastname"				=> "",
                        "shipping_address"				=> "jalan pintu air raya",
                        "shipping_address_city"			=> "Jakarta Pusat",
                        "shipping_address_region"		=> "DKI Jakarta",
                        "shipping_address_state"		=> "Indonesia",
                        "shipping_address_poscode"		=> "10710",
                        "shipping_msisdn"				=> "08123456789",
                        "shipping_address_country_code"	=> "ID",
                        "item" => array('id' 			=> "XYZ001" ,
                                        "product"		=> "Iphone 12",
                                        "qty"			=> "1",
                                        "amount"		=> "1000000",
                                        "payment_plan"	=> "01",
                                        "merchant_id"	=> "BC001",
                                        "tenor"			=> "00",
                                        "type"			=> "Smartphone",
                                        "url"			=> "https://merchant_website/product",
                                        "image_url"		=> "https://merchant_image_url/Mffc35PH77Dq7USrHb4qNm-1200-80.jpg"

                         ),

                        "reserve1"						=> "",
                        "reserve2"						=> "",
                        "signature"						=> $signature


         );

        $request1 = json_encode($data);


        // if ($env == 'dev') {
            // $url='https://dev.faspay.co.id/cvr/300011/10';
            $url='https://debit-sandbox.faspay.co.id/cvr/300011/10';

        // }else{
            // $url='https://web.faspay.co.id/cvr/300011/10';
        // }


        $c = curl_init ($url);
        curl_setopt ($c, CURLOPT_POST, true);
        curl_setopt ($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt ($c, CURLOPT_POSTFIELDS, $request1);
        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($c, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec ($c);


        curl_close($c);
        $data_response = json_decode($response);
        // dd($data_response);

        $redirect_url = $data_response->redirect_url;

        /* ======= redirect to faspay URL =======*/
        // header("Location:$redirect_url");

        // $redirectUrl = 'https://fpgdev.faspay.co.id/payment';
        // $queryString = http_build_query($payload);
        // $redirectUrl .= '?'.$queryString;
        return Redirect::away($redirect_url);
    }

    // Login
    public function login(){
        return view('frontend.pages.login');
    }
    public function loginSubmit(Request $request){
        $data= $request->all();
        if(Auth::attempt(['email' => $data['email'], 'password' => $data['password'],'status'=>'active'])){
            Session::put('user',$data['email']);
            request()->session()->flash('success','Successfully login');
            return redirect()->route('home');
        }
        else{
            request()->session()->flash('error','Invalid email and password pleas try again!');
            return redirect()->back();
        }
    }

    public function logout(){
        Session::forget('user');
        Auth::logout();
        request()->session()->flash('success','Logout successfully');
        return back();
    }

    public function register(){
        return view('frontend.pages.register');
    }
    public function registerSubmit(Request $request){
        // return $request->all();
        $this->validate($request,[
            'name'=>'string|required|min:2',
            'email'=>'string|required|unique:users,email',
            'password'=>'required|min:6|confirmed',
        ]);
        $data=$request->all();
        // dd($data);
        $check=$this->create($data);
        Session::put('user',$data['email']);
        if($check){
            request()->session()->flash('success','Successfully registered');
            return redirect()->route('home');
        }
        else{
            request()->session()->flash('error','Please try again!');
            return back();
        }
    }
    public function create(array $data){
        return User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'status'=>'active'
            ]);
    }
    // Reset password
    public function showResetForm(){
        return view('auth.passwords.old-reset');
    }

    public function subscribe(Request $request){
        if(! Newsletter::isSubscribed($request->email)){
                Newsletter::subscribePending($request->email);
                if(Newsletter::lastActionSucceeded()){
                    request()->session()->flash('success','Subscribed! Please check your email');
                    return redirect()->route('home');
                }
                else{
                    Newsletter::getLastError();
                    return back()->with('error','Something went wrong! please try again');
                }
            }
            else{
                request()->session()->flash('error','Already Subscribed');
                return back();
            }
    }

}
