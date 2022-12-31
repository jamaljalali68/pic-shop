<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Utilities\ImageUploader;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\Products\StoreRequest;
use App\Http\Requests\Admin\Products\UpdateRequest;

class AdminProductsController extends Controller
{
    public function all()
    {
        $products = Product::paginate(10);

        return view('admin.products.all', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.products.add', compact('categories'));
    }

    public function store(StoreRequest $request)
    {
        $validatedData = $request->validated();
       
        $admin = User::first();

        
        $createdProduct = Product::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'category_id' => $validatedData['category_id'],
                'price' => $validatedData['price'],
                'owner_id' => $admin->id,
            ]);


        if(!$this->uploadImages($createdProduct, $validatedData))
        {
            return back()->with('failed', 'محصول ایجاد نشد');
        }

        return back()->with('success', 'محصول ایجاد شد');

    }

    public function edit($product_id)
    {
        $categories = Category::all();

        $product = Product::findOrFail($product_id);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateRequest $request, $product_id)
    {
        $validatedData = $request->validated();

        $product = Product::findOrFail($product_id);

        $updatedProduct = $product->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'category_id' => $validatedData['category_id'],
            'price' => $validatedData['price'],
        ]);


        if(!$this->uploadImages($product, $validatedData) or !$updatedProduct)
        {
            return back()->with('failed', 'تصاویر بروزرسانی نشد');
        }

        return back()->with('success', 'محصول بروزرسانی شد');
    }

    public function delete($product_id)
    {
        $product = Product::findOrFail($product_id);

        $product->delete();

        return back()->with('success', 'محصول حذف شد');
    }

    public function downloadDemo($product_id)
    {
        $product = Product::findOrFail($product_id);

        return response()->download(storage_path('app/public/products/'. $product_id .'/'. $product->demo_url));
    }

    public function downloadSource($product_id)
    {
        $product = Product::findOrFail($product_id);

        return response()->download(storage_path('app/products/'. $product_id .'/'. $product->source_url));
    }

    private function uploadImages($createdProduct, $validatedData)
    {
        try{
            $basePath = 'products/' . $createdProduct->id . '/';

            $sourceImageFullPath = null;

            $data = [];

            if(isset($validatedData['source_url']))
            {
                $sourceImageFullPath = $basePath . $validatedData['source_url']->getClientOriginalName();

                Storage::disk('local')->put($sourceImageFullPath, File::get($validatedData['source_url']));

                $data['source_url'] =  $validatedData['source_url']->getClientOriginalName();
            }


            if(isset($validatedData['thumbnail_url']))
            {
                $fullPath = $basePath . $validatedData['thumbnail_url']->getClientOriginalName();

                Storage::disk('public')->put($fullPath, File::get($validatedData['thumbnail_url']));

                $data['thumbnail_url'] = $validatedData['thumbnail_url']->getClientOriginalName();
            }

            if(isset($validatedData['demo_url']))
            {
                $fullPath = $basePath . $validatedData['demo_url']->getClientOriginalName();

                Storage::disk('public')->put($fullPath, File::get($validatedData['demo_url']));

                $data['demo_url'] = $validatedData['demo_url']->getClientOriginalName();
            }   
        
    
            $updatedProduct = $createdProduct->update($data);

            if(!$updatedProduct){
                throw new \Exception('تصاویر آپلود نشدند');
            }

            return true;

        }catch(\Exception $e){
            return false;
        }
    }
}
