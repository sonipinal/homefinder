<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Home;
use App\Http\Requests\SearchHomesRequest;
use App\Http\Requests\StoreHomeRequest;
use App\Http\Resources\HomeResource;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $homes = Home::with('community')->paginate(2);
        return HomeResource::collection($homes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHomeRequest $request)
    {
        // validated() = only the fields that passed the rules
        $home = Home::create($request->validated());

        // load the community so the response shows it too
            return (new HomeResource($home->load('community')))
        ->response()
        ->setStatusCode(201);
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
