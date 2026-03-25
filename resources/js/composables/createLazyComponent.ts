import type { Component } from 'vue';
import { ref, shallowRef, defineComponent, h, Transition } from 'vue';

/**
 * Creates a lazy-loaded component WITHOUT using Suspense (experimental)
 * Uses manual loading state management with fullscreen overlay
 *
 * @param componentPath - Dynamic import path to the component
 * @returns A wrapper component that handles loading state internally
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * const LazyModal = createLazyComponent(() => import('./Modal.vue'));
 * const showModal = ref(false);
 * </script>
 *
 * <template>
 *   <Dialog :open="showModal">
 *     <LazyModal v-if="showModal" @close="showModal = false" />
 *   </Dialog>
 * </template>
 * ```
 *
 * Shows a professional fullscreen overlay with centered spinner while loading.
 * No experimental Suspense warnings.
 */
export function createLazyComponent<T extends Component>(
    componentPath: () => Promise<{ default: T }>
) {
    return defineComponent({
        name: 'LazyComponentWrapper',
        inheritAttrs: false,
        setup(_, { attrs, emit }) {
            const LoadedComponent = shallowRef<T | null>(null);
            const isLoading = ref(false);
            const showLoader = ref(false);
            const loadError = ref<Error | null>(null);

            // Load component on mount
            isLoading.value = true;

            const loaderTimeout = setTimeout(() => {
                showLoader.value = true;
            }, 200);

            componentPath()
                .then((module) => {
                    LoadedComponent.value = module.default;
                })
                .catch((err) => {
                    loadError.value = err;
                    console.error('Failed to load lazy component:', err);
                })
                .finally(() => {
                    clearTimeout(loaderTimeout);
                    isLoading.value = false;
                });

            // Forward all events to child component
            const emitToChild = (event: string, ...args: any[]) => {
                emit(event, ...args);
            };

            return () => {
                // Loading state: Fullscreen overlay with centered spinner
                if (isLoading.value && showLoader.value) {
                    return h(Transition, {
                        name: 'fade',
                        appear: true
                    }, {
                        default: () => h('div', {
                            class: 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm'
                        }, [
                            h('div', {
                                class: 'flex flex-col items-center gap-4 bg-background p-8 rounded-2xl shadow-2xl border border-border'
                            }, [
                                h('div', {
                                    class: 'relative'
                                }, [
                                    // Outer ring
                                    h('div', {
                                        class: 'w-16 h-16 border-4 border-primary/20 border-t-primary rounded-full animate-spin'
                                    }),
                                    // Inner pulse
                                    h('div', {
                                        class: 'absolute inset-0 flex items-center justify-center'
                                    }, [
                                        h('div', {
                                            class: 'w-8 h-8 bg-primary/10 rounded-full animate-pulse'
                                        })
                                    ])
                                ]),
                                h('span', {
                                    class: 'text-sm font-medium text-muted-foreground animate-pulse'
                                }, 'Đang tải...')
                            ])
                        ])
                    });
                }

                // Error state: Centered card with retry
                if (loadError.value) {
                    return h(Transition, {
                        name: 'fade',
                        appear: true
                    }, {
                        default: () => h('div', {
                            class: 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm'
                        }, [
                            h('div', {
                                class: 'bg-background p-8 rounded-2xl shadow-2xl border border-border max-w-md mx-4 text-center'
                            }, [
                                h('div', {
                                    class: 'w-16 h-16 mx-auto mb-4 rounded-full bg-destructive/10 flex items-center justify-center'
                                }, [
                                    h('svg', {
                                        class: 'w-8 h-8 text-destructive',
                                        viewBox: '0 0 24 24',
                                        fill: 'none',
                                        stroke: 'currentColor',
                                        'stroke-width': '2'
                                    }, [
                                        h('path', { d: 'M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z' })
                                    ])
                                ]),
                                h('h3', {
                                    class: 'text-lg font-semibold mb-2'
                                }, 'Không thể tải thành phần'),
                                h('p', {
                                    class: 'text-sm text-muted-foreground mb-6'
                                }, 'Đã có lỗi xảy ra khi tải. Vui lòng thử lại.'),
                                h('button', {
                                    class: 'px-6 py-2.5 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors',
                                    onClick: () => {
                                        isLoading.value = true;
                                        loadError.value = null;
                                        componentPath()
                                            .then((module) => {
                                                LoadedComponent.value = module.default;
                                            })
                                            .catch((err) => {
                                                loadError.value = err;
                                            })
                                            .finally(() => {
                                                isLoading.value = false;
                                            });
                                    }
                                }, 'Thử lại')
                            ])
                        ])
                    });
                }

                // Render actual component with all props and events
                if (LoadedComponent.value) {
                    return h(LoadedComponent.value, {
                        ...attrs,
                        ...emitToChild
                    });
                }

                // Fallback: empty node
                return h('div');
            };
        }
    });
}
