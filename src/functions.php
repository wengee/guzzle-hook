<?php declare(strict_types=1);
/**
 * This file is part of guzzle hook plugin.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-21 16:01:16 +0800
 */

namespace GuzzleHttp;

/**
 * Chooses and creates a default handler to use based on the environment.
 *
 * The returned handler is not wrapped by any default middlewares.
 *
 * @throws \RuntimeException if no viable Handler is available.
 * @return callable Returns the best handler for the given system.
 */
function choose_handler()
{
    return DefaultHandler::get();
}
