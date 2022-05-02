<?php

namespace TBCD\Tests\MessengerExtension;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use TBCD\MessengerExtension\Middleware\DependenciesMiddleware;
use TBCD\MessengerExtension\Stamp\DependenciesStamp;

class DependenciesMiddlewareTest extends TestCase
{

    /**
     * @return void
     */
    public function test(): void
    {
        $middleware = new DependenciesMiddleware(new NullBus(), new NullLogger());

        $message = new DependentMessageTest();
        $envelope = new Envelope($message);
        $stack = new StackMiddleware();

        $firstReturn = $middleware->handle($envelope, $stack);

        $dependenciesStamp = $firstReturn->last(DependenciesStamp::class);
        $this->assertInstanceOf(DependenciesStamp::class, $dependenciesStamp);

        $message = new LimitedMessageTest();
        $envelope = new Envelope($message);

        $secondReturn = $middleware->handle($envelope, $stack);

        $dependenciesStamp = $secondReturn->last(DependenciesStamp::class);
        $this->assertNull($dependenciesStamp);
    }

}