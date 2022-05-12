<?php

namespace TBCD\Tests\MessengerExtension;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use TBCD\MessengerExtension\Middleware\LimiterMiddleware;
use TBCD\MessengerExtension\Stamp\LimitedStamp;
use TBCD\MessengerExtension\Stamp\LimiterStamp;

class LimiterMiddlewareTest extends TestCase
{

    /**
     * @return void
     */
    public function test(): void
    {
        $middleware = new LimiterMiddleware(new ArrayAdapter(), new NullLogger());

        $message = new DummyMessage();
        $envelope = new Envelope($message, [new LimiterStamp(new DateInterval("PT30M"))]);
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