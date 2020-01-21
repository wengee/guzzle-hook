<?php declare(strict_types=1);
/**
 * This file is part of guzzle hook plugin.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-21 16:01:14 +0800
 */

namespace GuzzleHttp;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\StreamHandler;

class DefaultHandler
{
    protected static $handler;

    protected static $handlers = [
        'stream'    => StreamHandler::class,
        'curl'      => CurlHandler::class,
        'curlMulti' => CurlMultiHandler::class,
    ];

    public static function set($handler = null): void
    {
        if (is_string($handler)) {
            $handler = self::$handlers[$handler] ?? $handler;
            if (class_exists($handler)) {
                self::$handler = new $handler;
            }
        } else {
            self::$handler = $handler;
        }
    }

    public static function get()
    {
        if (!isset(self::$handler)) {
            self::set('stream');
        }

        return self::$handler;
    }
}
