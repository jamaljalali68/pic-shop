<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
      
        $products = null;

        if (isset($request->filter) && !isset($request->filter2)) {

            $products = $this->findFilter($request?->filter) ?? Product::all();
        } else if (!isset($request->filter) && isset($request->filter2)) {

            $products = $this->findFilter($request?->filter2) ?? Product::all();
        } else if (isset($request->filter) && isset($request->filter2)) {

            $products = $this->findMultiFilter($request?->filter, $request?->filter2) ?? Product::all();
        } else if ($request->has('search')) {

            $products = Product::where('title', 'LIKE', '%' . $request->input('search') . '%')->get();
        } else {

            $products = Product::all();
        }


        $categories = Category::all();

        return view('frontend.products.all', compact('products', 'categories'));
    }

    public function show($product_id)
    {
        $product = Product::findOrFail($product_id);

        $simillerProducts = Product::where('category_id', $product->category_id)->take(4)->get();

        return view('frontend.products.show', compact('product', 'simillerProducts'));
    }


    private function findFilter(string $MethodName)
    {

        try {

            return Product::$MethodName()->get();
        } catch (\Throwable $th) {

            return Product::all();
        }
    }

    private function findMultiFilter(string $firstMethodName, string $secondMethodName)
    {

        try {

            return Product::$firstMethodName()->$secondMethodName()->get();
        } catch (\Throwable $th) {

            return Product::all();
        }
    }
}
