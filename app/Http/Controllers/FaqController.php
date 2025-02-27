<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Faq::where('status', 1)->get();

        if ($items->isEmpty()) {
            return response()->json(['FaqData'=> $items, 'ResponseCode' => '200', 'Result' => 'false',
                'ResponseMsg' => 'FAQ Not Founded!'], 200);
        } else {
            return response()->json(['FaqData'=> $items, 'ResponseCode' => '200', 'Result' => 'true',
                'ResponseMsg' => 'FAQ List Get Successfully!'], 200);
        }
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
