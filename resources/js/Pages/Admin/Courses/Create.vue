<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, required: true },
    statuses: { type: Object, required: true },
    instructors: { type: Array, required: true },
    can_pick_instructor: { type: Boolean, default: false },
    is_admin: { type: Boolean, default: false },
});

const form = useForm({
    title: '',
    summary: '',
    category_id: '',
    status: 'draft',
    instructor_id: '',
    thumbnail: null,
});

const onThumbnail = (event) => {
    const file = event.target.files[0];
    form.thumbnail = file || null;
};

const submit = () => {
    form.post('/courses', {
        forceFormData: true,
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
    <Head title="New course" />
    <div class="kt-container-fixed py-5 lg:py-7.5 max-w-3xl">
        <div class="mb-6">
            <Link href="/courses" class="text-sm link mb-2 inline-block">
                ← Back to courses
            </Link>
            <h1 class="text-xl font-semibold text-mono">
                New course
            </h1>
        </div>

        <form class="kt-card" @submit.prevent="submit">
            <div class="kt-card-content p-5 lg:p-8 flex flex-col gap-5">
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label">Title</label>
                    <input v-model="form.title" class="kt-input" type="text" required />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label">Summary</label>
                    <textarea v-model="form.summary" class="kt-input" rows="4" />
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Category</label>
                        <select v-model="form.category_id" class="kt-select">
                            <option value="">None</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                    <div v-if="is_admin" class="flex flex-col gap-1">
                        <label class="kt-form-label">Status</label>
                        <select v-model="form.status" class="kt-select" required>
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div v-if="can_pick_instructor" class="flex flex-col gap-1">
                    <label class="kt-form-label">Instructor</label>
                    <select v-model="form.instructor_id" class="kt-select" required>
                        <option value="">Select instructor</option>
                        <option v-for="inst in instructors" :key="inst.id" :value="inst.id">
                            {{ inst.name }} ({{ inst.email }})
                        </option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label">Thumbnail</label>
                    <input class="kt-input" type="file" accept="image/*" @change="onThumbnail" />
                </div>
                <button class="kt-btn kt-btn-primary" type="submit" :disabled="form.processing">
                    Create course
                </button>
            </div>
        </form>
    </div>
</template>
