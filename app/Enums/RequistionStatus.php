<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RequistionStatus extends Enum
{
    const WAITING = 0;
    const APPROVED = 200;
    const CANCELED = 400;
}

