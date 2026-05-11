<?php

namespace App\Constants;

class Permission
{
    public const ORDER = [
        'SELECT'   => 'Xem đơn hàng',
        'CREATE' => 'Tạo đơn hàng',
        'UPDATE'   => 'Sửa đơn hàng',
        'DELETE' => 'Xóa đơn hàng',
    ];

    public const BOOKING = [
        'SELECT'   => 'Xem lịch thiết kế',
        'CREATE' => 'Tạo lịch thiết kế',
        'UPDATE'   => 'Sửa lịch thiết kế',
        'DELETE' => 'Xóa lịch thiết kế',
    ];

    public const SHIPMENT = [
        'SELECT' => 'Xem đơn vận chuyển',
        'CREATE' => 'Tạo đơn vận chuyển',
        'UPDATE'   => 'Sửa đơn vận chuyển',
        'DELETE' => 'Xóa đơn vận chuyển',
    ];
}
