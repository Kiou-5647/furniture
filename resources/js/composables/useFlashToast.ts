import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

export function useFlashToast() {
    const page = usePage();

    // Watch the flash prop for changes
    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return;

            if (flash.success) toast.success(flash.success);
            if (flash.error) toast.error(flash.error);
            if (flash.info) toast.info(flash.info);
            if (flash.warning) toast.warning(flash.warning);
        },
        { deep: true, immediate: true }
    );
}
