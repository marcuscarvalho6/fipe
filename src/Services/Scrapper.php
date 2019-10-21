<?php

namespace Marcuscarvalho6\Fipe;

use Exception;
use GuzzleHttp\Client;
use Marcuscarvalho6\Fipe\Entities\FipeModel;
use Marcuscarvalho6\Fipe\Entities\FipeBranch;
use Marcuscarvalho6\Fipe\FipeReferenceService;
use Marcuscarvalho6\Fipe\Entities\FipeReference;
use Marcuscarvalho6\Fipe\Entities\FipeYearModel;
use Marcuscarvalho6\Fipe\Entities\FipeYearModelDetail;

class Scrapper
{
    private $client;
    private $fipeReference;
    private $fipeReferenceService;

    public function __construct(Client $client, FipeReferenceService $fipeReferenceService)
    {
        set_time_limit(-1);
        $this->client = $client;
        $this->fipeReferenceService = $fipeReferenceService;
    }

    public function updateAll()
    {
        $this->updateCars();
        $this->updateMotorcycles();
        $this->updateTrucks();
    }

    public function updateCars()
    {
        $data = [];
        $data['referenece'] = $this->getReference();
        if(!$data['referenece']) {
            abort(500, 'Eita, não há referências disponíveis para atualização');
        }
        $brands = $this->getBrands($data['referenece']['codigo'], true, true, config('lupa.fipe.categories.cars'), true);
    }

    public function updateMotorcycles()
    {
        $data = [];
        $data['referenece'] = $this->getReference();
        if(!$data['referenece']) {
            abort(500, 'Eita, não há referências disponíveis para atualização');
        }
        $brands = $this->getBrands($data['referenece']['codigo'], true, true, config('lupa.fipe.categories.motorcycles'), true);
    }

    public function updateTrucks()
    {
        $data = [];
        $data['referenece'] = $this->getReference();
        if(!$data['referenece']) {
            abort(500, 'Eita, não há referências disponíveis para atualização');
        }
        $brands = $this->getBrands($data['referenece']['codigo'], true, true, config('lupa.fipe.categories.trucks'), true);
    }

    public function getReference()
    {
        try {
            // Vamos obter as Referências
            $data = [];
            $lastReference = $this->fipeReferenceService->references();
            if(!is_object($lastReference)) {
                abort(500, 'Dados de referência não encontrados');
            }
            $data['codigo'] =  $lastReference->Codigo;
            $data['mes'] =  $lastReference->Mes;
            FipeReference::updateOrCreate(['code' => $lastReference->Codigo], ['code' => $lastReference->Codigo, 'month' => $lastReference->Mes]);
            return $data;

        } catch (RequestException $e) {
            abort(500, 'Eita, parece que deu algo errado ao carregar as referências :/');
        }
    }

