<?php

namespace Webmozart\Assert;

class Assert
{
    /**
     * @psalm-assert true $value
     */
    public static function true($value, $message = '') {}


    /**
     * @psalm-assert !null $value
     */
    public static function notNull($value, $message = '') {}


    /**
     * @template T
     * @template-typeof T $type
     * @param class-string $type
     * @psalm-assert T $value
     */
    public static function isInstanceOf($value, $type, $message = '') {}
}
