import { reactive } from 'vue';

const state = reactive({
    isOpen: false,
    title: 'Confirm',
    message: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    variant: 'primary',
    onConfirm: null,
    onCancel: null,
});

const close = () => {
    state.isOpen = false;
    state.onConfirm = null;
    state.onCancel = null;
};

/**
 * Show a Metronic-styled confirmation modal. Returns a promise that resolves true/false.
 *
 * @param {object} options
 * @param {string} options.title
 * @param {string} options.message
 * @param {string} [options.confirmLabel]
 * @param {string} [options.cancelLabel]
 * @param {'primary'|'danger'} [options.variant]
 * @returns {Promise<boolean>}
 */
export function useConfirmModal() {
    const confirm = (options) => {
        return new Promise((resolve) => {
            state.title = options.title || 'Confirm';
            state.message = options.message || 'Are you sure?';
            state.confirmLabel = options.confirmLabel || 'Confirm';
            state.cancelLabel = options.cancelLabel || 'Cancel';
            state.variant = options.variant || 'primary';
            state.isOpen = true;
            state.onConfirm = () => {
                close();
                resolve(true);
            };
            state.onCancel = () => {
                close();
                resolve(false);
            };
        });
    };

    const handleConfirm = () => {
        if (state.onConfirm) {
            state.onConfirm();
        }
    };

    const handleCancel = () => {
        if (state.onCancel) {
            state.onCancel();
        } else {
            close();
        }
    };

    return {
        state: state,
        confirm: confirm,
        close: close,
        handleConfirm: handleConfirm,
        handleCancel: handleCancel,
    };
}
