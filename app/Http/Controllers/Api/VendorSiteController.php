<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use Illuminate\Http\JsonResponse;

class VendorSiteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Godown::query()
                ->select(['id', 'name', 'state', 'city', 'location', 'address'])
                ->orderBy('state')
                ->orderBy('city')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
