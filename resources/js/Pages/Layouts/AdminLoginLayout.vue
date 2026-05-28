<script setup>
import FlashGrowl from '@/components/FlashGrowl.vue';
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

onMounted(() => {
    // Remove dashboard-specific classes
    document.body.classList.remove('demo1', 'kt-sidebar-fixed', 'kt-header-fixed');
    
    // Initialize Metronic components when layout mounts
    const initMetronic = () => {
        if (typeof KTComponents !== 'undefined') {
            KTComponents.init();
        }
        if (typeof KTLayout !== 'undefined') {
            KTLayout.init();
        }
    };

    // Run on first load
    initMetronic();

    // Re-initialize after every Inertia page navigation
    router.on('success', () => {
        initMetronic();
    });
});
</script>

<template>
    <FlashGrowl />
    <div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
        <slot />
    </div>
</template>

<style>
.page-bg {
    background-image: url('@assets/media/images/2600x1200/bg-10.png');
}
.dark .page-bg {
    background-image: url('@assets/media/images/2600x1200/bg-10-dark.png');
}
</style>