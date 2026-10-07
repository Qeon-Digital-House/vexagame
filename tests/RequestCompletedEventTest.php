<?php

namespace Rrq\Vexagame\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Rrq\Vexagame\Events\RequestCompleted;
use Rrq\Vexagame\Exceptions\VexaGameException;

class RequestCompletedEventTest extends TestCase
{
    use MockGuzzleClient;

    /** @var RequestCompleted[] */
    private array $dispatched = [];

    private function events(): Dispatcher
    {
        $events = new Dispatcher();
        $events->listen(RequestCompleted::class, function (RequestCompleted $event) {
            $this->dispatched[] = $event;
        });

        return $events;
    }

    public function testDispatchesOnSuccess(): void
    {
        $body = ['code' => 200, 'payload' => ['latest_balance' => 5000]];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $body),
        ]), $this->events());

        $vexaGame->getProductItems('free-fire');

        $this->assertCount(1, $this->dispatched);
        $event = $this->dispatched[0];
        $this->assertSame('GET', $event->method);
        $this->assertSame('https://api.test.com/v2/product-item', $event->url);
        $this->assertSame(['product_slug' => 'free-fire'], $event->params);
        $this->assertSame(200, $event->statusCode);
        $this->assertSame($body, $event->response);
        $this->assertNull($event->error);
        $this->assertTrue($event->successful());
        $this->assertGreaterThanOrEqual(0, $event->durationMs);
    }

    public function testDispatchesOnErrorResponseAndStillThrows(): void
    {
        $body = ['code' => 400, 'message' => 'Saldo tidak mencukupi'];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(400, $body),
        ]), $this->events());

        try {
            $vexaGame->createTransaction('FF5', '123');
            $this->fail('Expected VexaGameException');
        } catch (VexaGameException $e) {
            $this->assertSame('Saldo tidak mencukupi', $e->getMessage());
        }

        $this->assertCount(1, $this->dispatched);
        $event = $this->dispatched[0];
        $this->assertSame('POST', $event->method);
        $this->assertSame(400, $event->statusCode);
        $this->assertSame($body, $event->response);
        $this->assertSame('Saldo tidak mencukupi', $event->error);
        $this->assertFalse($event->successful());
    }

    public function testDispatchesOnConnectionFailureWithoutStatus(): void
    {
        $vexaGame = $this->createVexaGame(new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', 'v2/me')),
        ]), $this->events());

        try {
            $vexaGame->getProfile();
            $this->fail('Expected VexaGameException');
        } catch (VexaGameException $e) {
        }

        $this->assertCount(1, $this->dispatched);
        $this->assertNull($this->dispatched[0]->statusCode);
        $this->assertNull($this->dispatched[0]->response);
        $this->assertSame('Connection timed out', $this->dispatched[0]->error);
    }

    public function testRedactsPin(): void
    {
        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, ['code' => 200, 'payload' => []]),
        ]), $this->events());

        $vexaGame->createTransaction('FF5', '123', pin: '123456');

        $this->assertSame('[REDACTED]', $this->dispatched[0]->params['pin']);
        $this->assertSame('FF5', $this->dispatched[0]->params['code']);
    }

    public function testFailingListenerDoesNotFailTheCall(): void
    {
        $events = new Dispatcher();
        $events->listen(RequestCompleted::class, function () {
            throw new \RuntimeException('queue down');
        });

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, ['code' => 200, 'payload' => ['id' => 1]]),
        ]), $events);

        $result = $vexaGame->createTransaction('FF5', '123');

        $this->assertSame(1, $result['payload']['id']);
    }

    public function testNoDispatcherIsANoOp(): void
    {
        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, ['code' => 200]),
        ]));

        $this->assertSame(['code' => 200], $vexaGame->getProfile());
    }
}
