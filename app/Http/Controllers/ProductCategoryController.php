<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductCategoryResource::collection(ProductCategory::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'categoryName' => ['required', 'max:36', 'string'],
            'categoryIcon' => ['required', 'max:400', 'image'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $path = $request->file('categoryIcon')->store('images/icon');
            // return Auth::user()->id;
            $category = ProductCategory::create([
                'user_id' => Auth::user()->id,
                'categoryName' => $request->categoryName,
                'categoryIcon' => $path,
            ]);

            $response = [
                'status' => Response::HTTP_CREATED,
                'message' => 'Category Created',
                'data' => $category
            ];

            return response()->json($response, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $cat = ProductCategory::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'categoryName' => ['required', 'max:36', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            if ($request->has('categoryIcon')) {

                $path = $request->file('categoryIcon')->store('images/icon');

                Storage::delete($cat->categoryIcon);

                $cat->update([
                    'categoryIcon' =>  $path,
                ]);
            }

            $cat->update([
                'categoryName' => $request->categoryName
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Category Updated',
                'data' => $cat
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $cat = ProductCategory::findOrFail($request->id);

        try {
            $cat->delete();

            Storage::delete($cat->categoryIcon);

            return response([
                'status' => Response::HTTP_OK,
                'message' => 'Deleted'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
