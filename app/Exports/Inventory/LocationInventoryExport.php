<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\Location;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LocationInventoryExport implements WithEvents, ShouldAutoSize
{
    public function __construct(
        private Location $location
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Clear existing content
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->removeRow($row->getRowIndex(), 1);
                }

                // 1. Header Information Section
                $sheet->setCellValue('A1', 'BÁO CÁO TỒN KHO CHI TIẾT');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color('2C3E50'));
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A2', 'Vị trí: ' . $this->location->name);
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A3', 'Mã kho: ' . $this->location->code);
                $sheet->mergeCells('A3:E3');
                $sheet->setCellValue('A4', 'Địa chỉ: ' . $this->location->getFullAddress());
                $sheet->mergeCells('A4:E4');
                $sheet->setCellValue('A5', 'Ngày xuất: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A5:E5');

                $sheet->getStyle('A2:A5')->getFont()->setItalic(true)->setColor(new Color('7F8C8D'));
                $sheet->getStyle('A2:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // 2. Table Headers
                $headers = ['Sản phẩm', 'SKU', 'Số lượng', 'Giá vốn (Đơn vị)', 'Tổng giá trị'];
                $sheet->fromArray($headers, null, 'A6');

                // 3. Fetch and Write Data
                $inventories = \App\Models\Inventory\Inventory::query()
                    ->join('product_variants', 'inventories.variant_id', '=', 'product_variants.id')
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->where('inventories.location_id', $this->location->id)
                    ->select('inventories.*')
                    ->orderBy('products.name')
                    ->with(['variant.product'])
                    ->get();

                $data = $inventories->map(function ($inventory) {
                    $variant = $inventory->variant;
                    $product = $variant->product;
                    return [
                        $product->name . ' ' . $variant->name,
                        $variant->sku,
                        $inventory->quantity,
                        (float)$inventory->cost_per_unit,
                        (float)($inventory->quantity * $inventory->cost_per_unit),
                    ];
                })->toArray();

                if (!empty($data)) {
                    $sheet->fromArray($data, null, 'A7');
                }

                // 4. Professional Styling
                $highestRow = $sheet->getHighestRow();

                // Table Header Style (Row 6)
                $sheet->getStyle('A6:E6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
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

                if ($highestRow >= 7) {
                    // General vertical alignment for data rows
                    $sheet->getStyle('A7:F' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    // Zebra striping for data rows
                    for ($row = 7; $row <= $highestRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F9FAFB'],
                                ],
                            ]);
                        }
                    }

                    /**
                     *  Column-specific formatting
                     **/
                    // Col D: SKU ,Quantity - Center
                    $sheet->getStyle('B7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Col E & F: Cost & Total Value - Right aligned, Thousand separator, 0 decimals
                    $sheet->getStyle('D7:E' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'numberFormat' => ['formatCode' => '#,##0'],
                    ]);

                    // Borders for the entire table
                    $sheet->getStyle('A6:E' . $highestRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    // Enable AutoFilter for the table header
                    $sheet->setAutoFilter('A6:E6');

                    // Explicitly AutoSize each column to ensure it works after styling
                    foreach (range('A', 'E') as $columnID) {
                        $sheet->getColumnDimension($columnID)->setAutoSize(true);
                    }
                }
            },
        ];
    }
}
