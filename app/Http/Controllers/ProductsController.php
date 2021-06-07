<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Throwable;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductResource::collection(Products::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //['productType', 'productName', 'productDescription', 'productPrice']

        $validator = Validator::make($request->all(), [
            'productType' => ['required', 'max:2'],
            'productName' => ['required', 'max:36', 'string'],
            'productDescription' => ['required', 'max:224'],
            'productPrice' => ['required', 'numeric'],
            'productImage' => ['required', 'image', 'max:512'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $path = $request->file('productImage')->store('images');

            $product = Products::create([
                'productType' => $request->productType,
                'productName' => $request->productName,
                'productDescription' => $request->productDescription,
                'productPrice' => $request->productPrice,
                'productImage' => $path,
                'user_id' => $request->user()->id
            ]);

            $response = [
                'status' => 201,
                'message' => 'Product Created',
                'data' => $product
            ];

            return response()->json($response, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            // dd($e);
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage()()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function show(Products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $products = Products::findOrFail($id);


        $validator = Validator::make($request->all(), [
            'productType' => ['required', 'max:2'],
            'productName' => ['required', 'max:36', 'string'],
            'productDescription' => ['required', 'max:224'],
            'productPrice' => ['required', 'numeric'],
            'productImage' => ['image', 'max:512'],

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            if ($request->has('productImage')) {

                $path = $request->file('productImage')->store('images');

                Storage::delete($products->productImage);

                $products->update([
                    'productImage' => $path,
                ]);
            }

            $products->update([
                'productType' => $request->productType,
                'productName' => $request->productName,
                'productDescription' => $request->productDescription,
                'productPrice' => $request->productPrice,
                'user_id' => $request->user()->id
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Products Updated',
                'data' => $products
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => end($e->errorInfo)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = Products::find($id);

            if (!$product) {
                $response = [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data not found',
                ];
            } else {
                Storage::delete($product->productImage);

                $product->delete();

                $response = [
                    'status' => Response::HTTP_OK,
                    'message' => 'Products deleted',
                ];
            }
            return response()->json($response, Response::HTTP_OK);
        } catch (QueryException $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => end($e->errorInfo)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
