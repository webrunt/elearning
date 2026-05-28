<script setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useGrowl } from '@/composables/useGrowl';

const page = usePage();
const growl = useGrowl();

const flashKeys = ['success', 'error', 'warning', 'info'];

const showFlashMessages = (flash) => {
    if (!flash) {
        return;
    }

    flashKeys.forEach((key) => {
        const message = flash[key];

        if (message) {
            growl.show(message, key);
        }
    });
};

watch(
    () => page.props.flash,
    (flash) => {
        showFlashMessages(flash);
    },
    { immediate: true, deep: true }
);
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
