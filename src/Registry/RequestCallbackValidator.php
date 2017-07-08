<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use Assert\Assert;
use Psr\Http\Message\RequestInterface;
use TRex\Reflection\CallableReflection;

class RequestCallbackValidator
{
    /**
     * @var RequestCallbackValidator
     */
    private static $instance;

    public static function instance(): RequestCallbackValidator
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param callable|null $requestCallback
     *
     * @return callable|null
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __invoke(callable $requestCallback = null)
    {
        if (!$requestCallback) {
            return $requestCallback;
        }

        $reflection = (new CallableReflection($requestCallback))->getReflector();

        Assert::that($reflection->getNumberOfParameters())->greaterOrEqualThan(1);
        Assert::that($reflection->getParameters()[0]->getType())->eq(RequestInterface::class);
        Assert::that($reflection->getReturnType())->eq(RequestInterface::class);

        return $requestCallback;
    }
}
