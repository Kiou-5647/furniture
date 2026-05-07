<script setup lang="ts">
import heroImage from '@images/4787421-interior-2685521.jpg';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Truck,
    ShieldCheck,
    Headphones,
    RotateCcw,
} from '@lucide/vue';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import ShopLayout from '@/layouts/ShopLayout.vue';
import ProductSection from '@/layouts/storefront/ProductSection.vue';
import { formatPrice } from '@/lib/utils';
import { index } from '@/routes/customer/bookings';

const props = defineProps<{
    rooms: {
        id: string;
        label: string;
        slug: string;
        image_url: string | null;
    }[];
    sections: Record<
        string,
        {
            title: string;
            cards: any[];
            moreUrl: string;
        }
    >;
}>();

const page = usePage();
const settings = page.props.settings;

const features = [
    {
        icon: Truck,
        title: 'Miễn phí vận chuyển',
        desc: `Cho đơn hàng trên ${formatPrice(settings.freeship_threshold)}`,
    },
    {
        icon: ShieldCheck,
        title: `Bảo hành tối thiểu ${settings.default_warranty} tháng`,
        desc: 'Cam kết chất lượng',
    },
    { icon: Headphones, title: 'Hỗ trợ 24/7', desc: 'Tư vấn miễn phí' },
    { icon: RotateCcw, title: 'Đổi trả dễ dàng', desc: 'Trong 30 ngày' },
];

/**
 * Map the section keys from the backend to specific UI layouts.
 * This keeps the Welcome.vue clean and allows the backend
 * to control the data, while the frontend controls the presentation.
 */
const getLayoutForKey = (key: string) => {
    const layoutMap: Record<string, 'grid' | 'carousel'> = {
        topSellers: 'carousel',
        newArrivals: 'grid',
        allProducts: 'grid',
    };
    return layoutMap[key] || 'grid';
};

const getLayoutPropsForKey = (key: string) => {
    const propsMap: Record<string, any> = {
        newArrivals: { cols: 4 },
        allProducts: { cols: 4 },
        topSellers: {},
    };
    return propsMap[key] || {};
};
</script>

