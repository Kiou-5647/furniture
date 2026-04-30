import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import 'vue-sonner/style.css';
import { toast } from 'vue-sonner';
import { initializeTheme } from '@/composables/useAppearance';
import Sonner from './components/ui/sonner/Sonner.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function getErrorMessage(error: unknown): string {
    if (error instanceof Error) return error.message;
    if (typeof error === 'string') return error;
    return 'An unexpected error occurred.';
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // 2. Change the render function to return a wrapper containing BOTH the App and Sonner
        const app = createApp({
            render: () => h('div', [
                h(App, props), // The main Inertia application
                h(Sonner, { position: 'top-center'})      // The global toast anchor (mounted once at the root)
            ])
        });

        app.config.errorHandler = (err, instance, info) => {
            console.error('Global Vue Error:', err, info);
            toast.error('Application Error', {
                description: getErrorMessage(err),
            });
        };

        app.use(plugin).mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

router.on('success', (event) => {
    // The page object is passed in the event argument
    const page = event.detail.page;
    const flash = page.props.flash as any;

    if (flash) {
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
        if (flash.info) toast.info(flash.info);
        if (flash.warning) toast.warning(flash.warning);
    }
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);

    toast.error('Async Error', {
        description: getErrorMessage(event.reason),
    });
});

window.onerror = function (message, source, lineno, colno, error) {
    console.error('Window Error:', message, source, lineno, colno, error);

    toast.error('Runtime Error', {
        description: getErrorMessage(error || message),
    });

    return false;
};

// This will set light / dark mode on page load...
initializeTheme();

// Automatically detect and sync the user's timezone
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
if (timezone) {
    document.cookie = `user_timezone=${timezone}; path=/; max-age=31536000; samesite=lax`;
}
