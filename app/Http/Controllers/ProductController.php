<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // -- GET /api/products
    public function getProducts() {
        return "get list products";
    }

    // -- POST /api/products
    public function createProduct(Request $request) {
        $product = new Product();
        $product->name = $request->get('name');
        $product->pricing = $request->get('pricing');
        $product->discount = $request->get('discount');
        $product->color = $request->get('color');
        $product->size = $request->get('size');
        $product->brand = $request->get('brand');
        $product->category_id = $request->get('category_id');

        $product->save();

        return [ "message" => "success", "data" => $product ];
    }

    // -- PATCH /api/products/{productId}
    public function updateProduct() {
        return "update 1 product";
    }

    // -- GET /api/products/{productId}
    public function getProduct() {
        return "get 1 product";
    }

    // -- DELETE /api/products/{productId}
    public function deleteProduct() {
        return "delete 1 product";
    }
}