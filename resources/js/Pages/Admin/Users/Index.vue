<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, required: true },
    roles: { type: Array, required: true },
});

const search = ref(props.filters.search || '');

const searchUsers = () => {
    router.get('/users', { search: search.value }, {
        preserveState: true,
        replace: true,
    });
};

const updateRole = (user, event) => {
    const role = event.target.value;

    router.patch('/users/' + user.id, { role }, { preserveScroll: true });
};

const roleLabel = (role) => role.replace('_', ' ');

const primaryRole = (user) => (user.roles.length > 0 ? user.roles[0] : 'student');
</script>

<script>
import AdminLayout from '@/Pages/Layouts/AdminDashboardLayout.vue';

export default {
    layout: AdminLayout,
};
</script>

<template>
    <Head title="Users" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-mono">
                    Users
                </h1>
                <p class="text-sm text-secondary-foreground">
                    Manage platform roles. Each user has one primary role.
                </p>
            </div>
            <form class="flex gap-2" @submit.prevent="searchUsers">
                <input
                    v-model="search"
                    type="search"
                    class="kt-input w-56"
                    placeholder="Search name or email…"
                />
                <button type="submit" class="kt-btn kt-btn-outline">
                    Search
                </button>
            </form>
        </div>

        <div class="kt-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium">
                                Name
                            </th>
                            <th class="text-left px-5 py-3 font-medium">
                                Email
                            </th>
                            <th class="text-left px-5 py-3 font-medium">
                                Role
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-b border-border last:border-0"
                        >
                            <td class="px-5 py-3 font-medium text-mono">
                                {{ user.name }}
                            </td>
                            <td class="px-5 py-3 text-secondary-foreground">
                                {{ user.email }}
                            </td>
                            <td class="px-5 py-3">
                                <select
                                    :value="primaryRole(user)"
                                    class="kt-input kt-input-sm min-w-36 capitalize"
                                    @change="updateRole(user, $event)"
                                >
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ roleLabel(role) }}
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
