<script setup>
import FlashGrowl from '@/components/FlashGrowl.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

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
    <div class="flex flex-col grow w-full min-h-[100dvh]">
        <header class="border-b border-border bg-background/95 backdrop-blur-sm sticky top-0 z-20">
            <div class="kt-container-fixed flex items-center justify-between gap-4 py-4 lg:py-5">
                <Link href="/" class="flex items-center gap-2 shrink-0">
                    <img class="h-[22px] dark:hidden" src="@assets/media/app/default-logo.svg" alt="" />
                    <img class="h-[22px] hidden dark:block" src="@assets/media/app/default-logo-dark.svg" alt="" />
                </Link>
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <Link class="text-secondary-foreground hover:text-primary" href="/courses">
                        Courses
                    </Link>
                    <Link
                        v-if="user"
                        class="text-secondary-foreground hover:text-primary"
                        href="/my-learning"
                    >
                        My learning
                    </Link>
                    <a class="text-secondary-foreground hover:text-primary" href="/#features">
                        Features
                    </a>
                </nav>
                <div class="flex items-center gap-2.5">
                    <template v-if="user">
                        <Link href="/my-learning" class="kt-btn kt-btn-sm kt-btn-ghost hidden sm:inline-flex">
                            My learning
                        </Link>
                        <span class="text-sm text-secondary-foreground hidden lg:inline">
                            Hi, {{ user.name }}
                        </span>
                        <Link href="/logout" method="post" as="button" type="button" class="kt-btn kt-btn-sm kt-btn-outline">
                            Sign out
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="kt-btn kt-btn-sm kt-btn-outline">
                            Sign in
                        </Link>
                        <Link href="/register" class="kt-btn kt-btn-sm kt-btn-primary">
                            Get started
                        </Link>
                    </template>
                </div>
            </div>
        </header>
        <main class="grow flex flex-col">
            <slot />
        </main>
        <footer class="border-t border-border bg-background">
            <div class="kt-container-fixed py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-secondary-foreground">
                <span>© {{ new Date().getFullYear() }} E-Learning</span>
                <div class="flex items-center gap-4">
                    <Link href="/login" class="link">
                        Student sign in
                    </Link>
                    <a class="link" :href="page.props.urls.admin_login" rel="noopener">
                        Staff portal
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
