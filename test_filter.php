<?php

$filter = new \App\Data\Public\ProductCardFilterData(filters: ['mau-sac' => 'den']);
$service = app(\App\Services\Public\StorefrontService::class);
$cards = $service->getProductCards($filter);

if ($cards->isEmpty()) {
    echo "No cards found\n";
} else {
    $card = $cards->first();
    echo "Product: " . ($card['product']['name'] ?? 'Unknown') . "\n";
    echo "Matching Count: " . ($card['matching_variants_count'] ?? 'N/A') . "\n";
    echo "Default Variant ID: " . ($card['default_variant_id'] ?? 'N/A') . "\n";
}
