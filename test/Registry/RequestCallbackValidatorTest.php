<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use FlixTech\SchemaRegistryApi\Registry\RequestCallbackValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestCallbackValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_correct_callbacks(): void
    {
        $callback = static function (RequestInterface $request) { return $request; };
        $this->assertSame($callback, RequestCallbackValidator::instance()($callback));

        $callback = static function (RequestInterface $request): RequestInterface { return $request; };
        $this->assertSame($callback, RequestCallbackValidator::instance()($callback));

        $callback = static function (MyRequestInterface $request): MyRequestInterface { return $request; };
        $this->assertSame($callback, RequestCallbackValidator::instance()($callback));

        $callback = null;
        $this->assertSame($callback, RequestCallbackValidator::instance()($callback));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_fails_for_invalid_parameter_hint(): void
    {
        $callback = static function (InvalidRequestInterface $request) { return $request; };
        RequestCallbackValidator::instance()($callback);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_fails_for_invalid_return_type_hint(): void
    {
        $callback = static function (RequestInterface $request): InvalidRequestInterface {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $request;
        };
        RequestCallbackValidator::instance()($callback);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_fails_for_un_hinted_callable(): void
    {
        $callback = static function ($request) { return $request; };
        RequestCallbackValidator::instance()($callback);
    }
}

interface MyRequestInterface extends RequestInterface
{
}

interface InvalidRequestInterface
{
}
