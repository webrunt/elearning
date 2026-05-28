<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { filterAdminMenuForRoles } from '@/config/adminMenu';

const page = usePage();

const menuItems = computed(() => {
    const roles = page.props.auth.user?.roles ?? [];

    return filterAdminMenuForRoles(roles);
});

const isActive = (href) => {
    const url = page.url.split('?')[0];

    if (href === '/dashboard') {
        return url === '/dashboard' || url === '/';
    }

    return url === href;
};
</script>

<template>
    <div
        class="kt-sidebar bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]"
        data-kt-drawer="true"
        data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0"
        id="sidebar"
    >
        <div class="kt-sidebar-header hidden lg:flex items-center relative justify-between px-3 lg:px-6 shrink-0" id="sidebar_header">
            <div class="kt-sidebar-logo min-w-0">
                <Link href="/dashboard" class="dark:hidden">
                    <img class="default-logo min-h-[22px] max-w-none" src="@assets/media/app/default-logo.svg" alt="" />
                    <img class="small-logo min-h-[22px] max-w-none" src="@assets/media/app/mini-logo.svg" alt="" />
                </Link>
                <Link href="/dashboard" class="hidden dark:block">
                    <img class="default-logo min-h-[22px] max-w-none" src="@assets/media/app/default-logo-dark.svg" alt="" />
                    <img class="small-logo min-h-[22px] max-w-none" src="@assets/media/app/mini-logo.svg" alt="" />
                </Link>
            </div>
            <button
                class="kt-btn kt-btn-outline kt-btn-icon size-[30px] absolute start-full top-2/4 z-40 -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
                data-kt-toggle="body"
                data-kt-toggle-class="kt-sidebar-collapse"
                id="sidebar_toggle"
                type="button"
            >
                <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300 rtl:translate rtl:rotate-180 rtl:kt-toggle-active:rotate-0" />
            </button>
        </div>
        <div class="kt-sidebar-content flex grow shrink-0 py-5 pe-2" id="sidebar_content">
            <div
                class="kt-scrollable-y-hover grow shrink-0 flex ps-2 lg:ps-5 pe-1 lg:pe-3"
                data-kt-scrollable="true"
                data-kt-scrollable-dependencies="#sidebar_header"
                data-kt-scrollable-height="auto"
                data-kt-scrollable-offset="0px"
                data-kt-scrollable-wrappers="#sidebar_content"
                id="sidebar_scrollable"
            >
                <div class="kt-menu flex flex-col grow gap-1" data-kt-menu="true" id="sidebar_menu">
                    <div class="kt-menu-item pt-2.25 pb-px">
                        <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">
                            Platform
                        </span>
                    </div>
                    <div
                        v-for="item in menuItems"
                        :key="item.title + item.href"
                        class="kt-menu-item"
                    >
                        <Link
                            :href="item.href"
                            class="kt-menu-link flex items-center grow border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg"
                            :class="{ 'kt-menu-item-active': isActive(item.href) }"
                        >
                            <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                <i :class="['ki-filled text-lg', item.icon]" />
                            </span>
                            <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                                {{ item.title }}
                            </span>
                        </Link>
                    </div>
                    <div class="kt-menu-item mt-auto pt-4">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            type="button"
                            class="kt-menu-link flex items-center grow border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] hover:bg-accent/60 hover:rounded-lg w-full"
                        >
                            <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                <i class="ki-filled ki-exit-right text-lg" />
                            </span>
                            <span class="kt-menu-title text-sm font-medium text-foreground">
                                Sign out
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
