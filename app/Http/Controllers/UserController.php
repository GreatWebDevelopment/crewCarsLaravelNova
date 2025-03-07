<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'UserController is working']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required',
            'password' => 'required',
            'ccode' => 'required',
            'driverLicense' => 'required|file|mimes:png,jpg,jpeg|max:10240',
            'insurance' => 'required|file|mimes:png,jpg,jpeg|max:10240',
            'pilotCertificate' => 'required|file|mimes:png,jpg,jpeg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
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
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Mobile Number or Email Address Already Used!'], 400);
        }

        if (!empty($referralCode)) {
            $referralCodes = User::where('referralCode', $referralCode)->get();

            if (count($referralCodes) > 0) {
                $nonce = $this->generateNonce();
                $timestamps = date('Y-m-d H:i:s');

                $driverLicenseUrl = uploadfile($request->file('driverLicense'), env('DOCUMENT_S3_PATH') . 'driver-license/');
                $pilotCertificate = uploadfile($request->file('pilotCertificate'), env('DOCUMENT_S3_PATH') . 'pilot-certificate/');
                $insurance = uploadfile($request->file('insurance'), env('DOCUMENT_S3_PATH') . 'insurance/');

                if (empty($driverLicenseUrl) || empty($pilotCertificate) || empty($insurance)) {
                    return response()->json(['ResponseCode' => '500', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 500);
                }

                $documents = $this->extractDataFromDocuments([
                   ['type' => 'DL', 's3Key' => $driverLicenseUrl],
                   ['type' => 'Insurance', 's3Key' => $insurance],
                   ['type' => 'Certificate', 's3Key' => $pilotCertificate],
                ]);

                if (empty($documents)) {
                    return response()->json(['ResponseCode' => '500', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 500);
                }

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'mobile' => $mobile,
                    'password' => Hash::make($password),
                    'countryCode' => $countryCode,
                    'referralCode' => $referralCode,
                    'verificationCode' => $nonce,
                    'walletBalance' => app('set')->scredit,
                    'registeredAt' => $timestamps,
                ]);

                foreach ($documents as $document) {
                    $user->documents()->create($document);
                }

                WalletReport::create([
                    'uid' => $user->id,
                    'message' => 'Sign up Credit Added',
                    'status' => 'Credit',
                    'amt' => app('set')->scredit,
                    'tdate' => $timestamps,
                ]);

                return response()->json(['UserLogin' => $user, 'currency' => app('set')->currency, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Sign Up Done Successfully!']);
            } else {
                return response()->json(['ResponseCode' => '404', 'Result' => 'false', 'ResponseMsg' => 'Refer Code Not Found Please Try Again!'], 404);
            }
        } else {
            $nonce = $this->generateNonce();
            $timestamps = date('Y-m-d H:i:s');

            $driverLicenseUrl = uploadfile($request->file('driverLicense'), env('DOCUMENT_S3_PATH') . 'driver-license/');
            $pilotCertificate = uploadfile($request->file('pilotCertificate'), env('DOCUMENT_S3_PATH') . 'pilot-certificate/');
            $insurance = uploadfile($request->file('insurance'), env('DOCUMENT_S3_PATH') . 'insurance/');

            if (empty($driverLicenseUrl) || empty($pilotCertificate) || empty($insurance)) {
                return response()->json(['ResponseCode' => '500', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 500);
            }

            $documents = $this->extractDataFromDocuments([
                ['type' => 'DL', 's3Key' => $driverLicenseUrl],
                ['type' => 'Insurance', 's3Key' => $insurance],
                ['type' => 'Certificate', 's3Key' => $pilotCertificate],
            ]);

            if (empty($documents)) {
                return response()->json(['ResponseCode' => '500', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 500);
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => Hash::make($password),
                'countryCode' => $countryCode,
                'verificationCode' => $nonce,
                'registeredAt' => $timestamps,
            ]);

            foreach ($documents as $document) {
                $user->documents()->create($document);
            }

            return response()->json(['UserLogin' => $user, 'currency' => app('set')->currency, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Sign Up Done Successfully!']);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'password' => 'required',
            'ccode' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
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
                $token = $user->createToken(env('APP_NAME'))->plainTextToken;
                $expiresAt = now()->addHours(2);

                return response()->json(['UserLogin' => $user, 'Token' => $token, 'ExpiresAt' => $expiresAt->toDateTimeString(), 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Login successfully!', 'type' => 'USER']);
            } else {
                return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Your profile has been blocked by the administrator, preventing you from using our app as a regular user.'], 401);
            }
        }

        $admin = User::where('mobile', $mobile)
            ->where('role', 'admin')
            ->first();

        if (!empty($admin) && Hash::check($password, $admin->password)) {
            $token = $admin->createToken(env('APP_NAME'))->plainTextToken;
            $expiresAt = now()->addHours(2);

            return response()->json(['AdminLogin' => $admin, 'Token' => $token, 'ExpiresAt' => $expiresAt->toDateTimeString(), 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Login successfully!', 'type' => 'ADMIN']);
        } else {
            return response()->json(['ResponseCode' => '404', 'Result' => 'false', 'ResponseMsg' => 'Account Not Found!!!'], 404);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'password' => 'required',
            'ccode' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went wrong!'], 400);
        }

        $mobile = strip_tags($request->input('mobile'));
        $password = strip_tags($request->input('password'));
        $countryCode = strip_tags($request->input('ccode'));

        $user = User::where('mobile', $mobile)
            ->where('countryCode', $countryCode)
            ->first();

        if (!empty($user)) {
            $user->password = Hash::make($password);
            $user->save();

            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Password Changed Successfully!']);
        } else {
            return response()->json(['ResponseCode' => '404', 'Result' => 'false', 'ResponseMsg' => 'Mobile Not Matched!'], 404);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Logged Out']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $name = strip_tags($request->input('name'));
        $email = strip_tags($request->input('email'));
        $password = strip_tags($request->input('password'));

        $user = User::find($id);
        if (empty($user)) {
            return response()->json(['ResponseCode' => '404', 'Result' => 'false', 'ResponseMsg' => 'User Not Exist!'], 404);
        }

        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        return response()->json(['UserLogin' => $user, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Profile Update successfully!']);
    }

    public function uploadPicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'image' => 'required|file|mimes:jpeg,png,jpg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '400', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 400);
        }

        $userId = Auth::user()->id;
        $image = $request->file('image');

        $url = uploadfile($image, env('PHOTO_S3_PATH'));
        if (empty($url)) {
            return response()->json(['ResponseCode' => '500', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 500);
        }

        $user = User::find($userId);
        $user->profilePicture = $url;
        $user->save();

        return response()->json(['UserLogin' => $user, 'ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Profile Image Upload Successfully!']);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            return response()->json(['ResponseCode' => '404', 'Result' => 'false', 'ResponseMsg' => 'User Not Exist!'], 404);
        }

        $user->status = 0;
        $user->save();

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Account Delete Successfully!']);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        $token = $user->createToken(env('APP_NAME'))->plainTextToken;

        $expiresAt = now()->addHours(2);

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'Token' => $token, 'ExpiresAt' => $expiresAt]);
    }

    public function referData()
    {
        $user = User::find(Auth::user()->id);

        if (empty($user)) {
            return response()->json(['ResponseCode' => '404', 'Result' => 'false', 'ResponseMsg' => 'Not Exist User!'], 404);
        }

        return response()->json([
            'ResponseCode' => '200',
            'Result' => 'true',
            'ResponseMsg' => 'Wallet Balance Get Successfully!',
            'code' => $user->verificationCode,
            'signupcredit' => app('set')->scredit,
            'refercredit' => app('set')->rcredit,
        ]);
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