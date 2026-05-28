const VARIANT_MAP = {
    success: 'success',
    error: 'destructive',
    warning: 'warning',
    info: 'info',
};

/**
 * Show Metronic KTToast notifications.
 */
export function useGrowl() {
    const show = (message, variant = 'info', options = {}) => {
        if (typeof KTToast === 'undefined') {
            console.warn('KTToast is not loaded.', message);

            return;
        }

        const toastVariant = VARIANT_MAP[variant] ?? 'info';
        const config = {
            message: message,
            variant: toastVariant,
            position: 'top-end',
            progress: true,
            appearance: 'solid',
        };

        if (options.position) {
            config.position = options.position;
        }

        if (options.duration) {
            config.duration = options.duration;
        }

        KTToast.show(config);
    };

    const success = (message, options = {}) => show(message, 'success', options);
    const error = (message, options = {}) => show(message, 'error', options);
    const warning = (message, options = {}) => show(message, 'warning', options);
    const info = (message, options = {}) => show(message, 'info', options);

    return {
        show: show,
        success: success,
        error: error,
        warning: warning,
        info: info,
    };
}
