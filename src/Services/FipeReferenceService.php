<?php

namespace  Marcuscarvalho6\Fipe;

use GuzzleHttp\Client;

class FipeReferenceService
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function references()
    {
        try {

            $res = $this->client->request('POST', config('lupa.fipe.url.references'), [
                'headers' => config('lupa.fipe.request.headers')
            ]);

            if($res->getStatusCode() == 200){
                $body = $res->getBody();
                $referencias = $body->getContents();
                $referencias = json_decode($referencias);
                return $referencias[0];
            }

        } catch (RequestException $e) {
            abort($e->getCode(), 'Erro interno');
        }
    }
}