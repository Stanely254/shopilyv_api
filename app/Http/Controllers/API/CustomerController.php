<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $customers = Customers::all();
        return response(['customers' => CustomerResource::collection($customers), 'message' => 'Retrieved Successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $username = $request->username;
        $company = $request->shop;
        $count = DB::table('customers')->where('phone', $username)->where('company_id', $company->shop)->first();
        if($count > 0){
            return response(['message' => 'customer already exists'], 403);
        } else {
            $data = $request->all();
            $validator = Validator::make($data, [
                'username' => 'required|max:255',
                'phone' => 'required|max:14',
                'company_id' => 'required'
            ]);

            if($validator->fails()){
                return response(['message' => $validator->errors(), 'Validation Errors']);
            }

            $customers = Customers::create($data);
            return response(['customer' => new CustomerResource($customers), 'message' => 'Created Successfully'], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function show(Customers $customers)
    {
        //
        return response(['customer' => new CustomerResource($customers)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customers $customers)
    {
        //
        $customers->update($request->all());
        return response(['customer' => new CustomerResource($customers), 'message' => 'Updated Successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customers $customers)
    {
        //
        $customers->delete();
        return response(['message' => 'deleted']);
    }
}
