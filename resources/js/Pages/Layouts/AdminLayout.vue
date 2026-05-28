<script setup>
import AdminSidebar from '@/components/Admin/Sidebar.vue';
import AdminHeader from '@/components/Admin/Header.vue';
import AdminFooter from '@/components/Admin/Footer.vue';
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

onMounted(() => {
    // Add dashboard-specific classes
    document.body.classList.add('demo1', 'kt-sidebar-fixed', 'kt-header-fixed');

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
    <AdminSidebar />
    <div class="kt-wrapper flex grow flex-col">
        <AdminHeader />

        <main class="grow pt-5" id="content" role="content">
            <!-- Your dynamic page content will be injected here -->
            <slot />
        </main>

        <AdminFooter />
    </div>
</template>