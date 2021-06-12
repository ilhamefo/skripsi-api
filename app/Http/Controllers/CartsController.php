<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Invoice;
use App\Models\Products;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Darryldecode\Cart\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CartsController extends Controller
{
    /**
     * Display an authenticated user.
     *
     * @return App\Models\User;
     */
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = User::findOrFail(Auth::user()->id);
            return $next($request);
        });
    }
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
            $product = Products::findOrFail($request->input('products'));

            $condition = new \Darryldecode\Cart\CartCondition(array(
                'name' => 'Tax 10%',
                'type' => 'tax',
                'target' => 'total',
                'value' => '10%',
                'attributes' => ['description' => 'Add tax to item',]

            ));

            \Cart::session($this->user->id)->condition($condition);

            \Cart::session($this->user->id)->add([
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
        return response()->json([
            'items' => \Cart::session($this->user->id)->getContent(),
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
        try {
            \Cart::session($this->user->id)->remove($request->input('id'));

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
        return response()->json([
            'total' => \Cart::session($this->user->id)->getTotal(),
        ], Response::HTTP_OK);
    }
    public function getSubTotal()
    {
        return response()->json([
            'subTotal' => \Cart::session($this->user->id)->getSubTotal(),
        ], Response::HTTP_OK);
    }

    public function order(Request $request)
    {
        $validator = Validator::make($request->only(['payment_method']), [
            'payment_method' => ['required', 'string', 'max:12']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            DB::transaction(function () use ($request) {

                $carts = \Cart::session($this->user->id)->getContent();

                $invoice_id = 'INV' . $this->user->id . 'U' .  rand(1, 9999) . 'R';

                foreach ($carts as $key => $value) {
                    Sales::create([
                        'product_id' => $value->id,
                        'invoice_id' => $invoice_id,
                        'quantity' => $value->quantity,
                        'subtotal' => $value->getPriceSum(),
                        'product_price' => $value->price,
                    ]);
                }

                Invoice::create([
                    'payment_type' => $request->payment_method,
                    'total_amount' => \Cart::session($this->user->id)->getTotal(),
                    'user_id' => $this->user->id
                ]);
            });

            // clearing carts once transaction done
            \Cart::session($this->user->id)->clear();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Created!'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
