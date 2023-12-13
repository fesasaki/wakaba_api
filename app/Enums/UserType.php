<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserType extends Enum
{
    const MASTER = 100;
    const ADMIN = 1;
    const USER = 0;
}

