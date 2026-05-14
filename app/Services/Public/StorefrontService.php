<?php

namespace App\Services\Public;

use App\Data\Public\ProductCardFilterData;
use App\Enums\ProductSortType;
use App\Models\Product\Bundle;
use App\Models\Product\ProductCard;
use App\Models\Product\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class StorefrontService
{
    /**
     * Tính tổng số lượng biến thể sản phẩm thỏa mãn các điều kiện lọc.
     */
    public function getTotalCount(ProductCardFilterData $filter): int
    {
        $pool = $this->fetchStorefrontPool($filter);

        $count = $pool->filter(function ($variant) use ($filter) {
            if ($filter->min_price !== null && $variant->effective_price < $filter->min_price) return false;
            if ($filter->max_price !== null && $variant->effective_price > $filter->max_price) return false;

            foreach ($filter->filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];
                if (!$this->isVariantSatisfiedInPool($namespace, $slugArray, $variant)) {
                    return false;
                }
            }
            return true;
        })->count();

        return $count;
    }

    /**
     * Lấy danh sách sản phẩm có thể mua, chèn ngẫu nhiên các bundle vào danh sách
     */
    public function getPurchasables(ProductCardFilterData $filter): LengthAwarePaginator
    {
        // 1. Gọi hàm getProductCards để lấy danh sách sản phẩm bình thường đã lọc và phân trang
        $paginatedProducts = $this->getProductCards($filter);
        // Lấy ra mảng các sản phẩm từ đối tượng phân trang
        $products = $paginatedProducts->items();

        // 2. Lấy danh sách tất cả các Bundle đang hoạt động
        $bundles = Bundle::query()
            ->where('is_active', true)
            ->get()
            ->map(fn(Bundle $bundle) => [
                'type' => 'bundle',
                'id' => $bundle->id,
                'name' => $bundle->name,
                'slug' => $bundle->slug,
                'price' => $bundle->calculateBundlePrice(),
                'images' => [
                    'primary' => $bundle->getFirstMediaUrl('primary_image', 'webp'),
                    'hover' => $bundle->getFirstMediaUrl('hover_image', 'webp')
                ],
            ]);

        // 3. Nếu không có bundle nào thì trả về danh sách sản phẩm bình thường
        if ($bundles->isEmpty()) {
            return $paginatedProducts;
        }

        // 4. Logic chèn bundle vào danh sách sản phẩm
        $result = collect();
        $bundleIndex = 0;
        // Tạo một khoảng cách ngẫu nhiên từ 3 đến 10 sản phẩm thì chèn 1 combo
        $injectionInterval = rand(3, 10);

        foreach ($products as $index => $product) {
            // Thêm sản phẩm bình thường vào kết quả
            $result->push($product);

            // Kiểm tra: Nếu đã đạt đến khoảng cách chèn VÀ vẫn còn combo để chèn
            if (($index + 1) % $injectionInterval === 0 && $bundleIndex < $bundles->count()) {
                // Chèn combo vào vị trí này
                $result->push($bundles[$bundleIndex]);
                $bundleIndex++; // Chuyển sang combo tiếp theo cho lần chèn sau
            }
        }

        // 5. Tạo lại đối tượng Paginator để giữ nguyên thông tin về tổng số trang, trang hiện tại và URL.
        return new LengthAwarePaginator(
            $result->values(),
            $paginatedProducts->total(),
            $paginatedProducts->perPage(),
            $paginatedProducts->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Lấy về các thẻ sản phẩm thỏa mãn filter, sắp xếp và chuyển thành pagination.
     */
    public function getProductCards(ProductCardFilterData $filter): LengthAwarePaginator
    {
        // 1. Lấy ra "pool" (tập hợp) tất cả các biến thể sản phẩm khả dụng từ DB dựa trên tìm kiếm cơ bản
        $pool = $this->fetchStorefrontPool($filter);

        // 2. Lọc ra những biến thể (variants) thỏa mãn TẤT CẢ các bộ lọc đang hoạt động
        $matchingVariantIds = [];
        foreach ($pool as $variant) {
            $isMatch = true;

            // Kiểm tra giá tối thiểu
            if ($filter->min_price !== null && $variant->effective_price < $filter->min_price) {
                $isMatch = false;
            }

            // Kiểm tra giá tối đa
            if ($isMatch && $filter->max_price !== null && $variant->effective_price > $filter->max_price) {
                $isMatch = false;
            }

            // Duyệt qua các lookup_namespace lọc
            foreach ($filter->filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];
                // Nếu biến thể không thỏa mãn bộ lọc, loại bỏ
                if (!$this->isVariantSatisfiedInPool($namespace, $slugArray, $variant)) {
                    $isMatch = false;
                    break;
                }
            }
            // Nếu thỏa mãn bộ lọc thì ghi nhận
            if ($isMatch) {
                $matchingVariantIds[] = $variant->variant_id;
            }
        }

        // 3. Nếu không có biến thể nào khớp -> trả về trang trống ngay lập tức
        $totalVariants = count($matchingVariantIds);
        if ($totalVariants === 0) {
            return new LengthAwarePaginator([], 0, $filter->limit ?? 24);
        }

        // 4. Chuyển đổi từ danh sách biến thể sang danh sách thẻ sản phẩm
        $orderedCardIds = [];
        foreach ($pool as $variant) {
            if (in_array($variant->variant_id, $matchingVariantIds)) {
                $cardId = $variant->product_card_id;

                // Chỉ thêm vào danh sách nếu thẻ sản phẩm này chưa xuất hiện
                if ($cardId && !in_array($cardId, $orderedCardIds)) {
                    $orderedCardIds[] = $cardId;
                }
            }
        }

        // 5. Xử lý pagination cho danh sách ID thẻ sản phẩm
        $totalCards = count($orderedCardIds);
        $limit = $filter->limit > 0 ? $filter->limit : 24;
        $offset = ($filter->page - 1) * $limit;

        // Cắt lấy đúng số lượng ID cho trang hiện tại
        $slicedCardIds = array_slice($orderedCardIds, $offset, $limit);

        if (empty($slicedCardIds)) {
            return new LengthAwarePaginator([], 0, $filter->limit ?? 24);
        }

        // Chuyển mảng thành Collection và làm phẳng mảng.
        $flatCardIds = collect($slicedCardIds)->flatten()->toArray();

        // 6. Truy vấn database để lấy chi tiết các ProductCard dựa trên ID đã lọc
        $cards = ProductCard::query()
            ->whereIn('id', $flatCardIds)
            ->with(['product', 'variants', 'options'])
            ->get();

        // Sắp xếp lại các card theo đúng thứ tự ID đã xuất hiện trong $slicedCardIds
        $cards = $cards->sortBy(fn($card) => array_search($card->id, $slicedCardIds));

        // 7. Xử lý Sắp xếp (Sorting) theo yêu cầu của người dùng
        if ($filter->type === ProductSortType::HIGH_LOW || $filter->type === ProductSortType::LOW_HIGH) {
            // Sắp xếp theo giá: Tìm giá thấp nhất/cao nhất trong các biến thể của card đó để làm chuẩn
            $cards = $cards->sortBy(function ($card) use ($filter) {
                $prices = $card->variants->map(fn($v) => $v->getEffectivePrice())->toArray();
                $price = empty($prices) ? 0 : ($filter->type === ProductSortType::HIGH_LOW ? max($prices) : min($prices));
                // So sánh giá trị âm nếu type === HIGH_LOW để các sản phẩm giá cao sẽ ở vị trí đầu tiên
                return $filter->type === ProductSortType::HIGH_LOW ? -$price : $price;
            });
        } elseif ($filter->type === ProductSortType::NEWEST) {
            // Sắp xếp theo hàng mới về: Ưu tiên flag is_new_arrival trước, sau đó đến ngày xuất bản
            $cards = $cards->sortByDesc(function ($card) {
                return [
                    $card->product->is_new_arrival ? 1 : 0,
                    $card->product->published_date
                ];
            });
        } elseif ($filter->type === ProductSortType::POPULARITY) {
            // Sắp xếp theo độ phổ biến: Dựa vào số lượng đơn hàng (sales_count)
            $cards = $cards->sortByDesc('sales_count');
        }

        // 8. Gắn thêm dữ liệu chi tiết cho mỗi card
        $finalCollection = $cards->map(fn(ProductCard $card) => $this->attachMatchingData($card, $filter))
            ->filter()
            ->values();

        // 9. Trả về đối tượng phân trang hoàn chỉnh
        return new LengthAwarePaginator(
            $finalCollection,
            $totalCards,
            $filter->limit,
            $filter->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Gắn thêm dữ liệu chi tiết cho mỗi ProductCard bao gồm danh sách
     * các biến thể khớp với bộ lọc và thông tin swatch.
     */
    private function attachMatchingData(ProductCard $card, ProductCardFilterData $filterData): array
    {
        $filters = $filterData->filters;
        $product = $card->product;

        // 1. Lọc ra những biến thể của Card này thực sự thỏa mãn bộ lọc hiện tại
        $matchingVariants = $card->variants->filter(function ($variant) use ($filters, $filterData) {

            if ($filterData->min_price !== null && $variant->getEffectivePrice() < $filterData->min_price) return false;
            if ($filterData->max_price !== null && $variant->getEffectivePrice() > $filterData->max_price) return false;

            // Tạo một object tạm thời để chuẩn hóa dữ liệu cho isVariantSatisfiedInPool làm việc thống nhất
            $variantObj = (object) [
                'price' => $variant->price,
                'effective_price' => $variant->getEffectivePrice(),
                'option_values' => $variant->option_values,
                'features' => $variant->features,
                'specifications' => $variant->specifications,
                'prod_features' => $variant->product->features,
                'prod_specifications' => $variant->product->specifications,
                'category_id' => $variant->product->category_id,
                'category_slug' => $variant->product->category?->slug,
                'collection_id' => $variant->product->collection_id,
                'collection_slug' => $variant->product->collection?->slug,
                'filterable_options' => $variant->product->filterable_options,
            ];

            // Duyệt qua các namespace lọc để kiểm tra
            foreach ($filters as $namespace => $slugs) {
                $slugArray = is_array($slugs) ? $slugs : [$slugs];
                if (!$this->isVariantSatisfiedInPool($namespace, $slugArray, $variantObj)) {
                    return false;
                }
            }
            return true;
        });

        // 2. Nếu không có biến thể nào trong Card này khớp với bộ lọc -> Loại bỏ toàn bộ Card này
        if ($matchingVariants->isEmpty()) {
            return [];
        }

        // 3. Xác định "Biến thể mặc định" để hiển thị trên Card
        // Ưu tiên chọn biến thể đang có giá giảm (Sale) đầu tiên
        $saleVariant = $matchingVariants->first(fn($v) => $v->getEffectivePrice() < $v->price);

        // 4. Trả về mảng dữ liệu cuối cùng cho Frontend
        return [
            'type' => 'product',
            'id' => $card->id,
            'matching_variants_count' => $matchingVariants->count(),
            'matched_variant_ids' => $matchingVariants->pluck('id')->toArray(),
            // Nếu có biến thể sale thì lấy, không thì lấy biến thể đầu tiên khớp
            'default_variant_id' => $saleVariant ? $saleVariant->id : $matchingVariants->first()?->id,
            'product' => [
                'name' => $product->name,
                'is_new_arrival' => $product->is_new_arrival,
            ],
            'metrics' => [
                'sales_count' => $card->sales_count,
                'average_rating' => $card->average_rating,
                'reviews_count' => $card->reviews_count,
            ],
            // Xây dựng danh sách Swatches
            'swatches' => $card->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'slug' => $v->slug,
                'price' => $v->price,
                'sale_price' => $v->getEffectivePrice(),
                'primary_image_url' => $v->getFirstMediaUrl('primary_image', 'webp') ?? $v->getFirstMediaUrl('primary_image'),
                'hover_image_url' => $v->getFirstMediaUrl('hover_image', 'webp') ?? $v->getFirstMediaUrl('hover_image'),
                'swatch_image_url' => $v->getFirstMediaUrl('swatch_image', 'swatch') ?? $v->getFirstMediaUrl('swatch_image') ?? null,
                'name' => $v->name,
                'label' => $v->swatch_label,
                'is_available' => $v->isInStock(),
            ]),
        ];
    }

    /**
     * Tạo bản tóm tắt cho các bộ lọc tính toán số lượng sản phẩm cho từng tùy chọn lọc để hiển thị ở sidebar
     */
    public function getFilterSummary(ProductCardFilterData $filter): array
    {
        // 1. Tạo một mã hash duy nhất dựa trên các bộ lọc hiện tại để làm cache key
        $filterHash = md5(json_encode($filter->filters));
        $cacheKey = "summary:{$filterHash}";

        // Sử dụng Cache với tag để có thể xóa toàn bộ cache bộ lọc khi sản phẩm thay đổi
        return \Illuminate\Support\Facades\Cache::tags([\App\Support\CacheTag::CategoryFilters->value])
            ->remember($cacheKey, now()->addHours(24), function () use ($filter) {

                // 2. Lấy "pool" biến thể sản phẩm
                $pool = $this->fetchStorefrontPool($filter);
                // Lấy tất cả các namespace lọc từ DB trừ nhóm danh mục
                $namespaces = \App\Models\Setting\LookupNamespace::where('slug', '!=', 'nhom-danh-muc')->get();
                $summary = [];

                // Định nghĩa các namespace đặc biệt không nằm trong bảng LookupNamespace
                $special = [
                    'danh-muc' => 'Danh mục',
                    'bo-suu-tap' => 'Bộ sưu tập',
                ];

                $allNamespaceSlugs = $namespaces->pluck('slug')->toArray();
                $allNamespaceLabels = $namespaces->pluck('display_name', 'slug')->toArray();

                // 3. Gộp và duyệt qua từng namespace để tính toán số lượng (count) cho từng option
                foreach (array_merge(array_keys($special), $allNamespaceSlugs) as $nsSlug) {

                    $label = $allNamespaceLabels[$nsSlug] ?? $special[$nsSlug] ?? $nsSlug;

                    // Lấy ra các bộ lọc hiện tại NHƯNG loại trừ chính namespace đang tính toán
                    // Ví dụ: Đang tính cho 'Màu sắc', thì chỉ lọc theo 'Kích thước' và 'Giá'
                    $otherFilters = collect($filter->filters)->except([$nsSlug])->toArray();

                    // Lọc pool biến thể dựa trên các filter KHÁC
                    $filteredPool = $pool->filter(function ($variant) use ($otherFilters) {
                        foreach ($otherFilters as $ns => $slugs) {
                            $slugArray = is_array($slugs) ? $slugs : [$slugs];
                            if (!$this->isVariantSatisfiedInPool($ns, $slugArray, $variant)) {
                                return false;
                            }
                        }
                        return true;
                    });

                    if ($filteredPool->isEmpty()) continue;

                    // 4. Đếm số lần xuất hiện của từng giá trị trong namespace này
                    $counts = [];
                    foreach ($filteredPool as $variant) {
                        $vals = $this->extractValuesFromVariant($nsSlug, $variant);
                        foreach ($vals as $val) {
                            $counts[$val] = ($counts[$val] ?? 0) + 1;
                        }
                    }

                    if (empty($counts)) continue;

                    // 5. Chuyển đổi các mã slug thành nhãn (label) dễ đọc cho người dùng
                    $options = [];
                    if ($nsSlug === 'danh-muc') {
                        $lookups = \App\Models\Product\Category::whereIn('slug', array_keys($counts))->get();
                        foreach ($lookups as $l) $options[] = ['slug' => $l->slug, 'label' => $l->display_name, 'count' => $counts[$l->slug]];
                    } elseif ($nsSlug === 'bo-suu-tap') {
                        $lookups = \App\Models\Product\Collection::whereIn('slug', array_keys($counts))->get();
                        foreach ($lookups as $l) $options[] = ['slug' => $l->slug, 'label' => $l->display_name, 'count' => $counts[$l->slug]];
                    } else {
                        $nsModel = \App\Models\Setting\LookupNamespace::where('slug', $nsSlug)->first();
                        if ($nsModel) {
                            // Chỉ lấy những lookup đang active và có số lượng > 0
                            $options = $nsModel->activeLookups()->get()->map(fn($l) => [
                                'slug' => $l->slug,
                                'label' => $l->display_name,
                                'count' => $counts[$l->slug] ?? 0
                            ])->filter(fn($o) => $o['count'] > 0)->values()->toArray();
                        }
                    }

                    if (!empty($options)) {
                        $summary[] = ['namespace' => $nsSlug, 'label' => $label, 'options' => $options];
                    }
                }

                return $summary;
            });
    }

    /**
     * Kiểm tra một biến thể sản phẩm có thỏa mãn một điều kiện lọc (namespace) cụ thể hay không.
     */
    private function isVariantSatisfiedInPool(string $namespace, array $slugs, $variant): bool
    {
        // 1. Xử lý bộ lọc đặc biệt: 'sale'
        if ($namespace === 'sale') {
            // Nếu người dùng chọn '1' (có giảm giá)
            if (in_array('1', $slugs)) {
                // Kiểm tra xem giá hiện tại (effective_price) có thấp hơn giá gốc (price) không
                return $variant->effective_price < $variant->price;
            }
            return true; // Nếu không lọc sale, coi như thỏa mãn
        }

        // 2. Xử lý bộ lọc 'danh-muc'
        if ($namespace === 'danh-muc') {
            return in_array($variant->category_slug, $slugs);
        }

        // 3. Xử lý bộ lọc 'bo-suu-tap'
        if ($namespace === 'bo-suu-tap') {
            return in_array($variant->collection_slug, $slugs);
        }

        // 4. Xử lý Option Values (Các tùy chọn như Màu sắc, Kích thước được định nghĩa trong option_values)
        // Ví dụ: $variant->option_values['mau-sac'] = 'do'
        if (isset($variant->option_values[$namespace]) && in_array($variant->option_values[$namespace], $slugs)) {
            return true;
        }

        // 5. Xử lý 'tinh-nang'
        if ($namespace === 'tinh-nang') {
            // Gộp tính năng của cả Biến thể và Sản phẩm cha lại thành một mảng
            $allFeatures = array_merge((array)($variant->features ?? []), (array)($variant->prod_features ?? []));
            foreach ($allFeatures as $feat) {
                // Nếu tìm thấy bất kỳ tính năng nào có slug khớp với bộ lọc -> Thỏa mãn
                if (isset($feat['lookup_slug']) && in_array($feat['lookup_slug'], $slugs)) return true;
            }
        } else {
            // 6. Xử lý Thông số kỹ thuật (Specifications)
            $allSpecs = array_merge((array)($variant->specifications ?? []), (array)($variant->prod_specifications ?? []));
            foreach ($allSpecs as $spec) {
                // Kiểm tra xem spec này có thuộc namespace đang lọc VÀ có được đánh dấu là 'có thể lọc' (is_filterable) không
                if (isset($spec['lookup_namespace']) && $spec['lookup_namespace'] === $namespace && ($spec['is_filterable'] ?? false)) {
                    $items = $spec['items'] ?? [];
                    foreach ($items as $item) {
                        // Nếu bất kỳ giá trị nào trong spec khớp với slug được chọn -> Thỏa mãn
                        if (isset($item['lookup_slug']) && in_array($item['lookup_slug'], $slugs)) return true;
                    }
                }
            }
        }

        // Nếu chạy hết tất cả các kiểm tra mà không return true -> Không thỏa mãn bộ lọc
        return false;
    }

    /**
     * Trích xuất tất cả các giá trị (slugs) thuộc về một namespace cụ thể
     * từ một biến thể sản phẩm để phục vụ việc đếm số lượng trong Summary.
     */
    private function extractValuesFromVariant(string $namespace, $variant): array
    {
        // Khởi tạo mảng chứa các giá trị tìm thấy
        $values = [];

        if ($namespace === 'danh-muc') {
            // Nếu là danh mục, lấy slug của danh mục sản phẩm (nếu có)
            if ($variant->category_slug) $values[] = $variant->category_slug;
        } elseif ($namespace === 'bo-suu-tap') {
            // Nếu là bộ sưu tập, lấy slug của bộ sưu tập (nếu có)
            if ($variant->collection_slug) $values[] = $variant->collection_slug;
        } elseif (isset($variant->option_values[$namespace])) {
            // Nếu là một option, lấy giá trị tương ứng trong mảng option_values
            $values[] = $variant->option_values[$namespace];
        } elseif ($namespace === 'tinh-nang') {
            // Nếu là tính năng, gộp tính năng của biến thể và sản phẩm cha
            $allFeatures = array_merge((array)($variant->features ?? []), (array)($variant->prod_features ?? []));
            foreach ($allFeatures as $feat) {
                // Thu thập tất cả các lookup_slug của các tính năng này
                if (isset($feat['lookup_slug'])) $values[] = $feat['lookup_slug'];
            }
        } else {
            // Đối với tất cả các namespace khác
            $allSpecs = array_merge((array)($variant->specifications ?? []), (array)($variant->prod_specifications ?? []));
            foreach ($allSpecs as $spec) {
                // Chỉ lấy giá trị nếu spec này khớp với namespace đang tìm kiếm và có thể lọc (is_filterable)
                if (isset($spec['lookup_namespace']) && $spec['lookup_namespace'] === $namespace && ($spec['is_filterable'] ?? false)) {
                    $items = $spec['items'] ?? [];
                    foreach ($items as $item) {
                        // Thu thập tất cả các lookup_slug có trong spec này
                        if (isset($item['lookup_slug'])) $values[] = $item['lookup_slug'];
                    }
                }
            }
        }

        // Loại bỏ các giá trị trùng lặp trước khi trả về
        return array_unique($values);
    }

    /**
     * Truy vấn database để lấy tập hợp (pool) tất cả biến thể sản phẩm
     * khả dụng, xử lý tìm kiếm Full-text và chuẩn hóa dữ liệu thô.
     */
    private function fetchStorefrontPool(ProductCardFilterData $filter): \Illuminate\Support\Collection
    {
        // Khởi tạo query, join bảng product, categories và collections
        $query = \App\Models\Product\ProductVariant::query()
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('collections', 'products.collection_id', '=', 'collections.id')
            // Chỉ lấy sản phẩm đã xuất bản với biến thể đang active và chưa bị xóa
            ->where('products.status', 'published')
            ->where('product_variants.status', 'active')
            ->whereNull('product_variants.deleted_at');

        // 2. Xử lý tìm kiếm nếu người dùng có nhập từ khóa
        if (!empty($filter->search)) {
            // Tách từ khóa tìm kiếm thành mảng các từ đơn (ví dụ: "bàn gỗ sồi" -> ["bàn", "gỗ", "sồi"])
            $words = preg_split('/\s+/', trim($filter->search), -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    // Sử dụng Regex \y (Word boundary trong PostgreSQL) để tìm kiếm chính xác từ, không bị dính từ con
                    $regex = "\\y" . $word . "\\y";
                    $q->where(function ($sq) use ($regex) {
                        // Tìm kiếm không phân biệt hoa thường (~*) trên tất cả các trường có thể chứa thông tin
                        $sq->whereRaw("products.name ~* ?", [$regex])
                            ->orWhereRaw("categories.display_name ~* ?", [$regex])
                            ->orWhereRaw("collections.display_name ~* ?", [$regex])
                            ->orWhereRaw("product_variants.name ~* ?", [$regex])
                            ->orWhereRaw("product_variants.sku ~* ?", [$regex])
                            ->orWhereRaw("product_variants.description ~* ?", [$regex])
                            ->orWhereRaw("products.features::text ~* ?", [$regex])
                            ->orWhereRaw("products.specifications::text ~* ?", [$regex])
                            ->orWhereRaw("product_variants.features::text ~* ?", [$regex])
                            ->orWhereRaw("product_variants.specifications::text ~* ?", [$regex]);
                    });
                }
            });
        }

        // 3. Chỉ chọn những trường thực sự cần thiết để tối ưu bộ nhớ
        $rawPool = $query->select([
            'product_variants.id as variant_id',
            'product_variants.product_card_id as product_card_id',
            'product_variants.price',
            'product_variants.option_values',
            'product_variants.features',
            'product_variants.specifications',
            'products.id as product_id',
            'categories.slug as category_slug',
            'collections.slug as collection_slug',
            'products.features as prod_features',
            'products.specifications as prod_specifications',
            'products.filterable_options',
        ])->get();

        // 4. Chuyển đổi dữ liệu thô từ DB thành các Object dễ thao tác
        return $rawPool->map(function ($item) {
            // Hàm helper để decode JSON string từ DB thành mảng PHP
            $decode = function ($value) {
                if (is_string($value)) return json_decode($value, true) ?? [];
                return is_array($value) ? $value : [];
            };

            // Lấy model biến thể thực tế để tính toán Effective Price
            $variantModel = ProductVariant::find($item->variant_id);
            if ($variantModel) {
                $effectivePrice = $variantModel->getEffectivePrice();
            }

            // Trả về một object chuẩn hóa để các hàm filter/summary sử dụng
            return (object) [
                'variant_id' => $item->variant_id,
                'product_card_id' => $item->product_card_id,
                'effective_price' => $effectivePrice,
                'price' => $item->price,
                'product_id' => $item->product_id,
                'category_id' => $item->category_id,
                'category_slug' => $item->category_slug,
                'collection_id' => $item->collection_id,
                'collection_slug' => $item->collection_slug,
                'option_values' => $decode($item->option_values),
                'features' => $decode($item->features),
                'specifications' => $decode($item->specifications),
                'prod_features' => $decode($item->prod_features),
                'prod_specifications' => $decode($item->prod_specifications),
                'filterable_options' => $decode($item->filterable_options),
            ];
        });
    }
}
