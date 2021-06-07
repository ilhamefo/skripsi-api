<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return  UserResource::collection(User::where('roles', '2')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->password . ' ' . $request->password_confirmation;
        $validator = Validator::make($request->only(['name', 'email', 'password', 'avatar', 'locations', 'password_confirmation']), [
            'name' => ['required', 'string', 'max:128'],
            'email' => ['required', 'email', 'max:128'],
            'password' => [
                'required',
                'confirmed',
                Password::min(6)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'avatar' => ['image', 'max:500'],
            'locations' => ['string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $employee = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'avatar' => $request->avatar,
                'locations' => $request->locations,
                'roles' => 2,
                'password' => Hash::make($request->password),
            ]);

            if ($request->has('avatar')) {
                $path = $request->file('avatar')->store('images/profile_picture/' . $employee->name);

                $employee->update([
                    'avatar' => $path
                ]);
            }

            $response = [
                'status' => Response::HTTP_OK,
                'messages' => 'Employee Created!',
                'data' => $employee
            ];

            event(new Registered($employee));

            return response()->json($response, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'messages' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // return $request->locations;
        $employee = User::findOrFail($request->id);

        $validator = Validator::make($request->only(['name', 'email', 'password', 'avatar', 'locations', 'password_confirmation']), [
            'name' => ['required', 'string', 'max:128'],
            'email' => ['required', 'email', 'max:128'],
            'password' => [
                'confirmed',
                Password::min(6)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'avatar' => ['image', 'max:500'],
            'locations' => ['string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            // updating employee avatar if user has input key 'avatar'
            if ($request->has('avatar')) {
                Storage::delete($employee->avatar);

                $employee->update([
                    'avatar' => $request->file('avatar')->store('images/profile_picture/' . $employee->name),
                ]);
            }

            // updating employee password if user has input keyed 'password'
            if ($request->has('password')) {
                $employee->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'locations' => $request->locations,
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'messages' => 'Success updating ' . $employee->name
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'messages' => 'failed to update, because : ' . $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);

            $user->avatar !== null ? Storage::delete($user->avatar) : '';

            $user->delete();

            // delete related products, if have any
            $user->products()->each(function ($product) {
                Storage::delete($product->productImage);
                $product->delete();
            });

            // delete related category products, if have any
            $user->productsCategory()->each(function ($productsCategory) {
                Storage::delete($productsCategory->categoryIcon);
                $productsCategory->delete();
            });

            $response = [
                'status' => Response::HTTP_OK,
                'messages' => 'Deleted!'
            ];

            return response($response, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'messages' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
