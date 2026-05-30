<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HistoryPlateService;
use App\Models\HistoryPlate;
use Carbon\Carbon;


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

    public function historyPlateByMonth()
    {
        $year = now()->year;

        $result = HistoryPlate::selectRaw("
            EXTRACT(MONTH FROM created_at) as month,
            status,
            COUNT(*) as total
        ")
        ->whereYear('created_at', now()->year)
        ->whereIn('status', [1, 2])
        ->groupBy('month', 'status')
        ->orderBy('month')
        ->get();

        $months = [
            1 => 'JAN',
            2 => 'FEV',
            3 => 'MAR',
            4 => 'ABR',
            5 => 'MAI',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGO',
            9 => 'SET',
            10 => 'OUT',
            11 => 'NOV',
            12 => 'DEZ',
        ];

        $status1 = array_fill(0, 12, 0);
        $status2 = array_fill(0, 12, 0);

        foreach ($result as $item) {
            $index = $item->month - 1;

            if ($item->status == 1) {
                $status1[$index] = $item->total;
            }

            if ($item->status == 2) {
                $status2[$index] = $item->total;
            }
        }

        return response()->json([
            'labels' => array_values($months),
            'series' => [
                [
                    'name' => 'Boleto Aberto',
                    'data' => $status1,
                ],
                [
                    'name' => 'Boleto Pago',
                    'data' => $status2,
                ]
            ]
        ]);
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
