<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\File;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    protected BarcodeGeneratorPNG $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorPNG;
    }

    /**
     * Generate barcode as base64 PNG.
     *
     * @param  string  $sku  The SKU to encode
     * @return string Base64 encoded PNG image
     */
    public function generate(string $sku): string
    {
        $png = $this->generator->getBarcode($sku, BarcodeGeneratorPNG::TYPE_CODE_128, 2, 50);

        return base64_encode($png);
    }

    /**
     * Generate and save barcode to disk.
     *
     * @param  string  $sku  The SKU to encode
     * @param  string  $path  The path to save the barcode
     * @return string The saved file path
     */
    public function generateAndSave(string $sku, string $path): string
    {
        $png = $this->generator->getBarcode($sku, BarcodeGeneratorPNG::TYPE_CODE_128, 2, 50);

        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, recursive: true);
        }

        File::put($path, $png);

        return $path;
    }

    /**
     * Get barcode URL/path for a SKU.
     *
     * @param  string  $sku  The SKU
     * @return string The barcode path
     */
    public function getBarcodePath(string $sku): string
    {
        return "barcodes/{$sku}.png";
    }
}
