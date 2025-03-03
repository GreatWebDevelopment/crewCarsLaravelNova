<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $userId = Auth::user()->id;
        $carId = $request->input('car_id');
        $galleries = Gallery::with(['car' => function ($query) {
            $query->without('bookings')
                ->select(['id', 'title']);
        }])
            ->where('carId', $carId)
            ->where('uid', $userId)
            ->select(['id', 'img', 'carId'])
            ->get();

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Gallery Get Successfully!', 'gallerylist' => $galleries]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $userId = Auth::user()->id;
        $carId = $request->input('car_id');

        $galleries = Gallery::where('carId', $carId)->where('uid', $userId)->get();
        if (count($galleries) > 0) {
            return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Gallery Already Added!']);
        }

        if ($request->hasFile('images')) {
            $images = $this->uploadFile($request->file('images'));
        }

        Gallery::create([
            'carId' => $carId,
            'uid' => $userId,
            'img' => $images,
        ]);

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Gallery Add Successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['ResponseCode' => '401', 'Result' => 'false', 'ResponseMsg' => 'Something Went Wrong!'], 401);
        }

        $carId = $request->input('car_id');
        $existingImages = json_decode($request->input('imlist'));

        if ($request->hasFile('images')) {
            $images = $this->uploadFile($request->file('images'));
        }

        $gallery = Gallery::find($id);
        $imagesToDelete = array_diff($gallery->img, $existingImages);

        if (count($imagesToDelete) > 0) {
            foreach ($imagesToDelete as $image) {
                Storage::disk('s3')->delete($image);
            }
        }

        $gallery->carId = $carId;
        $gallery->img = array_merge($existingImages, $images);
        $gallery->save();

        return response()->json(['ResponseCode' => '200', 'Result' => 'true', 'ResponseMsg' => 'Car Gallery Update Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    private function uploadFile($files)
    {
        $images = [];
        foreach ($files as $file) {
            $filename = uniqid() . time() . mt_rand() . '.' . $file->getClientOriginalExtension();
            $path = env('GALLERY_S3_PATH') . $filename;
            $s3 = Storage::disk('s3')->put($path, file_get_contents($file), 'public');
            if ($s3) {
                $images[] = $path;
            }
        }

        return $images;
    }
}
