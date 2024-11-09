<?php

namespace App\Http\Controllers;

use App\Models\PackageBooking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function my_bookings()
    {
        return view('dashboard.my-bookings');
    }

    public function booking_details(PackageBooking $packageBooking)
    {
        return view('dashboard.booking-details');
    }
}
