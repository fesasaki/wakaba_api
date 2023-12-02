<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Notification info(string $subject, string $message, integer $addressee, integer $sender = null)
 * @method static \App\Models\Notification warn(string $subject, string $message, integer $addressee, integer $sender = null)
 * @method static \App\Models\Notification err(string $subject, string $message, integer $addressee, integer $sender = null)
 * @method static \App\Models\Notification success(string $subject, string $message, integer $addressee, integer $sender = null)
 */
class Notification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'system_notification';
    }
}
