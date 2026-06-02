<?php

    namespace App\Services;

    use Illuminate\Support\Facades\Http;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Log;
    use App\Models\Bill;
    use App\Models\AuthorizedPlates;
    use App\Models\HistoryPlate;
    use Symfony\Component\HttpKernel\Exception\HttpException;

    class SGAService{

        public function searchPlateMember($cpfCnpjClient)
        {
            $tokens = [
                "GO" => env('TOKEN_SGA_GO'),
                "CE" => env('TOKEN_SGA')
            ];

            foreach ($tokens as $token) {

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->get("https://api.hinova.com.br/api/sga/v2/associado/buscar/{$cpfCnpjClient}");

                // ✅ sucesso
                if ($response->status() === 200 && $response->json()) {

                    $dataResponse = $response->json();

                    return array_map(function ($temp) {
                        return [
                            "codigo_veiculo" => $temp['codigo_veiculo'],
                            "plate" => $temp['placa'],
                            "model" => $temp['descricao_modelo'],
                            "status" => $temp['situacao']
                        ];
                    }, $dataResponse['veiculos'] ?? []);
                }

                // ❌ se NÃO for 406, já retorna erro direto
                if ($response->status() !== 406) {
                    return $response->json();
                }

                // 🔁 se for 406, tenta próximo token
            }

            // 🚫 se todos falharem com 406
            return [];
        }

        public function listBoletOfUser($cpfCnpjClient)
        {
            $hoje = \Carbon\Carbon::today();

            $tokens = [
                "GO" => env('TOKEN_SGA_GO'),
                "CE" => env('TOKEN_SGA')
            ];

            foreach ($tokens as $state => $token) {

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->post('https://api.hinova.com.br/api/sga/v2/listar/boleto/periodo/', [
                    'cpf_associado' => $cpfCnpjClient,
                    "data_vencimento_original_inicial" => $hoje->copy()->subDays(90)->format('d/m/Y'),
                    "data_vencimento_original_final"   => $hoje->copy()->addDays(90)->format('d/m/Y'),
                ]);

                // ✅ sucesso
                if ($response->status() === 200 && $response->json()) {

                    return [
                        "tokenState" => $state,
                        "data" => $response->json()
                    ];
                    
                }

                // ❌ se não for 406, retorna erro direto
                if ($response->status() !== 406) {
                    return $response->json();
                }

                // 🔁 se for 406, tenta próximo token
            }

            // 🚫 nenhum token retornou sucesso
            return [];
        }

        public function listBoletoOfPlate($plateVehicle)
        {

            /*$platAuthorized = AuthorizedPlates::where('plate_number', $plateVehicle)->first();

            if(!$platAuthorized){
                throw new HttpException(
                    422,
                    'Placa não autorizada para consulta de boletos.'
                );
            }*/

            //verificar se há boletos já cadastrados

            $boletCurrent = Bill::where('plate', strtoupper($plateVehicle))->get();

            /*if($boletCurrent->count() > 0){
            
                if($boletCurrent[0]->descricao_situacao_boleto === "ABERTO"){
                    HistoryPlate::create([
                        "authorized_plate_id" => $platAuthorized->id,
                        "status" => 1
                    ]);
                }

                if($boletCurrent[0]->descricao_situacao_boleto === "BAIXADO"){
                    HistoryPlate::create([
                        "authorized_plate_id" => $platAuthorized->id,
                        "status" => 2
                    ]);
                }

            }*/

            $hoje = \Carbon\Carbon::today();

            $tokens = [
                "GO" => env('TOKEN_SGA_GO'),
                "CE" => env('TOKEN_SGA')
            ];

            foreach ($tokens as $state => $token) {

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->post('https://api.hinova.com.br/api/sga/v2/listar/boleto-associado-veiculo', [
                    'placa' => $plateVehicle,
                    "data_vencimento_original_inicial" => $hoje->copy()->subDays(90)->format('d/m/Y'),
                    "data_vencimento_original_final"   => $hoje->copy()->addDays(90)->format('d/m/Y'),
                ]);

                // ✅ sucesso
                if ($response->status() === 200 && $response->json()) {
                    return [
                        "tokenState" => $state,
                        "data" => $response->json(),
                        "status" => 200,
                        "boletCurrent" => $boletCurrent
                    ];
                }

                if ($response->status() === 406) {

                    $body = $response->json();

                    if (isset($body['mensagem']) && str_contains($body['mensagem'], 'Nenhum boleto')) {
                        return [];
                    }

                    continue;
                }

                return $response->json();
            }

            throw new HttpException(
                404,
                'Nenhum boleto encontrado para a placa informada.'
            );
        }

        public function updateMaturity($codigoBolet, $state){

            Log::info('Iniciando processo de atualização de boleto', [
                'parametro' => $codigoBolet,
                'state' => $state
            ]);

            $plateVehicle = data_get($codigoBolet, 'veiculos.0.placa', null) ?? data_get($codigoBolet, 'veiculo.0.placa', null);

            try{

                $tokenState = $state === "CE" ? env('TOKEN_SGA') : env('TOKEN_SGA_GO');

                Log::info("Buscando pelo Token: ".$tokenState);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenState,
                    'Accept' => 'application/json',
                ])->post('https://api.hinova.com.br/api/sga/v2/alterar/vencimento-boleto', [
                    "nosso_numero" => $codigoBolet['nosso_numero'],
                    "nova_data_vencimento" => Carbon::now()->format('d/m/Y')
                ]);

                if($response->status() === 200){

                    //BOLETO ATUALIZADO PORTANTO GERAR BILLET PARA O BOLETO ATUALIZADO

                    $boletoAtualizado = $response->json();

                    Bill::updateOrCreate(
                        [
                            "nosso_numero" => data_get($codigoBolet, 'nosso_numero', 0),
                        ],
                        [
                            "codigo_boleto" => data_get($codigoBolet, 'codigo_boleto', 0),
                            "nova_data_vencimento" => $boletoAtualizado['nova_data_vencimento'],
                            "cpf_cnpj" => data_get($codigoBolet, 'cpf', 'Não Identificado'),
                            "associado" => data_get($codigoBolet, 'nome_associado', 'Não Identificado'),
                            "linha_digitavel" => data_get($codigoBolet, 'linha_digitavel', 'Não Identificado'),
                            "link_boleto" => data_get($codigoBolet, 'link_boleto', 'Não Identificado'),
                            "valor_boleto" => floatval(data_get($codigoBolet, 'valor_boleto', 0)),
                            "plate" => $plateVehicle
                        ]
                    );

                    return $response->json();

                }

            }catch(\Exception $e){

                Log::error('Erro ao atualizar boleto', [
                    'parametro' => $codigoBolet,
                    'mensagem' => $e->getMessage(),
                    'linha' => $e->getLine(),
                    'arquivo' => $e->getFile(),
                ]);

                return response()->json([
                    'erro' => 'Não foi possível atualizar o boleto.'
                ], 500);

            }

        }

        public function generateBillet($payload, $state){

            $veiculosJson = data_get($payload, 'veiculos') ?? data_get($payload, 'veiculo', '[]');

            $veiculos = json_decode($veiculosJson, true);

            Log::info('Iniciando processo de geração de boleto', [
                'parametro' => $payload,
                'state' => $state
            ]);
    
            $hoje = \Carbon\Carbon::today()->format('d/m/Y');
            $mesReferencia = \Carbon\Carbon::today()->format('m/Y');
            $vencimentoBolet = \Carbon\Carbon::today()->format('d/m/Y');

            try{

                //$place = AuthorizedPlates::where('plate_number', data_get($veiculos, '0.placa', 0))->first();

                $placeVehicle = data_get($veiculos, '0.placa', null);

                $tokenState = $state === "CE" ? env('TOKEN_SGA') : env('TOKEN_SGA_GO');

                $responseDataVehicle = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenState,
                    'Accept' => 'application/json',
                ])->get("https://api.hinova.com.br/api/sga/v2/veiculo/buscar/".$placeVehicle);

                $valorFixo = 0;
                $valorFinal = 0;

                if($responseDataVehicle->status() === 200 && $responseDataVehicle->json()){

                    $place = $responseDataVehicle->json();

                    $valorFixo = floatval($place[0]['valor_fixo'] );

                    $valorFinal = match (true) {
                        $valorFixo <= 100 => 50,
                        $valorFixo <= 150 => 75,
                        $valorFixo <= 200 => 100,
                        $valorFixo <= 300 => 150,
                        $valorFixo <= 450 => 200,
                        default => 225,
                    };

                }

                
            
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenState,
                    'Accept' => 'application/json'
                ])->post('https://api.hinova.com.br/api/sga/v2/boleto/cadastrar', [
                    "codigo_associado" => data_get($payload, 'codigo_associado', 0),
                    "codigo_tipo_boleto" => $state === "CE" ? 82 : 38,
                    "codigo_conta" => $state === "CE" ? 7 : 1 ,
                    "link_boleto" => true,
                    "codigo_situacao" => "2",
                    "array_parcela" => [
                        (object)[
                            "valor" => $valorFinal,
                            "vencimento" => $vencimentoBolet
                        ]
                    ],
                    "mes_referente" => $mesReferencia,
                    "data_emissao" => $hoje,
                    "referencia" => [
                        (object)[
                            "modulo" => "veiculo",
                            "codigo_modulo" => data_get($veiculos, '0.codigo_veiculo', 0),
                        ]
                    ]
                    
                ]);

                $body = [
                    "codigo_associado" => data_get($payload, 'codigo_associado', 0),
                    "codigo_tipo_boleto" => $state === "CE" ? 82 : 38,
                    "codigo_conta" => $state === "CE" ? 7 : 1,
                    "link_boleto" => true,
                    "codigo_situacao" => "2",
                    "array_parcela" => [
                        (object)[
                            "valor" => $valorFinal,
                            "vencimento" => $vencimentoBolet
                        ]
                    ],
                    "mes_referente" => $mesReferencia,
                    "data_emissao" => $hoje,
                    "referencia" => [
                        (object)[
                            "modulo" => "veiculo",
                            "codigo_modulo" => data_get($veiculos, '0.codigo_veiculo', 0),
                        ]
                    ]
                ];

                Log::info('Requisição para API de geração de boleto', [
                    'endpoint' => 'https://api.hinova.com.br/api/sga/v2/boleto/cadastrar',
                    'payload' => $body
                ]);

                

                Log::info('Resposta da API de geração de boleto', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                if($response->status() === 200){

                    $bodyResponse = $response->json();

                    $nossoNumero = $bodyResponse["0"]["nosso_numero"];
                    $linhaDigitavel = $bodyResponse["0"]["linha_digitavel"];
                    $linkBoleto = $bodyResponse["0"]["link_boleto"];

                    Bill::updateOrCreate(
                        [
                            "nosso_numero" => $nossoNumero,
                        ],
                        [
                            "codigo_boleto" => $nossoNumero,
                            "nova_data_vencimento" => $vencimentoBolet,
                            "cpf_cnpj" => data_get($payload, 'cpf', 'Não Identificado'),
                            "associado" => data_get($payload, 'nome_associado', 'Não Identificado'),
                            "linha_digitavel" => $linhaDigitavel,
                            "link_boleto" => $linkBoleto,
                            "valor_boleto" => $valorFinal,
                            "plate" => data_get($veiculos, '0.placa', 0)
                        ]
                    );

                    return $response->json();

                }


            }catch(\Exception $e){

                Log::error('Erro ao gerar boleto', [
                    'parametro' => $payload,
                    'mensagem' => $e->getMessage(),
                    'linha' => $e->getLine(),
                    'arquivo' => $e->getFile(),
                ]);

                return response()->json([
                    'erro' => 'Não foi possível gerar o boleto.'
                ], 500);

            }

        }

        public function getBolet($nosso_numero){

            try{
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('TOKEN_SGA'),
                    'Accept' => 'application/json',
                ])->get('https://api.hinova.com.br/api/sga/v2/buscar/boleto/'.$nosso_numero, []);

                if($response->status() === 200){

                    return $response->json();

                }

            }catch(\Exception $e){

                Log::error('Erro ao buscar boleto', [
                    'parametro' => $nosso_numero,
                    'mensagem' => $e->getMessage(),
                    'linha' => $e->getLine(),
                    'arquivo' => $e->getFile(),
                ]);

                return response()->json([
                    'erro' => 'Não foi possível buscar o boleto.'
                ], 500);

            }

        }

        public function getVehicle($plate, $state){

            try{
                $tokenState = $state === "CE" ? env('TOKEN_SGA') : env('TOKEN_SGA_GO');

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenState,
                    'Accept' => 'application/json',
                ])->get("https://api.hinova.com.br/api/sga/v2/veiculo/buscar/".$plate, []);

                if($response->status() === 200){

                    return $response->json();

                }

            }catch(\Exception $e){

                Log::error('Erro ao buscar veiculo', [
                    'parametro' => $plate,
                    'mensagem' => $e->getMessage(),
                    'linha' => $e->getLine(),
                    'arquivo' => $e->getFile(),
                ]);

                return response()->json([
                    'erro' => 'Não foi possível buscar o veiculo.'
                ], 500);

            }

        }

    }