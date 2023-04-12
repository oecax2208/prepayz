@extends('frontend.layouts.master')

@section('title','Payment Successfully')

@section('main-content')

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="bread-inner">
                    <ul class="bread-list">
                        <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                        <li class="active"><a href="javascript:void(0)">Pay</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Breadcrumbs -->

<section class="shop checkout section">
    <div class="container">
        <div class="primary">
            <div class="col-md-12">
                <h3><center>{!! $result !!}</center></h3>
                <center><img src="{{ asset('frontend/img/payment-success.png"></center>
            </div>
        </div>
    </div>
</section>

@endsection
