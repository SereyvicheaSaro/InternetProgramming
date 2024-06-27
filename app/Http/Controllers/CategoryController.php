<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function create(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'name'          => ['required', 'string', Rule::unique('categories')],
                'description'   => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = $validator->validated();
            $category = Category::create($data);

            return response()->json($category, Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    public function get(){
        $categories = Category::all();
        return response()->json($categories, Response::HTTP_OK);
    }

    public function getAllProductsInCategory($id){
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found.'], Response::HTTP_NOT_FOUND);
            }
            $products = $category->products;
            return response()->json($products, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
        
    }

    public function getById($id){
        $categories = Category::find($id);
        if(!$categories){
            return response()->json(['message'=> 'Category not found.'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($categories, Response::HTTP_OK);
    }

    public function update(Request $req, $id){
        try{
            $validator = $req->validate([
                'name'          => ['required', 'string', Rule::unique('categories')],
                'description'   => 'nullable|string'
            ]);

            $updateCategory = Category::find($id);

            if(!$updateCategory){
                return response()->json(['message'=> 'Category not found.'], Response::HTTP_NOT_FOUND);
            }
            
            $updateCategory->update($validator);
            return response()->json($updateCategory, Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    public function delete($id){
        try {
            $deleteCategory = Category::find($id);

            if(!$deleteCategory){
                return response()->json(['message'=> 'Category not found.'], Response::HTTP_NOT_FOUND);
            }

            $deleteCategory->delete();
            return response()->json(['message'=> 'Category deleted successfull.'], Response::HTTP_NO_CONTENT);

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
        return response()->json([
            'error' => 'An unexpected error occurred.'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
