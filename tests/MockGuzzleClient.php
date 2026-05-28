<?php

namespace Rrq\Vexagame\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Rrq\Vexagame\VexaGame;

trait MockGuzzleClient
{
    protected function createVexaGame(MockHandler $mockHandler): VexaGame
    {
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $vexaGame = new VexaGame([
            'api_key' => 'test-api-key',
            'base_url' => 'https://api.test.com',
            'timeout' => 10,
        ]);

        $reflection = new \ReflectionClass($vexaGame);
        $property = $reflection->getProperty('client');
        $property->setValue($vexaGame, $client);

        return $vexaGame;
    }

    protected function mockResponse(int $status, array $body): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }
}
