<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Product;
class User
{

    public function handle($request, Closure $next)
    {

        if(empty(session('user'))){
            // $product = Product::where('slug', $request->slug)->first();
            $product_detail= Product::getProductBySlug($request->slug);
            return redirect()->route('quick.checkout', compact('product_detail'));
            // return redirect()->route('login.form');
        }
        else{
            return $next($request);
        }
    }
}