<template>
    <Head title="Nội Thất - Kiến tạo không gian sống" />
    <ShopLayout>
        <!-- Hero Section: Immersive & Aspirational -->
        <section
            class="relative h-[90vh] min-h-[600px] overflow-hidden select-none"
        >
            <div class="absolute inset-0">
                <img
                    :src="heroImage"
                    alt="Nội thất sang trọng"
                    class="animate-slow-zoom h-full w-full scale-105 object-cover"
                />
                <!-- Dynamic Gradient: Deeper on the left for text readability, fading to transparency -->
                <div
                    class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"
                />
            </div>
            <div
                class="relative mx-auto flex h-full max-w-7xl items-center px-4 sm:px-6 lg:px-8"
            >
                <div class="animate-fade-in-up max-w-2xl space-y-6">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/20 px-3 py-1 text-[10px] font-bold tracking-widest text-amber-400 uppercase"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"
                            ></span>
                        </span>
                        Bộ sưu tập 2026
                    </div>
                    <h1
                        class="text-4xl leading-[1.1] font-bold tracking-tight text-white sm:text-6xl lg:text-7xl"
                    >
                        Kiến tạo không gian sống <br />
                        <span
                            class="bg-gradient-to-r from-amber-200 to-amber-500 bg-clip-text text-transparent"
                            >tinh tế</span
                        >
                    </h1>
                    <p class="max-w-lg text-base leading-relaxed text-zinc-300">
                        Khám phá bộ sưu tập nội thất hiện đại, thiết kế độc
                        quyền từ những thương hiệu hàng đầu. Biến ngôi nhà của
                        bạn thành không gian sống lý tưởng.
                    </p>
                    <div class="flex flex-wrap gap-4 pt-4">
                        <Link
                            href="/san-pham"
                            class="group relative inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-sm font-bold text-zinc-900 transition-all hover:scale-105 hover:bg-amber-400"
                        >
                            Khám phá ngay
                            <ArrowRight
                                class="h-4 w-4 transition-transform group-hover:translate-x-1"
                            />
                        </Link>
                        <Link
                            href="/san-pham?sale=1"
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 px-8 py-4 text-sm font-semibold text-white backdrop-blur-md transition-all hover:border-white/40 hover:bg-white/10"
                        >
                            Khuyến mãi
                        </Link>
                    </div>
                </div>
            </div>
            <!-- Scroll Indicator -->
            <div
                class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce opacity-50"
            >
                <div
                    class="flex h-9 w-5 justify-center rounded-full border-2 border-white p-1"
                >
                    <div
                        class="animate-scroll-dot h-2 w-1 rounded-full bg-white"
                    ></div>
                </div>
            </div>
        </section>

        <!-- Features Bar: Trust Ribbon -->
        <section class="border-b bg-white">
            <div
                class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8"
            >
                <div
                    v-for="f in features"
                    :key="f.title"
                    class="group flex items-start gap-4 transition-all hover:translate-y-[-4px]"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-50 text-zinc-600 transition-colors group-hover:bg-amber-400 group-hover:text-white"
                    >
                        <component :is="f.icon" class="h-6 w-6" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-zinc-900">
                            {{ f.title }}
                        </p>
                        <p class="text-xs leading-relaxed text-zinc-500">
                            {{ f.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rooms Section: Aspirational Gallery -->
        <section
            class="mx-auto max-w-7xl px-4 py-24 select-none sm:px-6 lg:px-8"
        >
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2
                    class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl"
                >
                    Khám phá theo không gian
                </h2>
                <div
                    class="mx-auto mt-3 h-1 w-12 rounded-full bg-amber-400"
                ></div>
                <p class="mt-4 text-sm text-zinc-500">
                    Tìm kiếm nội thất phù hợp cho từng phòng trong ngôi nhà của
                    bạn
                </p>
            </div>

            <Carousel :opts="{ align: 'start', loop: true }" class="w-full">
                <CarouselContent class="-ml-4">
                    <CarouselItem
                        v-for="room in rooms"
                        :key="room.id"
                        class="basis-1/2 pl-4 sm:basis-1/3 lg:basis-1/4"
                    >
                        <Link
                            :href="`/san-pham?phong=${room.slug}`"
                            class="group relative block overflow-hidden rounded-2xl bg-zinc-100"
                        >
                            <div
                                class="relative aspect-[3/4] w-full overflow-hidden"
                            >
                                <img
                                    v-if="room.image_url"
                                    :src="room.image_url"
                                    :alt="room.label"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-zinc-200 text-2xl font-bold text-zinc-400"
                                >
                                    {{ room.label.charAt(0) }}
                                </div>

                                <div
                                    class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 via-black/40 to-transparent p-4"
                                >
                                    <h3
                                        class="text-base font-bold text-white transition-colors group-hover:text-amber-400"
                                    >
                                        {{ room.label }}
                                    </h3>
                                </div>
                            </div>
                        </Link>
                    </CarouselItem>
                </CarouselContent>
                <CarouselPrevious class="-left-12 hidden sm:flex" />
                <CarouselNext class="-right-12 hidden sm:flex" />
            </Carousel>
        </section>

        <!-- Dynamic Product Sections -->
        <div class="mx-auto max-w-7xl space-y-32 px-4 py-16 sm:px-6 lg:px-8">
            <ProductSection
                v-for="(section, key) in sections"
                :key="key"
                :title="section.title"
                :cards="section.cards.data"
                :more-url="section.moreUrl"
                :layout="getLayoutForKey(key)"
                :layout-props="getLayoutPropsForKey(key)"
            />
        </div>

        <!-- CTA Banner: Design Studio Experience -->
        <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
            <div
                class="relative overflow-hidden rounded-3xl bg-zinc-900 px-8 py-20 sm:px-16 lg:py-28"
            >
                <div class="absolute inset-0 opacity-20">
                    <div
                        class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-amber-400 blur-3xl"
                    />
                    <div
                        class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-400 blur-3xl"
                    />
                </div>
                <div class="relative mx-auto max-w-3xl space-y-6 text-center">
                    <h2
                        class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl"
                    >
                        Dịch vụ thiết kế nội thất
                    </h2>
                    <p class="text-lg leading-relaxed text-zinc-400">
                        Đội ngũ kiến trúc sư giàu kinh nghiệm sẵn sàng tư vấn và
                        thiết kế không gian sống phù hợp với phong cách riêng
                        của bạn.
                    </p>
                    <Link
                        :href="index().url"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-10 py-4 text-sm font-bold text-zinc-900 shadow-xl transition-all hover:scale-105 hover:bg-amber-400"
                    >
                        Đặt lịch tư vấn ngay
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>
    </ShopLayout>
</template>

<style scoped>
.animate-slow-zoom {
    animation: slowZoom 20s infinite alternate ease-in-out;
}
.animate-fade-in-up {
    animation: fadeInUp 1s ease-out forwards;
}
.animate-scroll-dot {
    animation: scrollDot 2s infinite ease-in-out;
}

@keyframes slowZoom {
    from {
        transform: scale(1);
    }
    to {
        transform: scale(1.1);
    }
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes scrollDot {
    0% {
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        transform: translateY(12px);
        opacity: 0;
    }
}

.space-y-32 > * + * {
    margin-top: 8rem;
}
</style>
