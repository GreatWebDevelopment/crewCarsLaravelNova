<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'UserController is working']);
    }

    public function register(Request $request)
    {
        if (!checkRequestParams($request, ['name', 'email', 'mobile', 'password', 'ccode'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $name = strip_tags($request->input('name'));
        $email = strip_tags($request->input('email'));
        $mobile = strip_tags($request->input('mobile'));
        $password = strip_tags($request->input('password'));
        $countryCode = strip_tags($request->input('ccode'));
        $referralCode = strip_tags($request->input('rcode'));

        $users = User::where('mobile', $mobile)
            ->orWhere('email', $email)
            ->get();

        if (count($users) > 0) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Mobile Number or Email Address Already Used!'], 401);
        }

        if (!empty($referralCode)) {
            $referralCodes = User::where('referralCode', $referralCode)->get();

            if (count($referralCodes) > 0) {
                $nonce = $this->generateNonce();
                $timestamps = date('Y-m-d H:i:s');

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'mobile' => $mobile,
                    'password' => Hash::make($password),
                    'countryCode' => $countryCode,
                    'referralCode' => $referralCode,
                    'verificationCode' => $nonce,
                    'walletBalance' => app('set')['scredit'],
                    'registeredAt' => $timestamps,
                ]);

                WalletReport::create([
                    'uid' => $user->id,
                    'message' => 'Sign up Credit Added',
                    'status' => 'Credit',
                    'amt' => app('set')['scredit'],
                    'tdate' => $timestamps,
                ]);

                return response()->json(['UserLogin' => $user, 'currency' => app('set')['currency'], 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Sign Up Done Successfully!'], 200);
            } else {
                return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Refer Code Not Found Please Try Again!!'], 401);
            }
        } else {
            $nonce = $this->generateNonce();
            $timestamps = date('Y-m-d H:i:s');

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => Hash::make($password),
                'countryCode' => $countryCode,
                'verificationCode' => $nonce,
                'registeredAt' => $timestamps,
            ]);

            return response()->json(['UserLogin' => $user, 'currency' => app('set')['currency'], 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Sign Up Done Successfully!'], 200);
        }
    }

    public function login(Request $request)
    {
        if (!checkRequestParams($request, ['mobile', 'password', 'ccode'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $mobile = strip_tags($request->input('mobile'));
        $password = strip_tags($request->input('password'));
        $countryCode = strip_tags($request->input('ccode'));

        $user = User::where('countryCode', $countryCode)
            ->where(function ($query) use ($mobile) {
                $query->where('mobile', $mobile)
                    ->orWhere('email', $mobile);
            })
            ->where('role', 'user')
            ->first();

        if (!empty($user) && Hash::check($password, $user->password)) {
            if ($user->status === 1) {
                $token = $user->createToken('crewcars')->plainTextToken;

                return response()->json(['UserLogin' => $user, 'Token' => $token, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Login successfully!', 'type' => 'USER'], 200);
            } else {
                return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Your profile has been blocked by the administrator, preventing you from using our app as a regular user.'], 401);
            }
        }

        $admin = User::where('mobile', $mobile)
            ->where('role', 'admin')
            ->first();

        if (!empty($admin) && Hash::check($password, $admin->password)) {
            $token = $admin->createToken('crewcars')->plainTextToken;

            return response()->json(['AdminLogin' => $admin, 'Token' => $token, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Login successfully!', 'type' => 'ADMIN'], 200);
        } else {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Account Not Found!!!'], 401);
        }
    }

    public function forgotPassword(Request $request)
    {
        if (!checkRequestParams($request, ['email', 'password', 'ccode'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went wrong  try again !'], 401);
        }

        $mobile = strip_tags($request->input('email'));
        $password = strip_tags($request->input('password'));
        $countryCode = strip_tags($request->input('ccode'));

        $user = User::where('mobile', $mobile)
            ->where('countryCode', $countryCode)
            ->first();

        if (!empty($user)) {
            $user->password = Hash::make($password);
            $user->save();

            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Password Changed Successfully!!!!!'], 200);
        } else {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Mobile Not Matched!!!!'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Logged Out'], 200);
    }

    public function editProfile(Request $request, $id)
    {
        if (!checkRequestParams($request, ['name', 'email', 'uid'])) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $name = strip_tags($request->input('name'));
        $email = strip_tags($request->input('email'));
        $password = strip_tags($request->input('password'));

        $user = User::find($id);
        if (!empty($user)) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'User Not Exist!!!!'], 401);
        }

        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        return response()->json(['UserLogin' => $user, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Profile Update successfully!'], 200);
    }

    private function generateNonce()
    {
        $nonce = mt_rand(100000, 999999);
        $referralCodes = User::where('referralCode', $nonce)->get();
        if (count($referralCodes) > 0) {
            $this->generateNonce();
        } else {
            return $nonce;
        }
    }

}