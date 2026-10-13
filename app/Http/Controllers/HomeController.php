<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchHomesRequest;
use App\Http\Requests\StoreHomeRequest;
use App\Http\Resources\HomeResource;
use App\Models\Home;

class HomeController extends Controller
{
    // GET /api/homes: public search with filters (Day 1), validation (Day 2), resources (Day 3)
    public function index(SearchHomesRequest $request)
    {
        $homes = Home::with('community')                       // avoid N+1
            ->when($request->city, function ($query, $city) {
                $query->whereHas('community', function ($q) use ($city) {
                    $q->where('city', 'like', "%{$city}%");
                });
            })
            ->when($request->max_price, fn ($query, $max) => $query->where('price', '<=', $max))
            ->when($request->min_bedrooms, fn ($query, $min) => $query->where('bedrooms', '>=', $min))
            ->orderBy('price')
            ->paginate(2)                                      // change back to 20 later
            ->withQueryString();                               // keep filters in page links

        return HomeResource::collection($homes);
    }

    // POST /api/homes: logged-in users only (Day 4, see routes/api.php)
    public function store(StoreHomeRequest $request)
    {
        $home = Home::create($request->validated());

        return (new HomeResource($home->load('community')))
            ->response()
            ->setStatusCode(201);
    }
}