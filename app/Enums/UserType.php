<?php

namespace App\Enums;

enum UserType: string
{
    case Employee = 'employee';
    case Vendor = 'vendor';
    case Customer = 'customer';
}
