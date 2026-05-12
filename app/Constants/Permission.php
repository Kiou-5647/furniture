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

    public const PAYMENT = [
        'SELECT' => 'Xem lịch sử thanh toán',
    ];

    public const LOCATION = [
        'SELECT' => 'Xem vị trí',
        'CREATE' => 'Tạo vị trí',
        'UPDATE' => 'Sửa vị trí',
        'DELETE' => 'Xóa vị trí',
    ];

    public const REFUND = [
        'SELECT' => 'Xem hoàn tiền',
        'CREATE' => 'Tạo hoàn tiền',
        'UPDATE' => 'Sửa hoàn tiền',
        'DELETE' => 'Xóa hoàn tiền',
    ];

    public const SHIPPING_METHOD = [
        'SELECT' => 'Xem phương thức vận chuyển',
        'CREATE' => 'Tạo phương thức vận chuyển',
        'UPDATE' => 'Sửa phương thức vận chuyển',
        'DELETE' => 'Xóa phương thức vận chuyển',
    ];

    public const STOCK_MOVEMENT = [
        'SELECT' => 'Xem lịch sử tồn kho',
    ];

    public const STOCK_TRANSFER = [
        'SELECT' => 'Xem chuyển kho',
        'CREATE' => 'Tạo chuyển kho',
        'UPDATE' => 'Sửa chuyển kho',
        'DELETE' => 'Xóa chuyển kho',
    ];

    public const CUSTOMER = [
        'SELECT' => 'Xem khách hàng',
        'CREATE' => 'Tạo khách hàng',
        'UPDATE' => 'Sửa khách hàng',
        'DELETE' => 'Xóa khách hàng',
    ];

    public const VENDOR = [
        'SELECT' => 'Xem nhà cung cấp',
        'CREATE' => 'Tạo nhà cung cấp',
        'UPDATE' => 'Sửa nhà cung cấp',
        'DELETE' => 'Xóa nhà cung cấp',
    ];
}
