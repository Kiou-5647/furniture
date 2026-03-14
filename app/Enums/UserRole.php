<?php

namespace App\Enums;

enum UserRole: string
{
    case Employee = 'employee';
    case Vendor = 'vendor';
    case Customer = 'customer';
}
