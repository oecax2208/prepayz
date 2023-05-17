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
{{-- Author RDR --}}
<section class="shop checkout section">
    <div class="container">
        <div class="primary">
            <div class="col-md-12">
                <center><h4>{!!$result_msg!!}</h4></center><br>
                <div style="border: 1px solid black; padding: 10px;margin-bottom:0px">
                    <left><h6>{!! $result !!}</h6></left>
                </div>
                <center><img style="width:50%" src="{{ asset('frontend/img/payment-success.png')}}"></center>
            </div>
        </div>
    </div>
</section>

@endsection
