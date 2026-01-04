<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Godown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VendorsImport;

class VendorManagementController extends Controller
{
    /**
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $vendors = User::where('role', 'vendor')
                ->with('godowns')
                ->select('users.*');

            return DataTables::of($vendors)
                ->addColumn('godown_name', function ($vendor) {
                    return $vendor->godowns->first()?->name ?? 'N/A';
                })
                ->addColumn('location', function ($vendor) {
                    return $vendor->godowns->first()?->location ?? 'N/A';
                })
                ->addColumn('action', function ($vendor) {
                    return '<button class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm delete-vendor" data-id="' . $vendor->id . '">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('vendors.index');
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create()
    {
        return view('vendors.create');
    }

    /**
     * Store a newly created vendor in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'godown_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity_limit_mt' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'vendor',
        ]);

        Godown::create([
            'vendor_id' => $user->id,
            'name' => $request->godown_name,
            'location' => $request->location,
            'address' => $request->address,
            'capacity_limit_mt' => $request->capacity_limit_mt,
            'current_stock_mt' => 0,
        ]);

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully!');
    }

    /**
     * Import vendors from Excel file.
     */
    public function import(Request $request)
    {
        // Increase execution time limit for large imports
        set_time_limit(300); // 5 minutes
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $import = new VendorsImport();
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getSuccessCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();
            
            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} vendor(s).";
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} vendor(s) were skipped (already exist).";
                }
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " error(s) occurred. Check logs for details.";
                }
                return redirect()->route('vendors.index')->with('success', $message);
            } else {
                // No vendors imported - check if all were skipped or if there were errors
                if ($skippedCount > 0 && empty($errors)) {
                    $message = "All {$skippedCount} vendor(s) in the file already exist in the database. No new vendors were imported.";
                    return redirect()->route('vendors.index')->with('info', $message);
                } else {
                    $errorMessage = "No vendors were imported. ";
                    if (!empty($errors)) {
                        $errorMessage .= "Errors: " . implode('; ', array_slice($errors, 0, 10));
                        if (count($errors) > 10) {
                            $errorMessage .= " and " . (count($errors) - 10) . " more. Check Laravel logs for full details.";
                        }
                    } else {
                        $errorMessage .= "Please check the Excel file format matches the expected structure.";
                    }
                    if ($skippedCount > 0) {
                        $errorMessage .= " {$skippedCount} vendor(s) were skipped (already exist).";
                    }
                    \Log::error('Vendor import failed', ['errors' => $errors, 'skipped' => $skippedCount]);
                    return back()->with('error', $errorMessage);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Vendor import exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error importing vendors: ' . $e->getMessage() . '. Check Laravel logs for details.');
        }
    }

    /**
     * Remove the specified vendor from storage.
     */
    public function destroy($id)
    {
        $vendor = User::where('id', $id)->where('role', 'vendor')->firstOrFail();
        
        // Delete associated godowns (cascade will handle this)
        $vendor->godowns()->delete();
        $vendor->delete();

        return response()->json(['success' => true, 'message' => 'Vendor deleted successfully!']);
    }
}

