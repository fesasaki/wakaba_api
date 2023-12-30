<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class EventStatus extends Enum
{
    const ONGOING = 100;
    const DONE = 200;
    const CANCELED = 400;
}

