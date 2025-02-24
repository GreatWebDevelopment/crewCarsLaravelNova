<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $items = Page::where('status', 1)->select('title', 'description')->get();

        if ($items->isEmpty()) {
            return response()->json(['pagelist'=> $items, 'ResponseCode' => '200', 'Result' => 'false',
                'ResponseMsg' => 'Page Not Founded!'], 200);
        } else {
            return response()->json(['pagelist'=> $items, 'ResponseCode' => '200', 'Result' => 'true',
                'ResponseMsg' => 'Page List Founded!'], 200);
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
