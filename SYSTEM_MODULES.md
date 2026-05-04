# BÁO CÁO PHÂN TÍCH MODULE HỆ THỐNG (FURNITURE PROJECT)

Hệ thống là một nền tảng quản lý kinh doanh nội thất toàn diện, kết hợp giữa thương mại điện tử (B2C) và quản trị doanh nghiệp nội bộ (ERP). Hệ thống được xây dựng trên kiến trúc hiện đại với Laravel 12, Inertia.js và Vue 3.

## 1. Chi tiết các Module

### 📦 Module Quản lý Sản phẩm (Product Management)
- **Mô tả**: Quản lý toàn bộ danh mục hàng hóa, bao gồm sản phẩm đơn lẻ, biến thể và các gói combo (bundles).
- **Tác nhân**: Admin, Nhân viên (Employee), Khách hàng (Customer).
- **Use-cases**:
    - Quản lý danh mục (Categories) và bộ sưu tập (Collections).
    - Quản lý sản phẩm, biến thể và thẻ sản phẩm (Product Cards).
    - Tạo và quản lý gói combo (Bundles).
    - Tìm kiếm và khám phá sản phẩm (Public Discovery).

### 📉 Module Quản lý Kho (Inventory Management)
- **Mô tả**: Theo dõi số lượng tồn kho, vị trí lưu kho và điều phối hàng hóa giữa các chi nhánh.
- **Tác nhân**: Admin, Nhân viên (Employee).
- **Use-cases**:
    - Quản lý vị trí kho (Locations).
    - Điều chỉnh tồn kho và ghi nhận biến động kho (Stock Movements).
    - Quản lý luân chuyển hàng hóa giữa các kho (Stock Transfers).
    - Nhập kho hàng loạt (Bulk Import).

### 💰 Module Bán hàng & Thanh toán (Sales & Payment)
- **Mô tả**: Xử lý quy trình từ giỏ hàng, đặt hàng, xuất hóa đơn đến thanh toán và hoàn tiền.
- **Tác nhân**: Admin, Nhân viên (Employee), Khách hàng (Customer).
- **Use-cases**:
    - Quản lý giỏ hàng và quy trình thanh toán (Checkout).
    - Xử lý đơn hàng (Order Processing) và cập nhật trạng thái.
    - Quản lý hóa đơn (Invoices) và chiết khấu (Discounts).
    - Tích hợp thanh toán trực tuyến (VnPay) và quản lý hoàn tiền (Refunds).

### 🚚 Module Giao vận (Fulfillment/Shipping)
- **Mô tả**: Điều phối vận chuyển hàng hóa từ kho đến khách hàng.
- **Tác nhân**: Admin, Nhân viên (Employee).
- **Use-cases**:
    - Quản lý phương thức vận chuyển (Shipping Methods).
    - Tạo và quản lý đơn vận chuyển (Shipments).
    - Cập nhật trạng thái giao hàng (Deliver/Ship).
    - Xử lý hàng trả về (Return Shipment Items).

### 📅 Module Đặt lịch (Booking System)
- **Mô tả**: Quản lý lịch hẹn tư vấn thiết kế nội thất với các chuyên gia.
- **Tác nhân**: Admin, Nhà thiết kế (Designer), Khách hàng (Customer).
- **Use-cases**:
    - Đặt lịch hẹn tư vấn (Create Booking).
    - Quản lý khung giờ trống của nhà thiết kế (Designer Availability).
    - Xác nhận hoặc hủy lịch hẹn.
    - Thanh toán tiền đặt cọc (Pay Deposit).

### 👥 Module Quản trị Nhân sự (HR Management)
- **Mô tả**: Quản lý cơ cấu tổ chức, phòng ban và thông tin nhân viên.
- **Tác nhân**: Admin.
- **Use-cases**:
    - Quản lý phòng ban (Departments).
    - Quản lý hồ sơ nhân viên và nhà thiết kế (Employees/Designers).
    - Phân quyền và gán vai trò (Roles & Permissions).

### 👤 Module Khách hàng (Customer Management)
- **Mô tả**: Lưu trữ thông tin khách hàng, lịch sử tương tác và phản hồi.
- **Tác nhân**: Admin, Nhân viên (Employee), Khách hàng (Customer).
- **Use-cases**:
    - Quản lý hồ sơ khách hàng (Customer Profiles).
    - Quản lý đánh giá sản phẩm (Reviews).
    - Theo dõi tổng chi tiêu của khách hàng.

### ⚙️ Module Hệ thống & Cấu hình (Settings & System)
- **Mô tả**: Cấu hình chung của toàn hệ thống, quản lý dữ liệu tra cứu (lookups) và nhật ký hoạt động.
- **Tác nhân**: Admin.
- **Use-cases**:
    - Cấu hình cài đặt chung (General Settings) và giờ làm việc.
    - Quản lý danh mục tra cứu (Lookup Namespaces/Values).
    - Theo dõi nhật ký hệ thống (Activity Logs).
    - Quản lý địa chính (Provinces/Wards).

---

## 2. Tổng kết phân tích
- **Độ phức tạp**: Hệ thống có độ phức tạp trung bình-cao với nhiều luồng nghiệp vụ đan xen (ví dụ: Đơn hàng $\to$ Kho $\to$ Giao vận).
- **Điểm mạnh**: Phân tách rõ ràng giữa `Actions` (Logic nghiệp vụ) và `Controllers` (Điều hướng HTTP), giúp hệ thống dễ bảo trì và mở rộng.
- **Luồng dữ liệu**: Dữ liệu luân chuyển chặt chẽ từ `Public` (Khách hàng) $\to$ `Employee` (Xử lý nội bộ) $\to$ `Admin` (Giám sát/Cấu hình).
