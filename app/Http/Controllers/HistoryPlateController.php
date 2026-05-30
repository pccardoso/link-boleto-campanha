<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HistoryPlateService;

class HistoryPlateController extends Controller
{

    public function __construct(
        protected HistoryPlateService $historyPlateService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    
        $payload = $request->validate([
            'plate_vehicle' => 'required|string',
        ]);

        return $this->historyPlateService->validatePlate($payload['plate_vehicle']);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
}
