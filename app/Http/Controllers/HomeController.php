<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Home;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $homes = Home::with('community')->paginate(2);
        return response()->json($homes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'community_id' => 'required|exists:communities,id',
        'address'      => 'required|string|max:255',
        'price'        => 'required|integer|min:0',
        'bedrooms'     => 'required|integer|min:0',
    ]);

    $home = Home::create($data);

    return response()->json($home, 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
