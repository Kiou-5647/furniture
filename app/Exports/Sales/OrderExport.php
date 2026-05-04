<?php

namespace App\Exports\Sales;

use App\Data\Sales\OrderFilterData;
use App\Models\Sales\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrderExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    use Exportable;

    public function __construct(
        private OrderFilterData $filter
    ) {}

    public function query()
    {
        return Order::query()
            ->with(['acceptedBy', 'storeLocation'])
            ->when($this->filter->customer_id, fn($q) => $q->byCustomerId($this->filter->customer_id))
            ->when($this->filter->status, fn($q) => $q->byStatus($this->filter->status))
            ->when($this->filter->source, fn($q) => $q->bySource($this->filter->source))
            ->when($this->filter->store_location_id, fn($q) => $q->byStoreLocation($this->filter->store_location_id))
            ->when($this->filter->search, fn($q) => $q->search($this->filter->search))
            ->orderBy($this->filter->order_by ?? 'created_at', $this->filter->order_direction ?? 'desc');
    }

    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Ngày tạo',
            'Tổng tiền',
            'Trạng thái',
            'Nguồn',
            'Phương thức thanh toán',
            'Thời gian thanh toán',
            'Tỉnh/Thành phố',
            'Tên khách hàng',
            'Số điện thoại',
            'Email',
            'Địa chỉ',
            'Ghi chú',
            'Nhân viên thực hiện',
            'Vị trí cửa hàng',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('d/m/Y H:i'),
            $order->total_amount,
            $order->status->label(),
            match ($order->source) {
                'online' => 'Trực tuyến',
                'in_store' => 'Tại quầy',
                default => $order->source,
            },
            $order->payment_method?->label() ?? '—',
            $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : 'Chưa thanh toán',
            $order->province_name ?? '—',
            $order->guest_name ?? 'Khách vãng lai',
            $order->guest_phone ?? 'Khách vãng lai',
            $order->guest_email ?? 'Khách vãng lai',
            $order->getShippingAddressText(),
            $order->notes,
            $order->acceptedBy?->full_name ?? '—',
            $order->source === 'in_store' ? $order->storeLocation?->name : '—',
        ];
    }

    public function styles($sheet)
    {
        // Style the header row (Row 1)
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // 1. General alignment
                $sheet->getStyle('A2:O' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // 2. Zebra striping
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F9FAFB'],
                            ],
                        ]);
                    }
                }

                // 3. Column-specific formatting
                // Column C: Total Amount - Right aligned and number format #,##0
                $sheet->getStyle('C2:C' . $highestRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'numberFormat' => ['formatCode' => '#,##0'],
                ]);

                // Column B: Date - Centered
                $sheet->getStyle('B2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Column D, E, F: Status, Source, Payment Method - Centered
                $sheet->getStyle('D2:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Column G: Paid At - Centered
                $sheet->getStyle('G2:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 4. Conditional Formatting for "Chưa thanh toán" (Red text in Column G)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('G' . $row)->getValue();
                    if ($cellValue === 'Chưa thanh toán') {
                        $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    }
                }

                // 5. Enable AutoFilter for the header row
                $sheet->setAutoFilter('A1:O1');
            },
        ];
    }
}
