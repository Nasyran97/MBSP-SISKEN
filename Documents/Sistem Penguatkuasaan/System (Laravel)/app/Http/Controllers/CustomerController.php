<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Helpers\DateTimeFormatter;
use Illuminate\Support\Facades\Log;


class CustomerController extends Controller
{
    public function index(Request $request) {
        // info("display");
        $data = Customers::query()->where('status', '=', value: 'ACTIVE');

        // Apply filters based on request input
        if ($request->has('username') && $request->username != '') {
            $data->where('username', 'like', '%' . $request->username . '%');  // string type
        }
        if ($request->has('rating') && $request->rating != '') {
            $data->where('rating', '<', $request->rating);  //number type
        }
        if ($request->has('joining_date') && $request->joining_date != '') {
            $data->where('joining_date', '<=', DateTimeFormatter::dbDateFormat($request->joining_date));  // date type
        }
        if ($request->has('joining_month') && $request->joining_month != '') {
            $month = $request->joining_month;
            $year = $request->has('joining_year') ? $request->joining_year : date('Y'); // Default to current year if not provided

            // Filter the data by month and year
            $data->whereYear('joining_date', $year)
                 ->whereMonth('joining_date', $month);
        }

        $data = $data->get();

        if ($request->ajax()) {
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="edit-customer/'.$row->id.'" class="btn btn-outline-primary btn-sm edit" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm delete sa-params-delete" value='.$row->id.' title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('yajra-datatable', compact('data'));
    }

    public function updateStatus(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:customers,id', // Validate that 'id' exists in customers table
            'status' => 'required|string',
        ]);

        // Find the customer by ID and update the status
        $customer = Customers::findOrFail($request->id);
        $customer->status = $request->status;
        $customer->save();

        // Return a JSON response
        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $customer->status,
        ]);
    }

    // app/Http/Controllers/CustomerController.php
    public function editCustomer($id)
    {
        // info($id);
        // Fetch the customer by ID
        $customer = Customers::findOrFail($id);
        $customer->formatjoin_date = \Carbon\Carbon::parse(time: $customer->joining_date)->format('Y-m-d');
        info($customer);

        // Pass the customer data to the view
        return view('edit-customer', compact('customer'));
    }


    public function updateCustomer(Request $request, $id)
    {
        // Validate the incoming request data
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'balance' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('profile_pic')) {
            $path = $request->file('profile_pic')->store('profile_pictures', 'synology');
            // $path = $request->file('profile_pic')->store('edakwa/profile_pictures', 'synology');
        }

        try {
        // Find the customer by ID and update the data
            $customer = Customers::findOrFail($id);
            $customer->email = $request->email;
            $customer->phone = $request->phone;
            $customer->address = $request->address;
            $customer->rating = $request->rating;
            $customer->balance = $request->balance;
            $customer->joining_date = $request->join_date;
            $customer->picture  = $path;
            $customer->save();
            
            // Return success response as JSON
            return response()->json(['success' => true, 'message' => 'Customer updated successfully.']);
        }catch (\Exception $e) {
            // Return error response as JSON
            return response()->json(['success' => false, 'message' => 'Failed to update customer.']);
        }
    }

    public function markAsDeleted(Request $request)
    {
        // Validate that `ids` array is present
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:customers,id'
        ]);

        // Update the status of the selected rows to 'deleted'
        Customers::whereIn('id', $request->ids)->update(['status' => 'deleted']);

        // Return a success response
        return response()->json(['message' => 'Selected items marked as deleted']);
    }
}
