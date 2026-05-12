<?php

namespace App\Constants;

class Permission
{
    public const PERMISSION = [
        'GRANT' => 'Gán quyền hạn',
    ];

    public const ORDER = [
        'SELECT' => 'Xem đơn hàng',
        'CREATE' => 'Tạo đơn hàng',
        'UPDATE' => 'Sửa đơn hàng',
        'DELETE' => 'Xóa đơn hàng',
    ];

    public const BOOKING = [
        'SELECT' => 'Xem lịch thiết kế',
        'CREATE' => 'Tạo lịch thiết kế',
        'UPDATE' => 'Sửa lịch thiết kế',
        'DELETE' => 'Xóa lịch thiết kế',
    ];

    public const SHIPMENT = [
        'SELECT' => 'Xem đơn vận chuyển',
        'CREATE' => 'Tạo đơn vận chuyển',
        'UPDATE' => 'Sửa đơn vận chuyển',
        'DELETE' => 'Xóa đơn vận chuyển',
    ];

    public const CATEGORY = [
        'SELECT' => 'Xem danh mục',
        'CREATE' => 'Tạo danh mục',
        'UPDATE' => 'Sửa danh mục',
        'DELETE' => 'Xóa danh mục',
    ];

    public const COLLECTION = [
        'SELECT' => 'Xem bộ sưu tập',
        'CREATE' => 'Tạo bộ sưu tập',
        'UPDATE' => 'Sửa bộ sưu tập',
        'DELETE' => 'Xóa bộ sưu tập',
    ];

    public const PRODUCT = [
        'SELECT' => 'Xem sản phẩm',
        'CREATE' => 'Tạo sản phẩm',
        'UPDATE' => 'Sửa sản phẩm',
        'DELETE' => 'Xóa sản phẩm',
    ];

    public const BUNDLE = [
        'SELECT' => 'Xem gói sản phẩm',
        'CREATE' => 'Tạo gói sản phẩm',
        'UPDATE' => 'Sửa gói sản phẩm',
        'DELETE' => 'Xóa gói sản phẩm',
    ];

    public const DEPARTMENT = [
        'SELECT' => 'Xem phòng ban',
        'CREATE' => 'Tạo phòng ban',
        'UPDATE' => 'Sửa phòng ban',
        'DELETE' => 'Xóa phòng ban',
    ];

    public const DESIGNER = [
        'SELECT' => 'Xem nhà thiết kế',
        'CREATE' => 'Tạo nhà thiết kế',
        'UPDATE' => 'Sửa nhà thiết kế',
        'DELETE' => 'Xóa nhà thiết kế',
    ];

    public const DISCOUNT = [
        'SELECT' => 'Xem khuyến mãi',
        'CREATE' => 'Tạo khuyến mãi',
        'UPDATE' => 'Sửa khuyến mãi',
        'DELETE' => 'Xóa khuyến mãi',
    ];

    public const EMPLOYEE = [
        'SELECT' => 'Xem nhân viên',
        'CREATE' => 'Tạo nhân viên',
        'UPDATE' => 'Sửa nhân viên',
        'DELETE' => 'Xóa nhân viên',
    ];

    public const INVOICE = [
        'SELECT' => 'Xem hóa đơn',
        'CREATE' => 'Tạo hóa đơn',
        'UPDATE' => 'Sửa hóa đơn',
        'DELETE' => 'Xóa hóa đơn',
    ];

    public const LOOKUP = [
        'SELECT' => 'Xem tra cứu',
        'CREATE' => 'Tạo tra cứu',
        'UPDATE' => 'Sửa tra cứu',
        'DELETE' => 'Xóa tra cứu',
    ];
}
