/**
 * Metronic KTModal can leave stray backdrops when toggles target missing DOM nodes
 * (e.g. #search_modal). Close orphans after Inertia navigations.
 */
export function cleanupMetronicModals() {
    document.querySelectorAll('.kt-modal-backdrop').forEach((element) => {
        element.remove();
    });

    document.body.classList.remove('kt-modal-open', 'overflow-hidden');

    document.querySelectorAll('.kt-modal.open').forEach((element) => {
        if (element.closest('[data-confirm-modal]')) {
            return;
        }

        element.classList.remove('open');
    });

    if (typeof KTModal !== 'undefined') {
        document.querySelectorAll('[data-kt-modal="true"].open').forEach((element) => {
            const instance = KTModal.getInstance(element);

            if (instance) {
                instance.hide();
            } else {
                element.classList.remove('open');
            }
        });
    }
}
