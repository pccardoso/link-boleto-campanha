<?php

    namespace App\Services;
    use App\Models\AuthorizedPlates;
    use App\Models\HistoryPlate;
    use App\Models\Bill;

    class HistoryPlateService{

        public function validatePlate($plateVehicle){

            $plateAuthorized = AuthorizedPlates::where('plate_number', $plateVehicle)->first();
            $boletCurrent = Bill::where('plate', $plateVehicle)->first();

            if($plateAuthorized && $boletCurrent){

                $statusHistory = match($boletCurrent->descricao_situacao_boleto){
                    "ABERTO" => 1,
                    "BAIXADO" => 2
                };

                HistoryPlate::create([
                    'authorized_plate_id' => $plateAuthorized->id,
                    'status' => $statusHistory
                ]);

                return response()->json([
                    'message' => 'Placa autorizada e boleto encontrado',
                    'plate' => $plateVehicle,
                    'status' => $statusHistory
                ], 200);

            }

            return response()->json([
                'message' => 'Placa não autorizada ou boleto não encontrado',
                'plate' => $plateVehicle,
                'status' => 404
            ], 404);

        }

    }