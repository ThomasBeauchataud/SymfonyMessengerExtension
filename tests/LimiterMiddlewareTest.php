<?php

namespace TBCD\Tests\MessengerExtension;

use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Test\Middleware\MiddlewareTestCase;
use TBCD\MessengerExtension\Middleware\LimiterMiddleware;
use TBCD\MessengerExtension\Stamp\LimitedStamp;

class LimiterMiddlewareTest extends MiddlewareTestCase
{

    /**
     * @return void
     */
    public function test(): void
    {
        $middleware = new LimiterMiddleware(new ArrayAdapter(), new NullLogger());

        $message = new LimitedMessageTest();
        $envelope = new Envelope($message);
        $stack = new StackMiddleware();

        $firstReturn = $middleware->handle($envelope, $stack);

        $limitedStamp = $firstReturn->last(LimitedStamp::class);
        $this->assertInstanceOf(LimitedStamp::class, $limitedStamp);
        $this->assertFalse($limitedStamp->hasBeenLimited());

        $secondReturn = $middleware->handle($envelope, $stack);

        $limitedStamp = $secondReturn->last(LimitedStamp::class);
        $this->assertInstanceOf(LimitedStamp::class, $limitedStamp);
        $this->assertTrue($limitedStamp->hasBeenLimited());
    }

}