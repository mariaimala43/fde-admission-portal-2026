<?php

namespace App\Helpers;

class SchoolClassHelper
{
    // Returns class order numbers allowed for each school type
    public static function allowedClassOrders(string $type): array
    {
        return match($type) {
            'I-V'          => [1, 2, 3, 4, 5],
            'I-VIII'       => [1, 2, 3, 4, 5, 6, 7, 8],
            'I-X'          => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            'I-XII'        => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'VI-VIII'      => [6, 7, 8],
            'VI-X'         => [6, 7, 8, 9, 10],
            'VI-XII'       => [6, 7, 8, 9, 10, 11, 12],
            'Model College'=> [11, 12],
            default        => [1, 2, 3, 4, 5],
        };
    }
}
