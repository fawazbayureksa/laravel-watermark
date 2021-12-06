<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Image;
use Str;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(){
        $products = Product::OrderBy('created_at','DESC')->paginate(10);
        return view('product',compact('products'));

    }
}
