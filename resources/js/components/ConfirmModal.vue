<script setup>
import { useConfirmModal } from '@/composables/useConfirmModal';
import { onMounted, onUnmounted, watch } from 'vue';

const modal = useConfirmModal();

const onEscape = (event) => {
    if (event.key === 'Escape' && modal.state.isOpen) {
        modal.handleCancel();
    }
};

watch(
    () => modal.state.isOpen,
    (isOpen) => {
        if (isOpen) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    }
);

onMounted(() => {
    document.addEventListener('keydown', onEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', onEscape);
    document.body.classList.remove('overflow-hidden');
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="modal.state.isOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'confirm-modal-title'"
        >
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-[1px]"
                aria-hidden="true"
                @click="modal.handleCancel"
            />
            <div class="kt-modal open flex w-full max-w-[440px] relative z-10">
                <div class="kt-modal-content w-full max-w-[440px] top-auto mx-auto shadow-xl">
                    <div class="kt-modal-header">
                        <h3 id="confirm-modal-title" class="kt-modal-title text-mono">
                            {{ modal.state.title }}
                        </h3>
                        <button
                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0"
                            type="button"
                            aria-label="Close"
                            @click="modal.handleCancel"
                        >
                            <i class="ki-filled ki-cross" />
                        </button>
                    </div>
                    <div class="kt-modal-body">
                        <p class="text-sm text-secondary-foreground leading-relaxed">
                            {{ modal.state.message }}
                        </p>
                    </div>
                    <div class="kt-modal-footer flex justify-end gap-2.5 border-t border-border px-5 py-4">
                        <button
                            class="kt-btn kt-btn-outline"
                            type="button"
                            @click="modal.handleCancel"
                        >
                            {{ modal.state.cancelLabel }}
                        </button>
                        <button
                            class="kt-btn"
                            :class="modal.state.variant === 'danger' ? 'kt-btn-destructive' : 'kt-btn-primary'"
                            type="button"
                            @click="modal.handleConfirm"
                        >
                            {{ modal.state.confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
