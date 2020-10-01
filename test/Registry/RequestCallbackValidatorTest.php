<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use FlixTech\SchemaRegistryApi\Registry\RequestCallbackValidator;
use InvalidArgumentException;
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
        self::assertSame($callback, RequestCallbackValidator::instance()($callback));

        $callback = static function (RequestInterface $request): RequestInterface { return $request; };
        self::assertSame($callback, RequestCallbackValidator::instance()($callback));

        $callback = static function (MyRequestInterface $request): MyRequestInterface { return $request; };
        self::assertSame($callback, RequestCallbackValidator::instance()($callback));

        $callback = null;
        self::assertSame($callback, RequestCallbackValidator::instance()($callback));
    }

    /**
     * @test
     */
    public function it_fails_for_invalid_parameter_hint(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $callback = static function (InvalidRequestInterface $request) { return $request; };
        RequestCallbackValidator::instance()($callback);
    }

    /**
     * @test
     */
    public function it_fails_for_invalid_return_type_hint(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $callback = static function (RequestInterface $request): InvalidRequestInterface {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $request;
        };
        RequestCallbackValidator::instance()($callback);
    }

    /**
     * @test
     */
    public function it_fails_for_un_hinted_callable(): void
    {
        $this->expectException(InvalidArgumentException::class);

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
