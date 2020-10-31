<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WaitingListController extends Controller
{
    public function __construct() {
        $this->middleware('roleUser:Admin')->except(['store', 'show', 'showNearestWaitingList', 'getWaitingList', 'getCurrentWaitingListRegist']);
        $this->middleware('roleUser:Admin,Pasien,Super Admin')->only(['show']);
        $this->middleware('roleUser:Admin,Pasien')->only(['store']);
        $this->middleware('roleUser:Pasien')->only(['showNearestWaitingList', 'getWaitingList', 'getCurrentWaitingListRegist']);
    }
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
            'schedule' => 'required|numeric',
            'registered_date' => 'required|date',
            'residence_number' => 'required|numeric|digits:16',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $ordered = WaitingList::select('id')
            ->where('residence_number', $request->residence_number)
            ->where('schedule_id', $request->schedule)
            ->where('registered_date', $request->registered_date)
            ->first();

        if($ordered != null)
            return response()->json($validator->errors(), 400);

        $latestOrder = WaitingList::select('order_number')
            ->where('registered_date', $request->registered_date)
            ->where('schedule_id', $request->schedule)
            ->latest()->first();
        if($latestOrder == null) {
            $latestOrder = new WaitingList();
        }
        $latestOrder->order_number++;

        $waitingListId = WaitingList::create([
            'user_id' => Auth::id(),
            'schedule_id' => $request->schedule,
            'registered_date' => $request->registered_date,
            'order_number' => $latestOrder->order_number,
            'residence_number' => $request->residence_number,
            'status' => 'Belum Diperiksa',
        ])->id;

        $waitingListUpdated = WaitingList::where('id', $waitingListId)
            ->update([
                'barcode' => $waitingListId . '_' . $request->residence_number,
            ]);

        $waitingList = WaitingList::where('id', $waitingListId)->first();

        if($waitingListUpdated)
            return response()->json([
                'success' => true,
                'message' => 'Add data successfully!',
                'waiting_list' => $waitingList,
            ], 200);
        else
            return response()->json([
                'success' => false,
                'message' => 'Add data failed!',
                'waiting_list' => $waitingList,
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\WaitingList  $waitingList
     * @return \Illuminate\Http\Response
     */
    public function show(WaitingList $waitingList)
    {
        return response()->json($waitingList, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\WaitingList  $waitingList
     * @return \Illuminate\Http\Response
     */
    public function edit(WaitingList $waitingList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\WaitingList  $waitingList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WaitingList $waitingList)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $updated = WaitingList::where('id', $waitingList->id)
                            ->update([
                                'status' => $request->status,
                            ]);

        if($updated)
            return response()->json([
                'success' => true,
                'message' => 'Waiting list\'s status has been successfully updated!',
                'waiting_list' => $updated,
            ], 200);
        else
            return response()->json([
                'success' => false,
                'message' => 'Failed to update waiting list\'s status',
                'waiting_list' => $updated,
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\WaitingList  $waitingList
     * @return \Illuminate\Http\Response
     */
    public function destroy(WaitingList $waitingList)
    {
        if ($waitingList->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Waiting list has successfully deleted'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Waiting list can not be deleted'
            ], 500);
        }
    }

    public function getWaitingList() {
        $userId = Auth::id();
        date_default_timezone_set ('Asia/Jakarta');

        $currentWaitingList = DB::table('waiting_list_view')
                                ->where('user_id', $userId)
                                ->where('registered_date', date('Y-m-d'))
                                ->get();

        $futureWaitingList = DB::table('waiting_list_view')
                                ->where('user_id', $userId)
                                ->where('registered_date', '>', date('Y-m-d'))
                                ->get();

        $historyWaitingList = DB::table('waiting_list_view')
                                ->where('user_id', $userId)
                                ->where(function($q) {
                                    $q->where('status', 'Dibatalkan')
                                      ->orWhere('status', 'Sudah Diperiksa');
                                })
                                // ->where('registered_date', '<', date('Y-m-d'))
                                ->get();

        return response()->json([
            'success' => true,
            'currentWaitingList' => $currentWaitingList,
            'historyWaitingList' => $historyWaitingList,
            'futureWaitingList' => $futureWaitingList,
        ], 200);
    }

    public function showNearestWaitingList() {
        $userId = Auth::id();

        $waitingList = DB::table('waiting_list_view')
            ->where('user_id', $userId)
            ->where('distance_number', '>=', '0')
            ->where(function($q) {
                $q->where('status', 'Belum Diperiksa')
                    ->orWhere('status', 'Sedang Diperiksa');
            })->first();

        if($waitingList) {
            $message = "Successfully get nearest waiting list";
            $error = true;
        }
        else {
            $message = "You don\'t have any nearest waiting list";
            $error = false;
        }

        return response()->json([
            'success' => $error,
            'message' => $message,
            'waiting_list' => $waitingList,
        ], 200);
    }
    
    public function getCurrentWaitingListRegist($schedule, $date){
        $currentWaitingList = DB::table('waiting_list_view')
            ->select('current_number', 'latest_number')
            ->where('schedule_id', $schedule)
            ->where('registered_date', $date)
            ->first();

        if(!$currentWaitingList) {
            $currentWaitingList = new WaitingList();
            $currentWaitingList->current_number = 0;
            $currentWaitingList->latest_number = 0;
        }

        return response()->json([
            'success' => true,
            'message' => "Get the current and latest number in specific schedule and date",
            'waiting_list' => $currentWaitingList,
        ], 200);
    }
}
