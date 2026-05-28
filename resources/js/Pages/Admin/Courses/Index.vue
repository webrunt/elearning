<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    courses: { type: Object, required: true },
    filters: { type: Object, required: true },
    categories: { type: Array, required: true },
    statuses: { type: Object, required: true },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const categoryId = ref(props.filters.category_id || '');

const applyFilters = () => {
    router.get('/courses', {
        search: search.value,
        status: status.value,
        category_id: categoryId.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([status, categoryId], applyFilters);

const onSearch = (event) => {
    if (event.key === 'Enter') {
        applyFilters();
    }
};
</script>

<script>
import AdminLayout from '@/Pages/Layouts/AdminDashboardLayout.vue';

export default {
    layout: AdminLayout,
};
</script>

<template>
    <Head title="Courses" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-mono">
                    Courses
                </h1>
                <p class="text-sm text-secondary-foreground">
                    Manage course content and curriculum.
                </p>
            </div>
            <Link href="/courses/create" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus" />
                New course
            </Link>
        </div>

        <div class="kt-card mb-6">
            <div class="kt-card-content p-5 flex flex-wrap gap-4">
                <input
                    v-model="search"
                    class="kt-input max-w-xs"
                    type="search"
                    placeholder="Search courses…"
                    @keydown="onSearch"
                />
                <select v-model="status" class="kt-select w-40">
                    <option value="">All statuses</option>
                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                        {{ label }}
                    </option>
                </select>
                <select v-model="categoryId" class="kt-select w-48">
                    <option value="">All categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
                <button class="kt-btn kt-btn-outline" type="button" @click="applyFilters">
                    Filter
                </button>
            </div>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
            <div v-for="course in courses.data" :key="course.id" class="kt-card">
                <div class="kt-card-content p-0">
                    <div class="h-36 bg-muted/40 flex items-center justify-center overflow-hidden rounded-t-xl">
                        <img
                            v-if="course.thumbnail_url"
                            :src="course.thumbnail_url"
                            :alt="course.title"
                            class="w-full h-full object-cover"
                        />
                        <i v-else class="ki-filled ki-book text-3xl text-muted-foreground" />
                    </div>
                    <div class="p-5">
                        <span class="kt-badge kt-badge-sm kt-badge-outline mb-2">
                            {{ course.status_label }}
                        </span>
                        <h3 class="font-semibold text-mono mb-1 line-clamp-2">
                            {{ course.title }}
                        </h3>
                        <p class="text-sm text-secondary-foreground mb-3">
                            <span v-if="course.category">{{ course.category.name }}</span>
                            <span v-else>Uncategorized</span>
                            · {{ course.lessons_count }} lessons
                        </p>
                        <p v-if="course.instructor" class="text-xs text-muted-foreground mb-4">
                            {{ course.instructor.name }}
                        </p>
                        <Link :href="'/courses/' + course.id + '/edit'" class="kt-btn kt-btn-sm kt-btn-primary w-full justify-center">
                            Edit course
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="courses.data.length === 0" class="text-center text-secondary-foreground py-10">
            No courses found. Create your first course.
        </p>

        <div v-if="courses.links && courses.links.length > 3" class="flex justify-center gap-2 mt-8 flex-wrap">
            <template v-for="(link, index) in courses.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="kt-btn kt-btn-sm"
                    :class="link.active ? 'kt-btn-primary' : 'kt-btn-outline'"
                    v-html="link.label"
                />
                <span v-else class="kt-btn kt-btn-sm kt-btn-outline opacity-50" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
