<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:1000',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric',
        ]);

        if(isset($request['image'])) {
            $relativePath = $this->saveImage($request['image']);
            $request['image'] = $relativePath;
        }

        $product = Product::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'image' => $request['image'],
            'description' => $data['description'],
        ]);
        if($product){
            return response()->json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        // return ProductResource::collection($product);
        return response()->json([
            'data' => $product,
            'image' => $product->image ? URL::to($product->image) : null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id)->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);
        if($product){
            return response()->json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id)->delete();
        return response()->json(['success' => true]);
    }

    private function saveImage($image) {
        if(preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            $image = substr($image,strpos($image,',') +1);
            $type = strtolower($type[1]);
            if(!in_array($type,['jpg','jpeg','gif','png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        }else {
            throw new \Exception('did not match data URI with image data');
        }
        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $image);

        return $relativePath;
    }
}
