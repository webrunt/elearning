<script setup>
import CourseCard from '@/components/Student/CourseCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Pages/Layouts/PublicLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    courses: { type: Object, required: true },
    filters: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

const applyFilters = () => {
    router.get('/courses', {
        search: search.value,
        category: category.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(category, applyFilters);

const onSearchKeydown = (event) => {
    if (event.key === 'Enter') {
        applyFilters();
    }
};
</script>

<script>
export default {
    layout: PublicLayout,
};
</script>

<template>
    <Head title="Courses" />
    <div class="kt-container-fixed py-10 lg:py-14">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-mono mb-2">
                Course catalog
            </h1>
            <p class="text-secondary-foreground max-w-2xl">
                Browse published courses and enroll for free. Track your progress in My learning.
            </p>
        </div>

        <div class="kt-card mb-8">
            <div class="kt-card-content p-5 flex flex-wrap gap-4">
                <input
                    v-model="search"
                    class="kt-input max-w-sm grow"
                    type="search"
                    placeholder="Search courses…"
                    @keydown="onSearchKeydown"
                />
                <select v-model="category" class="kt-select w-48">
                    <option value="">All categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.slug">
                        {{ cat.name }}
                    </option>
                </select>
                <button class="kt-btn kt-btn-outline" type="button" @click="applyFilters">
                    Search
                </button>
            </div>
        </div>

        <div v-if="courses.data.length === 0" class="kt-card">
            <div class="kt-card-content p-12 text-center text-secondary-foreground">
                <p class="font-medium text-mono mb-2">
                    No published courses found
                </p>
                <p class="text-sm">
                    Try another search or check back soon.
                </p>
            </div>
        </div>

        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <CourseCard v-for="course in courses.data" :key="course.slug" :course="course" />
        </div>
    </div>
</template>
