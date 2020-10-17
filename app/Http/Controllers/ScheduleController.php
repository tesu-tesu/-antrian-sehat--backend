<?php

namespace App\Http\Controllers;

use App\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        echo "asdsad";die;
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day' => 'required|string',
            'time_open' => 'required|string',
            'time_close' => 'required|string',
            'polyclinic' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(),400);
        }

        $schedule = Schedule::create([
            'day' => $request->day,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
            'polyclinic_id' => $request->polyclinic
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New Schedule has successfully created',
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'day' => 'required|string',
            'time_open' => 'required|string',
            'time_close' => 'required|string',
            'polyclinic' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(),400);
        }

        $updated = Schedule::where('id', $schedule->id)
        ->update([
            'day' => $request->day,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
            'polyclinic_id' => $request->polyclinic
        ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule data updated succesfully'
            ],200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Schedule data cannot be updated'
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        $data = Schedule::find($schedule->id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule data not found'
            ],400);
        }

        if ($data->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule successfully deleted'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Schedule cannot be deleted'
            ], 500);
        }
    }
}