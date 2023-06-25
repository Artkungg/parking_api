<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $distance = "(ACOS(SIN(RADIANS(latitude))*SIN(RADIANS("
                    .$request->input('lat')."))+COS(RADIANS(latitude))*COS(RADIANS("
                    .$request->input('lat')."))*COS(RADIANS(longitude)-RADIANS("
                    .$request->input('long').")))*6380)";
        $parking = Parking::with('slots.reserves')->whereRaw($distance."<= 10")->get();
        return response()->json(['success' => true, 'data' => $parking]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $parking = new Parking;
        $api_key = env("GOOGLE_GEOCODE_API_KEY", null);
        $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($request->input('address')).'&sensor=false&key='.$api_key);
        $geo = json_decode($geo, true);
        if (isset($geo['status']) && ($geo['status'] == 'OK')) {
            $latitude = $geo['results'][0]['geometry']['location']['lat'];
            $longitude = $geo['results'][0]['geometry']['location']['lng'];
            $parking->name = $request->input('name');
            $parking->address = $request->input('address');
            $parking->latitude = $latitude;
            $parking->longitude = $longitude;
            $parking->free_at_time = $request->input('free_at_time');
            $parking->fee = $request->input('fee');
            $parking->save();
            return response()->json(['success' => true, 'data' => $parking]);
        }
        return response()->json(['success' => false, 'error' => 'something wrong.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parking = Parking::with('slots.reserves')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $parking]);
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

    public function report(string $id, string $time){
        if ($time == "today"){
            $parking = Parking::withSum(
                [
                    'reserves' => fn ($query) => $query->whereDate(
                        'check_out', Carbon::today()
                    )
                ], 'price'
            )->findOrFail($id);
        } elseif ($time == 'this_week') {
            $parking = Parking::withSum(
                [
                    'reserves' => fn ($query) => $query->whereBetween(
                        'check_out', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                    )
                ], 'price'
            )->findOrFail($id);
        } elseif ($time == 'this_year') {
            $parking = Parking::withSum(
                [
                    'reserves' => fn ($query) => $query->whereBetween(
                        'check_out', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]
                    )
                ], 'price'
            )->findOrFail($id);
        }
        return response()->json(['success' => true, 'data' => $parking]);
    }
}
