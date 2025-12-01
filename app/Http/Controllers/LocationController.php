<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getDistricts($regionId)
    {
        $districts = District::where('region_id', $regionId)->pluck('name', 'id');
        return response()->json($districts);
    }
}
