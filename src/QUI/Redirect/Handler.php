<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\System\Log;

/**
 * Class Handler
 * @package QUI\Redirect
 */
class Handler
{
    /**
     * Called as an event (onErrorHeaderShowBefore)
     *
     * @param $code
     * @param $url
     */
    public static function onErrorHeaderShow($code, $url)
    {
//        Log::write("Error Header: $code, $url");
    }
}