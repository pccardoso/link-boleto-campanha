<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\AuthorizedPlates;

class ImportExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-excel-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/Campanha Reativação.xlsx');

        Log::info("Iniciando importação do arquivo: {$path}");

        $spreadsheet = IOFactory::load($path);

        // Segunda aba
        $sheet = $spreadsheet->getSheet(1);

        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {

            $associado = $sheet->getCell("A{$row}")->getValue();
            $placa = $sheet->getCell("B{$row}")->getValue();
            $cod_veiculo = $sheet->getCell("C{$row}")->getValue();
            $data_contrato = $sheet->getCell("D{$row}")->getValue();
            $situacao = $sheet->getCell("E{$row}")->getValue();
            $tipo_veiculo = $sheet->getCell("F{$row}")->getValue();
            $voluntario = $sheet->getCell("G{$row}")->getValue();
            $email = $sheet->getCell("H{$row}")->getValue();
            $telefone = $sheet->getCell("I{$row}")->getValue();
            $celular = $sheet->getCell("J{$row}")->getValue();
            $valor_fixo = $sheet->getCell("K{$row}")->getValue();

            $cellValorContrato = $sheet->getCell("L{$row}");

            try {
                $valor_contrato = $cellValorContrato->getCalculatedValue();
            } catch (\Throwable $e) {
                $valor_contrato = $cellValorContrato->getOldCalculatedValue();
            }

            $searchPlate = AuthorizedPlates::where('plate_number', $placa)->first();

            if($searchPlate){
                Log::info("Placa já importada: {$placa}");
                continue;
            }
            
            //importar apenas os que possuem placa
            if($placa){

                Log::info("Importando placa: {$placa}");

                $authorizedPlates = AuthorizedPlates::create([
                    "name" => $associado,
                    "plate_number" => $placa,
                    "vehicle_number" => $cod_veiculo,
                    "contract_date" => $data_contrato,
                    "vehicle_status" => $situacao,
                    "vehicle_type" => $tipo_veiculo,
                    "volunteer" => $voluntario,
                    "email" => $email,
                    "phone" => $telefone,
                    "cell_phone" => $celular,
                    "fixed_value" => $valor_fixo,
                    "agreement_value" => $valor_contrato
                ]);

                Log::info("Placa importada com sucesso: {$placa}");
            }
        }
    }
}
