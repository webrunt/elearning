<script setup>
import { useConfirmModal } from '@/composables/useConfirmModal';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const confirmModal = useConfirmModal();

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const createForm = useForm({
    name: '',
    description: '',
    sort_order: 0,
});

const editingId = ref(null);
const editForm = useForm({
    name: '',
    description: '',
    sort_order: 0,
});

const startEdit = (category) => {
    editingId.value = category.id;
    editForm.name = category.name;
    editForm.description = category.description || '';
    editForm.sort_order = category.sort_order;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const submitCreate = () => {
    createForm.post('/categories', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const submitEdit = () => {
    editForm.put('/categories/' + editingId.value, {
        preserveScroll: true,
        onSuccess: () => cancelEdit(),
    });
};

const deleteCategory = async (category) => {
    const confirmed = await confirmModal.confirm({
        title: 'Delete category',
        message: 'Delete "' + category.name + '"? You cannot delete categories that still have courses.',
        confirmLabel: 'Delete',
        cancelLabel: 'Cancel',
        variant: 'danger',
    });

    if (!confirmed) {
        return;
    }

    router.delete('/categories/' + category.id, {
        preserveScroll: true,
    });
};
</script>

<script>
import AdminLayout from '@/Pages/Layouts/AdminDashboardLayout.vue';

export default {
    layout: AdminLayout,
};
</script>

<template>
    <Head title="Categories" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-mono">
                    Categories
                </h1>
                <p class="text-sm text-secondary-foreground">
                    Organize courses by topic.
                </p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="kt-card">
                <div class="kt-card-header px-5 py-4">
                    <h3 class="kt-card-title text-sm font-semibold">
                        Add category
                    </h3>
                </div>
                <form class="kt-card-content p-5 flex flex-col gap-4" @submit.prevent="submitCreate">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Name</label>
                        <input v-model="createForm.name" class="kt-input" type="text" required />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Description</label>
                        <textarea v-model="createForm.description" class="kt-input" rows="3" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Sort order</label>
                        <input v-model.number="createForm.sort_order" class="kt-input" type="number" min="0" />
                    </div>
                    <button class="kt-btn kt-btn-primary" type="submit" :disabled="createForm.processing">
                        Create
                    </button>
                </form>
            </div>

            <div class="kt-card">
                <div class="kt-card-header px-5 py-4">
                    <h3 class="kt-card-title text-sm font-semibold">
                        All categories
                    </h3>
                </div>
                <div class="kt-card-content p-0">
                    <div
                        v-for="category in categories"
                        :key="category.id"
                        class="border-b border-border last:border-0 p-5"
                    >
                        <template v-if="editingId === category.id">
                            <form class="flex flex-col gap-3" @submit.prevent="submitEdit">
                                <input v-model="editForm.name" class="kt-input" type="text" required />
                                <textarea v-model="editForm.description" class="kt-input" rows="2" />
                                <input v-model.number="editForm.sort_order" class="kt-input" type="number" min="0" />
                                <div class="flex gap-2">
                                    <button class="kt-btn kt-btn-sm kt-btn-primary" type="submit">
                                        Save
                                    </button>
                                    <button class="kt-btn kt-btn-sm kt-btn-outline" type="button" @click="cancelEdit">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </template>
                        <template v-else>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-mono">
                                        {{ category.name }}
                                    </p>
                                    <p v-if="category.description" class="text-sm text-secondary-foreground mt-1">
                                        {{ category.description }}
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ category.slug }}
                                    </p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button class="kt-btn kt-btn-sm kt-btn-outline" type="button" @click="startEdit(category)">
                                        Edit
                                    </button>
                                    <button class="kt-btn kt-btn-sm kt-btn-outline text-destructive" type="button" @click="deleteCategory(category)">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p v-if="categories.length === 0" class="p-5 text-sm text-secondary-foreground">
                        No categories yet.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
