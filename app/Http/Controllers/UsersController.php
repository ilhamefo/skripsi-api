<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Throwable;

class UsersController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user =  User::findOrFail(Auth::user()->id);

        $validator = Validator::make($request->only(['name', 'locations']), [
            'name' => ['required', 'string', 'max:128'],
            'locations' => ['required', 'string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user->update([
                'name' => $request->input('name'),
                'locations' => $request->input('locations'),
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Updated!',
                'data' => $user
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => end($e->errorInfo)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateAvatar(Request $request)
    {

        $user =  User::findOrFail(Auth::user()->id);

        $validator = Validator::make($request->only(['avatar']), [
            'avatar' => ['required', 'image', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            //delete users avatar if any
            if (!is_null($user->avatar)) {
                Storage::delete($user->avatar);
            }

            $path = $request->file('avatar')->store('images/profile_picture/' . $user->name);

            $user->update([
                'avatar' => $path,
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Updated!',
                'data' => $user
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => end($e->errorInfo)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteAvatar()
    {
        $user =  User::findOrFail(Auth::user()->id);

        try {
            if ($user->avatar == NULL) {
                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => "You don't have any Profile Picture"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Storage::delete($user->avatar);

            $user->update([
                'avatar' => NULL,
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Deleted!',
                'data' => $user
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function verify(EmailVerificationRequest $request)
    {
        try {
            $request->fulfill();

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Email Verified!',
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateEmail(Request $request)
    {
        $user =  User::findOrFail(Auth::user()->id);

        Validator::make($request->only(['email']), [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if (
            $request->email !== $user->email &&
            $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, $request);
            $response = [
                'status' => Response::HTTP_OK,
                'message' => "We've sent you email confirmation! \n Please check your inbox",
            ];

            return response()->json($response, Response::HTTP_OK);
        } else {
            $user->forceFill([
                'email' => $request->email
            ])->save();

            $response = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => "Already Your Email!",
            ];

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    protected function updateVerifiedUser($user, $request)
    {
        $user->forceFill([
            'email' => $request->email,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->only(['old_password', 'new_password']), [
            'old_password' => [
                'required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail('Old Password didn\'t match');
                    }
                },
            ],
            'new_password' => [
                'required',
                'different:password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user =  User::findOrFail(Auth::user()->id);

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            $response = [
                'status' => Response::HTTP_OK,
                'message' => 'Updated!',
                'data' => $user,
                'test' => $request->new_password
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
