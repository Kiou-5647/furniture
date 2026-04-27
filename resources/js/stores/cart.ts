import { router } from '@inertiajs/vue3';
import axios from 'axios'
import { reactive, computed } from 'vue'
import { show as showBundle } from '@/routes/bundles';
import { data as cartData, destroy, update, store } from '@/routes/cart'
import { show as showProduct } from '@/routes/products';

export interface CartVariant {
    id: string;
    name: string;
    label: string | null;
    sku: string;
    slug: string;
    price: number;
    sale_price: number | null;
    quantity: number;
    image_url: string | null;
}

export interface CartItem {
    id: string;
    name: string;
    sku: string;
    slug: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
    image_url: string | null;
    purchasable_type: string;
    configuration: Record<string, string> | null;
    selected_variants?: CartVariant[]; // Added for Bundle breakdowns
}

const state = reactive({
    items: [] as CartItem[],
    totals: {
        item_count: 0,
        subtotal: 0,
        discount: 0,
        total: 0,
    },
    isOpen: false,
    isLoading: false,
})

export const useCartStore = () => {
    const openDrawer = () => (state.isOpen = true)
    const closeDrawer = () => (state.isOpen = false)

    const fetchCart = async () => {
        state.isLoading = true
        try {
            const { data } = await axios.get(cartData().url)
            state.items = data.items
            state.totals = data.totals
        } catch (e) {
            console.error("Failed to fetch cart", e)
        } finally {
            state.isLoading = false
        }
    }

    const visitItemPage = (purchasable_type: string, sku: string | null, slug: string | null) => {
        state.isOpen = false;
        if (purchasable_type == 'App\\Models\\Product\\Bundle')
        {
            router.visit(showBundle(slug!).url)
        } else if (purchasable_type == 'App\\Models\\Product\\ProductVariant') {
            router.visit(showProduct({sku: sku!, variant_slug: slug!}).url)
        }
    }

    const addToCart = async (payload: {
        purchasable_id: string,
        purchasable_type: string,
        quantity: number,
        configuration?: Record<string, string>
    }) => {
        state.isLoading = true
        try {
            const { data } = await axios.post(store().url, payload);
            await fetchCart();
            state.isOpen = true;
            return { success: true, item: data.item };
        } catch (e) {
            console.error("Failed to add to cart", e);
            return { success: false, reason: 'error' };
        } finally {
            state.isLoading = false
        }
    }

    const updateItemQuantity = async (itemId: string, quantity: number) => {
        try {
            await axios.patch(update(itemId).url, { quantity })
            await fetchCart()
        } catch (e) {
            console.error("Failed to update quantity", e)
        }
    }

    const removeItem = async (itemId: string) => {
        try {
            await axios.delete(destroy(itemId).url)
            await fetchCart()
        } catch (e) {
            console.error("Failed to remove item", e)
        }
    }

    return {
        state,
        openDrawer,
        closeDrawer,
        fetchCart,
        visitItemPage,
        addToCart,
        updateItemQuantity,
        removeItem,
        itemCount: computed(() => state.totals.item_count),
        drawerTotal: computed(() => state.totals.total),
        checkoutBreakdown: computed(() => ({
            subtotal: state.totals.subtotal,
            discount: state.totals.discount || 0,
            shipping: 0,
            finalTotal: state.totals.total
        })),
    }
}
