<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct() {
//        $this->middleware('api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::all();
        return response()->json($data, 200);
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
            'name' => 'required|string|between:3,150',
            'email' => 'required|string|email|unique:users|max:100',
            'password' => 'required|string|min:6',
            'phone' => 'required|numeric|digits_between:8,13',
            'role' => 'required|string',
            'residence_number' => 'nullable|numeric|unique:users|digits:16',
            'health_agency' => 'nullable|numeric'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'residence_number' => $request->residence_number,
            'health_agency_id' => $request->health_agency,
            'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'new User has successfully created',
            'user' => $user
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $data = User::findOrFail($user->id);
        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $data = User::find($user->id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'User data not found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:3,150',
            'email' => 'required|string|email|unique:users,email,' .$user->id. '|max:100',
            'password' => 'required|string|min:6',
            'phone' => 'required|numeric|digits_between:8,13',
            'role' => 'required|string',
            'residence_number' => 'nullable|numeric|unique:users,residence_number,' .$user->id. '|digits:16',
            'health_agency' => 'nullable|numeric'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $updated = User::where('id', $user->id)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'residence_number' => $request->residence_number,
                'health_agency_id' => $request->health_agency,
                'password' => bcrypt($request->password)
            ]);

        if ($updated)
            return response()->json([
                'success' => true,
                'message' => 'User data updated successfully!'
            ], 200);
        else
            return response()->json([
                'success' => false,
                'message' => 'User data can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $data = User::find($user->id);
 
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'User data not found'
            ], 400);
        }
 
        if ($data->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'User has successfully deleted'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User can not be deleted'
            ], 500);
        }
    }
}
