<script setup>
import FlashGrowl from '@/components/FlashGrowl.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

onMounted(() => {
    document.body.classList.remove('kt-sidebar-fixed', 'kt-header-fixed');

    const initMetronic = () => {
        if (typeof KTComponents !== 'undefined') {
            KTComponents.init();
        }
        if (typeof KTLayout !== 'undefined') {
            KTLayout.init();
        }
    };

    initMetronic();

    router.on('success', () => {
        initMetronic();
    });
});
</script>

<template>
    <FlashGrowl />
    <div class="grid lg:grid-cols-2 grow w-full min-h-[100dvh]">
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <slot />
        </div>
        <div
            class="lg:rounded-xl lg:border lg:border-border lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-panel hidden lg:flex flex-col"
        >
            <div class="flex flex-col p-8 lg:p-16 gap-4">
                <Link href="/">
                    <img class="h-[28px] max-w-none" src="@assets/media/app/default-logo.svg" alt="E-Learning" />
                </Link>
                <div class="flex flex-col gap-3">
                    <h3 class="text-2xl font-semibold text-mono">
                        Learn without limits
                    </h3>
                    <p class="text-base font-medium text-secondary-foreground">
                        Browse courses, learn at your pace, and track progress —
                        <span class="text-mono font-semibold">free to start</span>
                        on our student portal.
                    </p>
                </div>
                <Link href="/" class="text-sm link mt-2">
                    Back to home
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.branded-panel {
    background-image: url('@assets/media/images/2600x1600/1.png');
}
:global(.dark) .branded-panel {
    background-image: url('@assets/media/images/2600x1600/1-dark.png');
}
</style>
