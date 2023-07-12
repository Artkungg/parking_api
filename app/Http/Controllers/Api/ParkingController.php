<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParkingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parking = Parking::with('slots.reserves')->get();
        return response()->json(['success' => true, 'data' => $parking]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'address' => 'required',
            'free_at_time' => 'required',
            'fee' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        $parking = new Parking;
        $api_key = env("GOOGLE_GEOCODE_API_KEY", null);
        $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($request->input('address')).'&sensor=false&key='.$api_key);
        $geo = json_decode($geo, true);
        if (isset($geo['status']) && ($geo['status'] == 'OK')) {
            $latitude = $geo['results'][0]['geometry']['location']['lat'];
            $longitude = $geo['results'][0]['geometry']['location']['lng'];
            $parking->user_id = $request->input('user_id');
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
        $parking = Parking::with('slots.reserves')->where('id', $id)->get();
        return response()->json(['success' => true, 'data' => $parking]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'address' => 'required',
            'free_at_time' => 'required',
            'fee' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        try {
            $parking = Parking::findOrFail($id);
            $parking->name = $request->input('name');
            $parking->address = $request->input('address');
            $parking->free_at_time = $request->input('free_at_time');
            $parking->fee = $request->input('fee');
            $parking->save();
            return response()->json([
                'success' => true,
                'message' => 'Update parking successfully', 
                'data' => $parking
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $parking = Parking::findOrFail($id);
            $parking->delete();
            return response()->json(['success'=> true, 'message' => 'Delete parking successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e]);
        }
    }

    public function nearbyParking (Request $request) {
        $validator = Validator::make(request()->all(), [
            'lat' => 'required',
            'long' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        $distance = "(ACOS(SIN(RADIANS(latitude))*SIN(RADIANS("
                    .$request->input('lat')."))+COS(RADIANS(latitude))*COS(RADIANS("
                    .$request->input('lat')."))*COS(RADIANS(longitude)-RADIANS("
                    .$request->input('long').")))*6380)";
        $parking = Parking::with('slots.reserves')->whereRaw($distance."<= 15")->get();
        return response()->json(['success' => true, 'data' => $parking]);
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
