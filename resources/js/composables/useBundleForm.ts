import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { index, store, update } from '@/routes/employee/bundles';
import type { Bundle } from '@/types/bundle';

export function useBundleForm(initialBundle: Bundle | null) {
    const form = ref({
        id: initialBundle?.id ?? null,
        name: initialBundle?.name ?? '',
        slug: initialBundle?.slug ?? '',
        description: initialBundle?.description ?? '',
        discount_type: initialBundle?.discount_type ?? 'percentage',
        discount_value: initialBundle?.discount_value ?? 0,
        is_active: initialBundle?.is_active ?? true,

        primary_image_file: null as File | null,
        primary_image_url: initialBundle?.images?.primary ?? null,
        hover_image_file: null as File | null,
        hover_image_url: initialBundle?.images?.hover ?? null,

        contents: initialBundle?.contents ?? [],
    });

    const bundlePricing = computed(() => {
        let individualTotal = 0;

        // Calculate total based on the lowest variant price for each card in the bundle
        form.value.contents.forEach(item => {
            const variants = item.product_card?.variants || [];
            if (variants.length > 0) {
                const minPrice = Math.min(...variants.map(v => Number(v.sale_price ?? v.price)));
                individualTotal += minPrice * item.quantity;
            }
        });

        const discountType = form.value.discount_type;
        const discountVal = form.value.discount_value;
        let finalPrice = individualTotal;

        if (discountType === 'percentage') {
            finalPrice = individualTotal * (1 - discountVal / 100);
        } else if (discountType === 'fixed_amount') {
            finalPrice = individualTotal - discountVal;
        } else if (discountType === 'fixed_price') {
            finalPrice = discountVal;
        }

        return {
            individualTotal,
            finalPrice: Math.max(0, finalPrice),
            savings: individualTotal - finalPrice
        };
    });

    const isValid = computed(() => {
        return (
            form.value.name.trim() !== '' &&
            form.value.slug.trim() !== '' &&
            form.value.contents.length > 0 &&
            form.value.contents.every(c => c.product_card?.id && c.quantity > 0)
        );
    });

    function addCard(cardData: any) {
        const cardId = cardData.id;

        if (form.value.contents.some(c => c.product_card?.id === cardId)) {
            return;
        }

        form.value.contents.push({
            id: crypto.randomUUID(),
            quantity: 1,
            product_card: cardData,
        });
    }

    function removeCard(index: number) {
        form.value.contents.splice(index, 1);
    }

    function setPrimaryImage(file: File | null) {
        form.value.primary_image_file = file;
        if (file) form.value.primary_image_url = null;
    }

    function setHoverImage(file: File | null) {
        form.value.hover_image_file = file;
        if (file) form.value.hover_image_url = null;
    }

    function submit() {
        console.info(form.value)
        const payload = {
            ...form.value,
            contents: form.value.contents.map(c => ({
                product_card_id: c.product_card.id,
                quantity: c.quantity,
            })),
        };

        if (initialBundle) {
            router.put(update({ bundle: initialBundle }).url, payload, {
                onSuccess: () => router.visit(index()),
                onError: (errors) => console.error(errors),
                forceFormData: true,
                preserveScroll: true
            });
        } else {
            router.post(store().url, payload, {
                onSuccess: () => router.visit(index()),
                onError: (errors) => console.error(errors),
                preserveScroll: true
            });
        }
    }

    return {
        form, isValid, bundlePricing, addCard, removeCard, submit,
        setPrimaryImage, setHoverImage
    };
}
