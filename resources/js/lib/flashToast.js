import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

export function initializeFlashToast() {
    let lastToastKey = null;
    let lastToastAt = 0;

    const showToast = (data) => {
        if (!data?.type || !data?.message) return;

        const key = `${data.type}:${data.message}`;
        const now = Date.now();

        // Prevent duplicate toasts when both Inertia flash and success fire.
        if (lastToastKey === key && now - lastToastAt < 1000) return;

        lastToastKey = key;
        lastToastAt = now;

        const method = typeof toast[data.type] === 'function' ? data.type : 'message';
        toast[method](data.message);
    };

    // Inertia::flash event (new API)
    router.on('flash', (event) => {
        const flash = event.detail?.flash;
        const data = flash?.toast;
        showToast(data);
    });

    // Fallback for shared session flash props on successful visits/forms.
    router.on('success', (event) => {
        const data = event.detail?.page?.props?.flash?.toast;
        showToast(data);
    });
}
