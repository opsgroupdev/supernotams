<?php

namespace App\Enum;

enum NotamStatus: int
{
    case UNTAGGED = 0;
    case PROCESSING = 1;
    case TAGGED = 2;
    case ERROR = 9;
}
