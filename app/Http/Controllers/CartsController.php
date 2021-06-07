<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Products;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Darryldecode\Cart\Cart;
use Symfony\Component\HttpFoundation\Response;

class CartsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $user = User::findOrFail(Auth::user()->id);
            $product = Products::findOrFail($request->input('products'));


            \Cart::session($user->id)->add([
                'id' => $product->id,
                'name' =>  $product->productName,
                'price' =>  $product->productPrice,
                'quantity' => 1,
                'attributes' => [
                    'mood' => $request->input('mood'),
                    'size' => $request->input('size'),
                    'sugar' => $request->input('sugar'),
                ],
                'associatedModel' => new ProductResource($product)
            ]);

            return response()->json([
                'status' => 'success',
                'data' => \Cart::getContent()
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'Not OK',
                'message' => $th->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = User::findOrFail(Auth::user()->id);

        return response()->json([
            'items' => \Cart::session($user->id)->getContent(),
        ], Response::HTTP_OK);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        try {
            \Cart::session($user->id)->remove($request->input('id'));

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Removed'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTotal()
    {
        $user = User::findOrFail(Auth::user()->id);

        return response()->json([
            'total' => \Cart::session($user->id)->getTotal(),
        ], Response::HTTP_OK);
    }
}
