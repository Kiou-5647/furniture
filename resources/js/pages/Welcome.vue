<script setup lang="ts">
import heroImage from '@images/4787421-interior-2685521.jpg';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Truck, ShieldCheck, Headphones, RotateCcw } from '@lucide/vue';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import ShopLayout from '@/layouts/ShopLayout.vue';
import ProductSection from '@/layouts/storefront/ProductSection.vue';

defineProps<{
    rooms: {
        id: string;
        label: string;
        slug: string;
        image_url: string | null;
    }[];
    sections: Record<string, {
        title: string;
        cards: any[];
        moreUrl: string;
    }>;
}>();

const features = [
    { icon: Truck, title: 'Miễn phí vận chuyển', desc: 'Cho đơn hàng trên 2 triệu' },
    { icon: ShieldCheck, title: 'Bảo hành 2 năm', desc: 'Cam kết chất lượng' },
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
        <!-- Hero Section -->
        <section class="relative select-none overflow-hidden">
            <div class="absolute inset-0">
                <img :src="heroImage" alt="Nội thất sang trọng" class="h-full w-full object-cover" />
                <div
                    class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/60 to-black/30 sm:to-transparent" />
            </div>
            <div class="relative mx-auto max-w-7xl px-4 py-32 sm:px-6 lg:px-8 lg:py-40">
                <div class="max-w-xl">
                    <h1 class="text-2xl leading-relaxed font-bold text-white sm:text-5xl lg:text-6xl">
                        Kiến tạo không gian sống <span class="text-amber-400">tinh tế</span>
                    </h1>
                    <p class="mt-6 text-sm leading-relaxed text-white">
                        Khám phá bộ sưu tập nội thất hiện đại, thiết kế độc quyền từ những thương hiệu hàng đầu.
                        Biến ngôi nhà của bạn thành không gian sống lý tưởng.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link href="/san-pham"
                            class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-100 transition-colors">
                            Khám phá ngay
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                        <Link href="/khuyen-mai"
                            class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm hover:bg-white/10 transition-colors">
                            Khuyến mãi
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Bar -->
        <section class="border-b bg-zinc-50">
            <div class="ml-6 grid max-w-7xl grid-cols-2 gap-6 px-4 py-8 sm:mx-auto sm:px-6 lg:grid-cols-4 lg:px-8">
                <div v-for="f in features" :key="f.title" class="flex items-center gap-3">
                    <component :is="f.icon" class="h-5 w-5 shrink-0 text-zinc-500" />
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ f.title }}</p>
                        <p class="text-xs text-zinc-500">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rooms Carousel -->
        <section class="select-none mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10">
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Khám phá theo không gian</h2>
                <p class="mt-2 text-sm text-zinc-500">Tìm kiếm nội thất phù hợp cho từng phòng trong ngôi nhà của bạn
                </p>
            </div>
            <Carousel :opts="{
                align: 'start',
                loop: true
            }" class="w-full">
                <CarouselContent class="-ml-4">
                    <CarouselItem v-for="room in rooms" :key="room.id" class="basis-1/2 sm:basis-1/3 lg:basis-1/4">
                        <Link :href="`/san-pham?phong=${room.slug}`"
                            class="group block overflow-hidden rounded-xl bg-zinc-100">
                            <div class="relative aspect-square overflow-hidden">
                                <img v-if="room.image_url" :src="room.image_url" :alt="room.label"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div v-else class="flex h-full w-full items-center justify-center bg-zinc-200">
                                    <span class="text-2xl font-bold text-zinc-400">{{ room.label.charAt(0) }}</span>
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-80 transition-opacity group-hover:opacity-90" />
                            </div>
                            <div class="relative -mt-24 p-5">
                                <h3 class="text-md font-semibold text-white sm:text-lg">{{ room.label }}</h3>
                            </div>
                        </Link>
                    </CarouselItem>
                </CarouselContent>
                <CarouselPrevious class="left-2 w-6 h-6 sm:w-8 sm:h-8 sm:flex" />
                <CarouselNext class="right-2 w-6 h-6 sm:w-8 sm:h-8 sm:flex" />
            </Carousel>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 space-y-20">
            <ProductSection
                v-for="(section, key) in sections"
                :key="key"
                :title="section.title"
                :cards="section.cards"
                :more-url="section.moreUrl"
                :layout="getLayoutForKey(key)"
                :layout-props="getLayoutPropsForKey(key)"
            />
        </div>

        <!-- CTA Banner -->
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl bg-zinc-900 px-6 py-16 sm:px-12 lg:py-20">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-amber-400 blur-3xl" />
                    <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-400 blur-3xl" />
                </div>
                <div class="relative text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Dịch vụ thiết kế nội thất
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-base text-zinc-400">
                        Đội ngũ kiến trúc sư giàu kinh nghiệm sẵn sàng tư vấn và thiết kế
                        không gian sống phù hợp với phong cách của bạn.
                    </p>
                    <Link href="/dat-lich"
                        class="mt-8 inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-100 transition-colors">
                        Đặt lịch tư vấn
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>
    </ShopLayout>
</template>

<style scoped>
/* Ensure smooth transitions between sections */
.space-y-20 > * + * {
  margin-top: 5rem;
}
</style>
