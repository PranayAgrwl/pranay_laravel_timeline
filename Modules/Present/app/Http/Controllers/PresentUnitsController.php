<?php

namespace Modules\Present\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Modules\Present\app\Models\PresentUnits;
use Modules\Present\Models\PresentUnits;
use Modules\Present\Http\Requests\StorePresentUnitRequest; 
use Modules\Present\Http\Requests\UpdatePresentUnitRequest;

class PresentUnitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $units = PresentUnits::orderBy('name')->get();
        // return view('present::units.index', compact('units'));

        return view('present::units.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('present::units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('present::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('present::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
