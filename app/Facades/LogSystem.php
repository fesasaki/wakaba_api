<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\LogSystem logWithHistory(string $action, stdClass $old_data, ParameterBag $new_data)
 * @method static \App\Models\LogSystem info(string $action, integer $user = null)
 * @method static \App\Models\LogSystem warn(string $action, integer $user = null)
 * @method static \App\Models\LogSystem error(string $action, integer $user = null)
 * @method static \App\Models\LogSystem critical(string $action, integer $user = null)
 */

class LogSystem extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'log_system';
    }
}
