<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Events\{ TripAccepted, TripStarted, TripEnded, TripLocationUpdated };
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'origin' => ['required'],
            'destination' => ['required'],
            'destination_name' => ['required']
        ]);

        return $request->user()->trips()->create($request->only([
            'origin',
            'destination',
            'destination_name'
        ]));
    }

    public function show(Request $request, Trip $trip) {
        if ($trip->user->id === $request->user()->id) {
            return $trip;
        }

        if ($trip->driver && $trip->user()->driver) {
            if ($trip->driver->id === $request->user()->driver->id) {
                return $trip;
            }
        }    

        return response()->json(['message' => 'Cannot find this trip.'], 404);
    }

    public function accept(Request $request, Trip $trip) {

        // A driver accepts a trip
        $request->validate([
            'driver_location' => ['required']
        ]);

        $trip->update([
            'driver_id' => $request->user()->id,
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');

        // dispatch trip event
        TripAccepted::dispatch($trip, $request->user());

        return $trip;

    }

    public function start(Request $request, Trip $trip) {

        // A driver has started taking a passenger to their destination
        $trip->update([
            'is_started' => true
        ]);

        $trip->load('driver.user');

        // dispatch trip event
        TripStarted::dispatch($trip, $request->user());

        return $trip;

    }

    public function end(Request $request, Trip $trip) {

        // A driver trip has been completed
        $trip->update([
            'is_complete' => true
        ]);

        $trip->load('driver.user');

        // dispatch trip event
        TripEnded::dispatch($trip, $request->user());

        return $trip;

    }

    public function location(Request $request, Trip $trip) {

        // update the driver's current location
        $request->validate([
            'driver_location' => ['required']
        ]);

        $trip->update([
            'driver_location' => $request->driver_location
        ]);

        $trip->load('driver.user');

        // dispatch trip event
        TripLocationUpdated::dispatch($trip, $request->user());

        return $trip;

    }
}
