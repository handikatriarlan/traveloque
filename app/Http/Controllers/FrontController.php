<?php

namespace App\Http\Controllers;

use App\Models\PackageTour;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packageTours = PackageTour::orderByDesc('id')->take(3)->get();
        return view('front.index', compact('packageTours'));
    }

    public function details(PackageTour $packageTour)
    {
        $latestPhotos = $packageTour->package_photos()->orderByDesc('id')->take(3)->get();

        return view('front.details', compact('packageTour', 'latestPhotos'));
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
