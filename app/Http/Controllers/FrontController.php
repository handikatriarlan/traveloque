<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageBookingCheckoutRequest;
use App\Http\Requests\StorePackageBookingRequest;
use App\Http\Requests\UpdatePackageBookingRequest;
use App\Models\PackageBank;
use App\Models\PackageBooking;
use App\Models\PackageTour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FrontController extends Controller
{
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

    public function choose_bank(PackageBooking $packageBooking)
    {
        $user = Auth::user();

        if ($packageBooking->user_id != $user->id) {
            abort(403);
        }

        $banks = PackageBank::all();
        return view('front.choose-bank', compact('packageBooking', 'banks'));
    }

    public function choose_bank_store(UpdatePackageBookingRequest $request, PackageBooking $packageBooking)
    {
        $user = Auth::user();

        if ($packageBooking->user_id != $user->id) {
            abort(403);
        }

        DB::transaction(function () use ($request, $packageBooking) {
            $validated = $request->validated();
            $packageBooking->update([
                'package_bank_id' => $validated['package_bank_id'],
            ]);
        });

        return redirect()->route('front.book-payment', $packageBooking->id);
    }

    public function book_payment(PackageBooking $packageBooking)
    {
        return view('front.book-payment', compact('packageBooking'));
    }

    public function book_payment_store(StorePackageBookingCheckoutRequest $request, PackageBooking $packageBooking)
    {
        $user = Auth::user();

        if ($packageBooking->user_id != $user->id) {
            abort(403);
        }

        DB::transaction(function () use ($request, $user, $packageBooking) {
            $validated = $request->validated();

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $packageBooking->update($validated);
            // $packageBooking->update([
            //     'is_paid' => true,
            // ]);

        });

        return redirect()->route('front.book-finish');
    }

    public function book_finish()
    {
        return view('front.book-finish');
    }
}
