<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageBookingRequest;
use App\Models\PackageBank;
use App\Models\PackageBooking;
use App\Models\PackageTour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function book(PackageTour $packageTour)
    {
        return view('front.book', compact('packageTour'));
    }

    public function book_store(StorePackageBookingRequest $request, PackageTour $packageTour)
    {
        $user = Auth::user();
        $bank = PackageBank::orderByDesc('id')->first();
        $packageBookingId = null;

        DB::transaction(function () use ($request, $packageTour, $user, $bank, &$packageBookingId) {
            $validated = $request->validated();

            $startDate = new Carbon($validated['start_date']);
            $totalDays = $packageTour->days - 1;
            $endDate = $startDate->addDays($totalDays);

            $subTotal = $packageTour->price * $validated['quantity'];
            $insurance = 30000 * $validated['quantity'];
            $tax = $subTotal * 0.1;

            $validated['end_date'] = $endDate;
            $validated['user_id'] = $user->id;
            $validated['is_paid'] = false;
            $validated['proof'] = 'dummytrx.png';
            $validated['package_tour_id'] = $packageTour->id;
            $validated['package_bank_id'] = $bank->id;
            $validated['insurance'] = $insurance;
            $validated['tax'] = $tax;
            $validated['sub_total'] = $subTotal;
            $validated['total_amount'] = $subTotal + $tax + $insurance;

            $packageBooking = PackageBooking::create($validated);
            $packageBookingId = $packageBooking->id;
        });

        if ($packageBookingId) {
            return redirect()->route('front.choose_bank', $packageBookingId);
        } else {
            return back()->withErrors('Failed to Create Booking.');
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
