<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response)
    {
        $htmlContent = '<!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
            <title>Bem Vindo - Licit API</title>
        </head>
        <body>
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img src="https://canhotinho.pe.gov.br/img/logo_loading.png" alt="Logo" class="img-fluid mt-5">
                        <h1 class="mt-5">Bem Vindo ao Licit API</h1>
                        <p class="lead">Aqui você tem acesso aos seus dados.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        $response->getBody()->write($htmlContent);
        return $response->withHeader('Content-Type', 'text/html');
        return $response;
    });
    
    $app->group('/api/v1', function (Group $group) {
        $url_api = 'https://sistemas.tce.pe.gov.br/DadosAbertos/';

        // Consulta Unidades Gestoras
        $group->get('/unidades_gestoras', function (Request $request, Response $response){
            $url = 'https://sistemas.tce.pe.gov.br/DadosAbertos/UnidadesJurisdicionadas!json';
            $unidades_gestoras = file_get_contents($url);
            $response->getBody()->write(mb_convert_encoding($unidades_gestoras, 'UTF-8', 'ISO-8859-1'));
            return $response->withHeader('Content-Type', 'application/json');
        });

        // Consultar Licitações
        $group->get('/consulta_licitacoes/{id_ug}/{ano}', function (Request $request, Response $response, array $args) use ($url_api) {
            $id_ug = $args['id_ug'];
            $ano = $args['ano'];

            $licitacoes = file_get_contents($url_api . "LicitacoesDetalhes!json?CODIGOUG={$id_ug}&&ANOMODALIDADE={$ano}");

            $response->getBody()->write(mb_convert_encoding($licitacoes, 'UTF-8', 'ISO-8859-1'));
            return $response->withHeader('Content-Type', 'application/json');

        });

        // Consulta Anexos da Licitação
        $group->get('/consulta_anexos_licitacao/{codigo_pl}', function (Request $request, Response $response, array $args) use ($url_api) {
            $codigo_pl = $args['codigo_pl'];
            

            $anexos = file_get_contents($url_api . "LicitacoesDocumentos!json?ProcessoLicitatorio=".$codigo_pl);

            $response->getBody()->write(mb_convert_encoding($anexos, 'UTF-8', 'ISO-8859-1'));
            return $response->withHeader('Content-Type', 'application/json');

        });

        // Consulta Contratos
        $group->get('/consulta_contratos/{id_ug}/{ano}', function (Request $request, Response $response, array $args) use ($url_api) {
            $id_ug = $args['id_ug'];
            $ano = $args['ano'];

            $contratos = file_get_contents($url_api . "Contratos!json?AnoContrato=".$ano."&CodigoUG=".$id_ug);

            $response->getBody()->write(mb_convert_encoding($contratos, 'UTF-8', 'ISO-8859-1'));
            return $response->withHeader('Content-Type', 'application/json');

        });

        // Consulta Anexos dos contratos
        $group->get('/consulta_anexos_contrato/{codigo_contrato_original}', function (Request $request, Response $response, array $args) use ($url_api) {
            $codigo_contrato_original = $args['codigo_contrato_original'];

            $anexo_contrato = file_get_contents($url_api . "/ContratoDocumentos!json?CodigoContratoOriginal=".$codigo_contrato_original);

            $response->getBody()->write(mb_convert_encoding($anexo_contrato, 'UTF-8', 'ISO-8859-1'));
            return $response->withHeader('Content-Type', 'application/json');

        });

        // Consulta Obras
        $group->get('/consulta_obras/{nome_municipio}', function (Request $request, Response $response, array $args) use ($url_api) {
            $nome_municipio = $args['nome_municipio'];

            $obras = file_get_contents($url_api . "/Obras!json?Municipio=".$nome_municipio);

            $response->getBody()->write(mb_convert_encoding($obras, 'UTF-8', 'ISO-8859-1'));
            return $response->withHeader('Content-Type', 'application/json');

        });

    });
    
};