    public function getBrands($reference, $brand=true, $model=true, $type=1, $view=true)
    {
        try {
            // Vamos obter as marcas
            $res = $this->client->request('POST', config('lupa.fipe.url.brands'), [
                'headers' => config('lupa.fipe.request.headers'),
                'form_params' => [
                    'codigoTabelaReferencia' => $reference,
                    'codigoTipoVeiculo' => $type                
                ]
            ]);
            
            if($res->getStatusCode() == 200){
                $body = $res->getBody();
                $content = $body->getContents();
                $brands = json_decode($content, 1);
                foreach($brands as $brand) {
                    $brand = (object) ($brand);
                    // Adiciona dados no banco
                    FipeBranch::updateOrCreate(['code' => $brand->Value], ['code' => $brand->Value, 'name' => $brand->Label]);
                    if($view) {
                        echo "\n\e[1;37;40m\e[0m\n";
                        echo "\e[1;37;40m\e[0m\e[0;32;40m{$brand->Label}\e[0m\n";
                        echo "\e[1;37;40m\e[0m";
                    }
                    if($model) {
                        $models = $this->getModel($reference, $type, $brand->Value);
                        if(is_object($models)) {
                            foreach($models->Modelos as $model) {
                                FipeModel::updateOrCreate(['code' => $model->Value], ['code' => $model->Value, 'name' => $model->Label]);
                                $modelYears = $this->getModelYears($reference, $type, $brand->Value, $model->Value);
                                foreach($modelYears as $modelYear) {
                                    $storedModel = FipeYearModel::updateOrCreate(['value' => $modelYear->Value, 'model_code' => $model->Value], ['value' => $modelYear->Value, 'name' => $modelYear->Label]);
                                    $explodedLabel = explode(' ',$modelYear->Label);
                                    $explodedValue = explode('-',$modelYear->Value);
                                    $detail = $this->getModelDetail(
                                        $reference,
                                        $brand->Value,
                                        $model->Value,
                                        $type,
                                        $explodedLabel[0],
                                        $explodedLabel[1],
                                        $modeloCodigoExterno='', 
                                        $tipoConsulta='tradicional'
                                    );

                                    $value = preg_replace("/[^0-9]/", "", $detail->Valor);
                                    $value = substr($value, 0, -2) . '.' . substr($value, -2);
                                    $value = floatVal($value);
                                    
                                    FipeYearModelDetail::updateOrCreate(['fipe_year_model_id' => $storedModel->id], [
                                        'fipe_year_model_id' => $storedModel->id,
                                        'value' => $value,
                                        'marca' => $detail->Marca,
                                        'modelo' => $detail->Modelo,
                                        'ano_modelo' => $detail->AnoModelo,
                                        'combustivel' => $detail->Combustivel,
                                        'codigo_fipe' => $detail->CodigoFipe,
                                        'mes_referencia' => $detail->MesReferencia,
                                        'autenticacao' => $detail->Autenticacao,
                                        'tipo_veiculo' => $detail->TipoVeiculo,
                                        'sigla_combustivel' => $detail->SiglaCombustivel,
                                        'data_consulta' => $detail->DataConsulta
                                    ]);

                                    echo "\n| COD: {$detail->CodigoFipe}\t";
                                    echo "| MOD: ".str_pad($detail->Modelo, 40)."\t";
                                    echo "| ANO: {$detail->AnoModelo}\t";
                                    echo "| COM: ".str_pad($detail->Combustivel,10)."\t";
                                    echo "| VAL: ".str_pad($detail->Valor, 15)."\e[0m\t";
                                    echo "| REF: {$detail->MesReferencia}\t";
                                }
                            }
                        }
                    }
                }
            }
            echo "\nDemorou, mas acabou :)";
        } catch (RequestException $e) {
            abort($e->getCode(), 'Oops, algo executar o processo principal :(');
        }
    }

    public function getModel($reference, $type, $brand)
    {
        try {

            $res = $this->client->request('POST', config('lupa.fipe.url.models'), [
                'headers' => config('lupa.fipe.request.headers'),
                'form_params' => [
                    'codigoTabelaReferencia' => $reference,
                    'codigoTipoVeiculo' => $type,
                    'codigoMarca' => $brand,
                ]
            ]);
            
            if($res->getStatusCode() == 200){
                $body = $res->getBody();
                $modelos = $body->getContents();
                return json_decode($modelos);
            }

        } catch (RequestException $e) {
            abort($e->getCode(), 'Oops, algo errado ao carregar os modelos :(');
        }
    }

    public function getModelYears($reference, $category, $brand, $model)
    {
        try {

            $res = $this->client->request('POST', config('lupa.fipe.url.model_years'), [
                'headers' => config('lupa.fipe.request.headers'),
                'form_params' => [
                    'codigoTipoVeiculo' => $category,
                    'codigoTabelaReferencia' => $reference,
                    'codigoMarca' => $brand,
                    'codigoModelo' => $model
                ]
            ]);
            
            if($res->getStatusCode() == 200){
                $body = $res->getBody();
                $model_years = $body->getContents();
                return json_decode($model_years);
            }

        } catch (RequestException $e) {
            abort($e->getCode(), 'Oops, algo errado ao carregar as marcas :(');
        }
    }

    public function getModelDetail($reference, $brand, $model, $type, $year, $fuelType, $modeloCodigoExterno=null, $tipoConsulta='tradicional')
    {
        try {

            $res = $this->client->request('POST', config('lupa.fipe.url.model_details'), [
                'headers' => config('lupa.fipe.request.headers'),
                'form_params' => [
                    'codigoTabelaReferencia' => $reference,
                    'codigoMarca' => $brand,
                    'codigoModelo' => $model,
                    'codigoTipoVeiculo' => $type,
                    'anoModelo' => $year,
                    'codigoTipoCombustivel' =>  config('lupa.fipe.fuel_types')[$fuelType],
                    'tipoVeiculo' => config('lupa.fipe.category_names')[$type],
                    'modeloCodigoExterno' => $modeloCodigoExterno,
                    'tipoConsulta' => $tipoConsulta
                ]
            ]);
            
            if($res->getStatusCode() == 200){
                $body = $res->getBody();
                $model_years = $body->getContents();
                return json_decode($model_years);
            }

        } catch (RequestException $e) {
            abort($e->getCode(), 'Oops, algo errado ao carregar os detalhes do veículo :(');
        }
    }
}