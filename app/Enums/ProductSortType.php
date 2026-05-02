<?php

namespace App\Enums;

enum ProductSortType: string
{
    case POPULARITY = 'popularity';
    case HIGH_LOW = 'high-low';
    case LOW_HIGH = 'low-high';
    case NEWEST = 'newest';
}
