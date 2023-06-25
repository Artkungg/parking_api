<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use App\Models\Reserve;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReserveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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

    public function check_in(string $id){
        $slot = Slot::where('parking_id', $id)
                    ->where("is_use", false)
                    ->firstOrFail();

        $slot->is_use = true;
        $slot->save();

        $reserve = new Reserve;
        $reserve->slot_id = $slot->id;
        $reserve->check_in = Carbon::now();
        $reserve->save();

        return response()->json(['data' => $slot]);
    }

    public function check_out(string $id){
        $reserve = Reserve::findOrFail($id);
        $reserve->check_out = Carbon::now();
        $minute = $reserve->check_out->diffInMinutes($reserve->check_in);

        $slot = Slot::findOrFail($reserve->slot_id);
        $slot->is_use = false;

        $parking = Parking::select('id', 'name', 'free_at_time', 'fee')->findOrFail($slot->parking_id);

        if ($minute <= $parking->free_at_time){
            $reserve->price = 0;
        } else {
            $reserve->price = ceil($minute/60) * $parking->fee;
        }

        $reserve->save();
        $slot->save();
        return response()->json([
            'success' => true, 
            'data' => [
                'name' => $parking->name, 
                'slot' => $slot->code,
                'check_in' => $reserve->check_in,
                'check_out' => $reserve->check_out,
                'fee' => $parking->fee,
                'price' => ceil($minute/60) * $parking->fee
            ]
        ]);
    }
}
