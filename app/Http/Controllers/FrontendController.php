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

    public function paymentFaspay(Request $request,$id){
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

        $tranid = date("YmdGis");
        $token = '09B9C3D7-7365-4233-8C11-1C3A176F9D2B';

        $signaturecc=sha1('##'.strtoupper('faspay').'##'.strtoupper('abcde').'##'.$token.'##');
        $post = array(
                    "TRANSACTIONTYPE"               => '5',
                    "RESPONSE_TYPE"                 => '3',
                    "MERCHANTID"                    => '35258',
                    "PAYMENT_METHOD"                => '1',
                    "TOKEN_TYPE"                    => '1', //1 untuk static token, 0 untuk dynamic token
                    "PYMT_TOKEN"                    => '09B9C3D7-7365-4233-8C11-1C3A176F9D2B',
                    "AMOUNT"                        => $request->amount,
                    "CURRENCYCODE"                  => 'IDR',
                    "CARDNAME"                      => $request->cardholder_name,
                    "CARDTYPE"                      => 'V',
                    "CARDNO"                        => $request->card_number,
                    "CARDCVC"                       => $request->card_cvv,
                    "EXPIRYMONTH"                   => $request->expiration_month,
                    "EXPIRYYEAR"                    => $request->expiration_year,
                    "CARD_ISSUER_BANK_COUNTRY_CODE" => 'INDONESIA',
                    "savecard"                      => '1',
                    "paymentoption"                 => '1',
                    "PYMT_IND"                      => '',
                    "PYMT_CRITERIA"                 => '',
                    "MPARAM1"                       => '',
                    "SIGNATURE"                     => $signaturecc
                    );


        $string = '<form method="post" name="form" action="https://fpg.faspay.co.id/payment/api">';  // yang diubah URLnya ke prod apa dev
        if ($post != null) {
            foreach ($post as $name=>$value) {
                $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
                }
        }

        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';
        return view('frontend.pages.quick_checkout_response', compact('string'));
        // echo $string;
        exit;
    }


    public function paymentFaspayTEST(Request $request,$id){
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

        // $merchant_id = "35258";
        // $merchant_secret = "jQbpI1mv";
        // // Generate a unique identifier using uniqid function
        $uniqueId = uniqid();
        // // Remove the first 7 characters to get 10 characters
        $uniqueId = substr($uniqueId, 7, 10);
        // // Insert hyphens at appropriate positions to get the final format
        $invoice_no = substr($uniqueId, 0, 8) . "-" . substr($uniqueId, 8, 4) . "-" . substr($uniqueId, 12, 4) . "-" . substr($uniqueId, 16, 4) . "-" . substr($uniqueId, 20);
        // dd($invoice_no);

        $amount = $request->amount;
        // $currency = "IDR";
        // // $redirect_url = "https://your_website.com/redirect_url";
        // // $callback_url = "https://your_website.com/callback_url";

        // $signature = md5($merchant_id.$merchant_secret.$invoice_no.$amount.$currency);

        $signaturecc=sha1('##'.strtoupper('35258').'##'.strtoupper('jQbpI1mv').'##'. $uniqueId .'##'. $amount .'##'.$invoice_no.'##');
        $post = array(
            "TRANSACTIONTYPE"               => '4',
            "RESPONSE_TYPE"                 => '3',
            "MERCHANTID"                    => '35258',
            "PAYMENT_METHOD"                => '3',
            "MERCHANT_TRANID"               => $uniqueId,
            "TRANSACTIONID"                 => $invoice_no,
            "AMOUNT"                        => $amount,
            "CURRENCYCODE"                  => 'IDR',
            "CARDNAME"                      => '',
            "CARDTYPE"                      => '',
            "CARDNO"                        => '',
            "CARDCVC"                       => '',
            "EXPIRYMONTH"                   => '',
            "EXPIRYYEAR"                    => '',
            "CARD_ISSUER_BANK_COUNTRY_CODE" => '',
            "savecard"                      => '',
            "paymentoption"                 => '',
            "PYMT_IND"                      => '',
            "PYMT_CRITERIA"                 => '',
            "PYMT_TOKEN"                    => '',
            "MPARAM1"                       => '',
            "SIGNATURE"                     => $signaturecc
        );

        $post   = http_build_query($post);
        $url    = "https://fpgdev.faspay.co.id/payment/api";
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

        $string = curl_exec($ch);
        dd($string);
        curl_close($ch);

        return view('frontend.pages.quick_checkout_response', compact('string'));

        // if($response_data['response_code'] == '00') {
        //     // Redirect to payment page
        //     header('Location: '.$response_data['payment_url']);
        // } else {
        //     // Handle error
        //     echo $response_data['response_desc'];
        // }
    }

    public function paymentFaspay4(Request $request,$id){
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

        // // Generate a unique identifier using uniqid function
        // $uniqueId = uniqid();
        // // Remove the first 7 characters to get 10 characters
        // $uniqueId = substr($uniqueId, 7, 10);
        // // Insert hyphens at appropriate positions to get the final format
        // $orderId = substr($uniqueId, 0, 8) . "-" . substr($uniqueId, 8, 4) . "-" . substr($uniqueId, 12, 4) . "-" . substr($uniqueId, 16, 4) . "-" . substr($uniqueId, 20);

        // $transId = date("YmdGis");
        // // $signaturecc=sha1('##'.strtoupper('35258').'##'.strtoupper('jQbpI1mv').'##3180550000000091##1000.00##'.'691FCAFC-BFE3-47A5-A4F0-B2C21AB589FE'.'##');
        // $signaturecc=sha1('##'.strtoupper('35258').'##'.strtoupper('jQbpI1mv').'##'. Strtoupper($transId).'##'. $request->amount .'##'.$orderId.'##');

        // // ****************************************************
        // $merchant_id = "35258";
        // $password 	 = "jQbpI1mv";
        // $amount 	 = $request->amount;
        // $signature = strtoupper(sha1(strtoupper('##'.$merchant_id.'##'.$password.'##'.$orderId.'##'.$amount.'##'.$transId.'##')));
        // $trxtype = "";
        // // if ($stat=="A") {
        //     // $trxtype = '2';
        // // }elseif ($stat=="V") {
        //     // $trxtype = '10';
        //     $trxtype = '4';

        // // }
        // $post = array(
        //     "PAYMENT_METHOD"	=> '1',
        //     "TRANSACTIONTYPE"	=> $trxtype,
        //     "MERCHANTID"		=> $merchant_id,
        //     "MERCHANT_TRANID"	=> $transId,
        //     "TRANSACTIONID" 	=> $orderId,
        //     "AMOUNT"			=> $amount,
        //     "RESPONSE_TYPE"		=> '3',
        //     "TXN_PASSWORD"      => 'jQbpI1mv',
        //     "CURRENCYCODE"      => 'IDR',
        //     "SIGNATURE"			=> $signature,
        // );
        // $a	= $this->inquiryCapture($post);
	    // return $a;
        $mid    = 'simulator';
        $passw    = 'abcde';
        $trx_id    = date("YmdHis");
        $bill_total   = number_format(5000,2,".","");
        $address   = 'Jalan Pintu Air Raya No 2A JKT';
        $city    = 'Daerah Khusus Ibukota Jakarta JKT';
        $region    = 'Indonesia';
        $state    = 'Indonesia';
        $poscode   = '10710';
        $ctrycode   = 'ID';

        $merchant_id  = "35258";
        $member_id   = date("YmdHis");
        $member_name  = "Simulator Test";
        $member_email  = "sanjayaega@gmail.com";
        $member_email_notif = "t";
        $process_date  = "20,21";
        $rec_type   = 1;
        $rec_amount   = number_format(5000,2,".","");
        $rec_start   = "2022-03-10";
        $rec_end   = "2022-07-10";
        $rec_period   = "D";
        $rec_period_at  = 1;
        $rec_accumulate  = "t";
        $rec_accumulate_at = 0;
        $rec_status   = "t";
        $card_expire_notif = 0;

        $user_id  = "bot35258";
        $password  = "jQbpI1mv";

        $rec_signature    = sha1(md5($user_id.$password.$member_id));
        $signaturecc      = sha1('##'.strtoupper($mid).'##'.strtoupper($passw).'##'.$trx_id.'##'.$bill_total.'##0##');

        $string     = "<form method='post' name='frmPayment' action='https://fpg.faspay.co.id/recurring'>";

        $post = array(
                    "LANG"                                    => '',
                    "MERCHANTID"                              => $mid,
                    "PAYMENT_METHOD"                          => '3',
                    "MERCHANT_TRANID"                         => $trx_id,
                    "TXN_PASSWORD"                            => $passw,
                    "CURRENCYCODE"                            => 'IDR',
                    "AMOUNT"                                  => $bill_total,
                    "CUSTNAME"                                => $member_name,
                    "CUSTEMAIL"                               => $member_email,
                    "DESCRIPTION"                             => 'Testing di '.$mid,
                    "RETURN_URL"                              => 'http://stack.faspay.co.id/post_fpg/result.php',
                    "SIGNATURE"                               => $signaturecc,
                    "BILLING_ADDRESS"                         => $address,
                    "BILLING_ADDRESS_CITY"                    => $city,
                    "BILLING_ADDRESS_REGION"                  => $region,
                    "BILLING_ADDRESS_STATE"                   => $state,
                    "BILLING_ADDRESS_POSCODE"                 => $poscode,
                    "BILLING_ADDRESS_COUNTRY_CODE"            => $ctrycode,
                    "RECEIVER_NAME_FOR_SHIPPING"        	  => $member_name,
                    "SHIPPING_ADDRESS"                        => $address,
                    "SHIPPING_ADDRESS_CITY"                   => $city,
                    "SHIPPING_ADDRESS_REGION"                 => $region,
                    "SHIPPING_ADDRESS_STATE"                  => $state,
                    "SHIPPING_ADDRESS_POSCODE"                => $poscode,
                    "SHIPPING_ADDRESS_COUNTRY_CODE"           => $ctrycode,
                    "SHIPPINGCOST"                    		  => '0.00',
                    "PHONE_NO"                                => '',
                    "MREF1"                                   => '',
                    "MREF2"                                   => '',
                    "MREF3"                                   => '',
                    "MREF4"                                   => '',
                    "MREF5"                                   => '',
                    "MREF6"                                   => '',
                    "MREF7"                                   => '',
                    "MREF8"                                   => '',
                    "MREF9"                                   => '',
                    "MREF10"                                  => '',
                    "MPARAM1"                                 => '',
                    "MPARAM2"                                 => '',
                    "CUSTOMER_REF"                            => '',
                    "PYMT_IND"                                => '',
                    "PYMT_CRITERIA"                           => '',
                    "FRISK1"                                  => '',
                    "FRISK2"                                  => '',
                    "DOMICILE_ADDRESS"                        => '',
                    "DOMICILE_ADDRESS_CITY"                   => '',
                    "DOMICILE_ADDRESS_REGION"                 => '',
                    "DOMICILE_ADDRESS_STATE"                  => '',
                    "DOMICILE_ADDRESS_POSCODE"                => '',
                    "DOMICILE_ADDRESS_COUNTRY_CODE"           => '',
                    "DOMICILE_PHONE_NO"                       => '',
                    "merchant_id"                             => $merchant_id,
                    "member_id"                               => $member_id,
                    "member_name"                             => $member_name,
                    "member_email"                            => $member_email,
                    "member_email_notif"                      => $member_email_notif,
                    "process_date"                            => $process_date,
                    "recurring_type"                          => $rec_type,
                    "recurring_amount"                        => $rec_amount,
                    "recurring_start_date"                    => $rec_start,
                    "recurring_end_date"                      => $rec_end,
                    "recurring_period"                        => $rec_period,
                    "recurring_period_at"                     => $rec_period_at,
                    "recurring_accumulate"                    => $rec_accumulate,
                    "recurring_accumulate_at"                 => $rec_accumulate_at,
                    "recurring_status"                        => $rec_status,
                    "recurring_signature"                     => $rec_signature,
                    "card_expire_notif"                       => $card_expire_notif,
        );

        if($post!= null) {
            foreach ($post as $name => $value) {
                    $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }

        $string .='<script> document.frmPayment.submit();</script>';
        $string .= '</form>';

        // echo $string;
        return view('frontend.pages.quick_checkout_response', compact('string'));
    }

    public function paymentFaspay22(Request $request,$id){
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

        // Generate a unique identifier using uniqid function
        $uniqueId = uniqid();
        // Remove the first 7 characters to get 10 characters
        $uniqueId = substr($uniqueId, 7, 10);
        // Insert hyphens at appropriate positions to get the final format
        $orderId = substr($uniqueId, 0, 8) . "-" . substr($uniqueId, 8, 4) . "-" . substr($uniqueId, 12, 4) . "-" . substr($uniqueId, 16, 4) . "-" . substr($uniqueId, 20);

        $transId = date("YmdGis");
        // $signaturecc=sha1('##'.strtoupper('35258').'##'.strtoupper('jQbpI1mv').'##3180550000000091##1000.00##'.'691FCAFC-BFE3-47A5-A4F0-B2C21AB589FE'.'##');
        $signaturecc=sha1('##'.strtoupper('35258').'##'.strtoupper('jQbpI1mv').'##'. Strtoupper($transId).'##'. $request->amount .'##'.$orderId.'##');

        // ****************************************************
        $merchant_id = "35258";
        $password 	 = "jQbpI1mv";
        $amount 	 = $request->amount;
        $signature = strtoupper(sha1(strtoupper('##'.$merchant_id.'##'.$password.'##'.$orderId.'##'.$amount.'##'.$transId.'##')));
        $trxtype = "";
        // if ($stat=="A") {
            // $trxtype = '2';
        // }elseif ($stat=="V") {
            // $trxtype = '10';
            $trxtype = '4';

        // }
        $post = array(
            "PAYMENT_METHOD"	=> '1',
            "TRANSACTIONTYPE"	=> $trxtype,
            "MERCHANTID"		=> $merchant_id,
            "MERCHANT_TRANID"	=> $transId,
            "TRANSACTIONID" 	=> $orderId,
            "AMOUNT"			=> $amount,
            "RESPONSE_TYPE"		=> '3',
            "TXN_PASSWORD"      => 'jQbpI1mv',
            "CURRENCYCODE"      => 'IDR',
            "SIGNATURE"			=> $signature,
        );
        $a	= $this->inquiryCapture($post);
	    return $a;

    }

    function inquiryCapture($post){

        // $url 	= "https://fpgdev.faspay.co.id/payment/api";
        $url 	= "https://fpg.faspay.co.id/payment/api";
        $post   = http_build_query($post);


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
        $result	= curl_exec($ch);
        dd($result);

        curl_close($ch);
        $lines	= explode(';',$result);
        $result = array();
        foreach($lines as $line){
            list($key,$value) = array_pad(explode('=', $line, 2), 2, null);
            $result[trim($key)] = trim($value);
        }
        return view('frontend.pages.quick_checkout_response', compact('result'));

    }

    public function quickCheckoutResponse(Request $request)
    {
        $success = false;
        $responseCode = $request->input('RESPONSE_CODE');

        // Check if the response code is success
        if ($responseCode == '00') {
            $success = true;
        }

        return view('frontend.pages.quick_checkout_response', compact('success'));
    }


    function generateMerchantRef() {
        $timestamp = time();
        $random = rand(1000, 9999);
        $merchant_ref = "REF" . $timestamp . $random;

        return $merchant_ref;
    }

    public function paymentFaspay3(Request $request, $id){
        $name = $request->first_name.' '.$request->last_name;
        $billing_address = [
            'customer_name' => $name,
            'customer_email' => $request->email,
            'phone_no'  => $request->phone,
            'shipping_address' => $request->address1,
            'shipping_state' => $request->city,
            'shipping_post_code' => $request->post_code,
            'shipping_country' => $request->country,
        ];

        $order_number = Carbon::now()->timestamp;

        $cart = [
            "name" => $request->description,
            "qty" => "1",
            "price" => convertToIDR($request->amount),
            // "main_price" => convertToIDR($request->amount),
        ];

        $payment_method = 'Faspay';

        $currency = 'IDR';

        $tax = '0.35';

        $transaction_number = $order_number;

        $order_status = 'Pending';

        $shipping_info = [
            "ship_first_name" => $request->first_name,
            "ship_last_name" => $request->last_name,
            "ship_email" => $request->email,
            "ship_phone" => $request->phone,
            "ship_company" => null,
            "ship_address" => $request->street_address1,
            "ship_zip" => $request->post_code,
            "ship_city" => $request->city,
            "ship_country" => $request->country
        ];

        // $billing_info = [
        //     "_token" => "jTCG9ZNZDqMAR360lPsHDregw1OS4KZw5YQphKZY",
        //     "bill_name" => $request->name,
        //     "bill_email" => $request->email,
        //     "bill_phone" => $request->phone,
        //     "same_ship_address" => "on"
        // ];

        $billing_info = [
            "bill_name" => $request->name,
            "bill_email" => $request->email,
            "bill_phone" => $request->phone,
        ];

        $payment_status = 'Unpaid';

        Session::put('billing_address', $billing_address);

        OrdersFaspay::create([
            'order_number' => $order_number,
            'cart' => json_encode($cart),
            'payment_method' => $payment_method,
            'currency_sign' => $currency,
            'tax' => $tax,
            'transaction_number' => $transaction_number,
            'order_status' => $order_status,
            'shipping_info' => json_encode($shipping_info),
            'billing_info' => json_encode($billing_info),
            'payment_status' => $payment_status,
            'billing_address' => json_encode($billing_address),
        ]);

        return $this->checkoutFaspay(
            $transaction_number, $amount, $currency, $isLN, $isProductPayment
        );


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
