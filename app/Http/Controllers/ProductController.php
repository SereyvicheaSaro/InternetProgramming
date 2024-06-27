<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\FileUploadController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function create(Request $req)
    {
        try {
            $validator = $req->validate([
                'name'          => 'required|string',
                'code'          => 'required|string',
                'category_id'   => 'required|integer|exists:categories,id',
                'unit_price'    => 'required|numeric|min:0|max:999999.99',
                'quantity'      => 'required|integer',
                'image'         => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'promotion'     => 'required|integer',
                'alert'         => 'required|integer',
            ]);

            // Handle image upload
            $image = FileUploadController::storeImage($req->file('image'), 'uploads/products');
            $validator['image'] = $image;

            // Create product
            $product = Product::create($validator);

            return response()->json($product, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    public function get(){
        $produtcs = Product::all();
        return response()->json($produtcs, Response::HTTP_FOUND);
    }

    public function getById($id){
        $produtcs = Product::find($id);
        return response()->json($produtcs, Response::HTTP_FOUND);
    }

    public function update(Request $req, $id){
        try{
            $validator = $req->validate ([
                'name'          => 'required|string',
                'code'          => 'required|string',
                'category_id'   => 'required|integer|exists:categories,id',
                'unit_price'    => 'required|numeric|min:0|max:999999.99',
                'quantity'      => 'required|integer', 
                'image'         => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'promotion'     => 'required|integer',
                'alert'         => 'required|integer',
            ]);
            $image = FileUploadController::storeImage($req->file('image'), 'uploads/products');
            $validator['image'] = $image;

            $updateProduct = Product::find($id);
            if(!$updateProduct){
                return response()->json(['message'=>'Product not found'], Response::HTTP_NOT_FOUND);
            }
            
            $updateProduct->update($validator);
            return response()->json($updateProduct, Response::HTTP_CREATED);
            
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    public function delete($id){
        try{
            $deleteProduct = Product::find($id);
            if(!$deleteProduct){
                return response()->json(['message'=>'Product not found'], Response::HTTP_NOT_FOUND);
            }
            $deleteProduct->delete();
            return response()->json(['message'=>'Product deleted successfull.'], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }   
    }

    protected function handleValidationException(ValidationException $e){
        return response()->json([
            'message'   => 'Validation Error',
            'errors'    => $e->errors()],
            Response::HTTP_BAD_REQUEST
        );
    }
    
    protected function handleUnexpectedException(\Exception $e){
        Log::error('Unexpected error occurred', ['exception' => $e]);

        return response()->json([
            'error' => 'An unexpected error occurred.'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
