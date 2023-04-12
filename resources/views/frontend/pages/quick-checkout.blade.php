@extends('frontend.layouts.master')

@section('title','Checkout page')

@section('main-content')

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0)">Quick Checkout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Checkout -->
    <section class="shop checkout section">
        <div class="container" style="border: solid 1px #adb5bd;border-radius:5px;padding: 20px;">
            <form class="form" method="POST" action="{{ route('quick.checkout.process', ['id' => $product_detail->id]) }}">
                @csrf
                <div class="col-lg-12 col-12">
                    <div class="checkout-form">
                        <h2 style="text-align:center;">Make Your Checkout Here</h2>
                        <p style="color: orange;text-align:center;">Are you sure you want to checkout without login first?</p>
                        <input type="hidden" name="description" value="{{ $product_detail->title }}" />
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-6">
                                <div class="single-widget">
                                    <img style="display: block;margin: 0 auto;" src="{{ asset('frontend/img/payment-process.png')}}" alt="#">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-6">
                                <div class="order-details">
                                    <!-- Order Widget -->
                                    <div class="single-widget">
                                        <h2>CART TOTALS</h2>
                                        <div class="content">
                                            <ul>
                                                <li><h5>{{$product_detail->title}}</h5></li>
                                                <li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Cart Subtotal
                                                    @php
                                                        $after_discount=($product_detail->price-(($product_detail->price*$product_detail->discount)/100));
                                                    @endphp
                                                    <input type="hidden" value="{{number_format($after_discount,2)}}" name="amount"/>
                                                    <span>Rp.{{number_format($after_discount,2)}}</span><s>Rp.{{number_format($product_detail->price,2)}}</s>
                                                </li>
                                                <li class="shipping">
                                                    Shipping Cost
                                                    @if(count(Helper::shipping())>0 && Helper::cartCount()>0)
                                                        <select name="shipping" class="nice-select">
                                                            <option value="">Select your address</option>
                                                            @foreach(Helper::shipping() as $shipping)
                                                            <option value="{{$shipping->id}}" class="shippingOption" data-price="{{$shipping->price}}">{{$shipping->type}}: Rp.{{$shipping->price}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <span>Free</span>
                                                    @endif
                                                </li>
                                                <input type="hidden" value="{{number_format($after_discount,2)}}" name="amount"/>
                                                <li class="last"  id="order_total_price">Total<span>Rp.{{number_format($after_discount,2)}}</span></li>

                                                {{--
                                                @if(session('coupon'))
                                                <li class="coupon_price" data-price="{{session('coupon')['value']}}">You Save<span>Rp.{{number_format(session('coupon')['value'],2)}}</span></li>
                                                @endif
                                                @php
                                                    $total_amount=Helper::totalCartPrice();
                                                    if(session('coupon')){
                                                        $total_amount=$total_amount-session('coupon')['value'];
                                                    }
                                                @endphp
                                                @if(session('coupon'))
                                                    <li class="last"  id="order_total_price">Total<span>Rp.{{number_format($total_amount,2)}}</span></li>
                                                @else
                                                    <li class="last"  id="order_total_price">Total<span>Rp.{{number_format($total_amount,2)}}</span></li>
                                                @endif
                                                --}}
                                            </ul>
                                        </div>
                                    </div>
                                    <!--/ End Order Widget -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="single-widget get-button">
                        <div class="content">
                            <div class="button">
                                <button style="width: 40%;" type="submit" class="btn">Proceed to checkout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!--/ End Checkout -->

    <!-- Start Shop Newsletter  -->
    <section class="shop-newsletter section">
        <div class="container">
            <div class="inner-top">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2 col-12">
                        <!-- Start Newsletter Inner -->
                        <div class="inner">
                            <h4>Newsletter</h4>
                            <p> Subscribe to our newsletter and get <span>10%</span> off your first purchase</p>
                            <form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">
                                <input name="EMAIL" placeholder="Your email address" required="" type="email">
                                <button class="btn">Subscribe</button>
                            </form>
                        </div>
                        <!-- End Newsletter Inner -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Newsletter -->
@endsection
@push('styles')
	<style>
		li.shipping{
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}
		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}
		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}
		.form-select {
			height: 30px;
			width: 100%;
		}
		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}
		.list li{
			margin-bottom:0 !important;
		}
		.list li:hover{
			background:#F7941D !important;
			color:white !important;
		}
		.form-select .nice-select::after {
			top: 14px;
		}
	</style>
@endpush
@push('scripts')
	<script src="{{asset('frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
	<script src="{{ asset('frontend/js/select2/js/select2.min.js') }}"></script>
	<script>
		$(document).ready(function() { $("select.select2").select2(); });
  		$('select.nice-select').niceSelect();
	</script>
	<script>
		function showMe(box){
			var checkbox=document.getElementById('shipping').style.display;
			// alert(checkbox);
			var vis= 'none';
			if(checkbox=="none"){
				vis='block';
			}
			if(checkbox=="block"){
				vis="none";
			}
			document.getElementById(box).style.display=vis;
		}
	</script>
	<script>
		$(document).ready(function(){
			$('.shipping select[name=shipping]').change(function(){
				let cost = parseFloat( $(this).find('option:selected').data('price') ) || 0;
				let subtotal = parseFloat( $('.order_subtotal').data('price') );
				let coupon = parseFloat( $('.coupon_price').data('price') ) || 0;
				// alert(coupon);
				$('#order_total_price span').text('$'+(subtotal + cost-coupon).toFixed(2));
			});

		});

	</script>

@endpush
