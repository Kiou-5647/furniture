import axios from 'axios'
import { reactive, computed } from 'vue'
import { data as cartData, destroy, update, store } from '@/routes/cart'

const state = reactive({
    items: [] as any[],
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

    const addToCart = async (payload: { purchasable_id: string, purchasable_type: string, quantity: number }) => {
        state.isLoading = true
        try {
            // API Call to CartController@store
            const { data } = await axios.post(store().url, payload);

            // Sync full state to ensure totals and existing items are correct
            await fetchCart();

            // Auto-open the drawer to show the user the item was added
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
        addToCart,
        updateItemQuantity,
        removeItem,
        itemCount: computed(() => state.totals.item_count),
        drawerTotal: computed(() => state.totals.total),
        checkoutBreakdown: computed(() => ({
            subtotal: state.totals.subtotal,
            discount: state.totals.discount || 0,
            shipping: 0, // Placeholder for future implementation
            finalTotal: state.totals.total
        })),
    }
}
