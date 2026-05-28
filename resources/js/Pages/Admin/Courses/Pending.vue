<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    courses: { type: Object, required: true },
});
</script>

<script>
import AdminLayout from '@/Pages/Layouts/AdminDashboardLayout.vue';

export default {
    layout: AdminLayout,
};
</script>

<template>
    <Head title="Pending review" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-mono">
                Courses pending review
            </h1>
            <p class="text-sm text-secondary-foreground">
                Instructor submissions waiting for approval before publish.
            </p>
        </div>

        <div v-if="courses.data.length === 0" class="kt-card">
            <div class="kt-card-content p-8 text-center text-secondary-foreground">
                No courses are waiting for review.
            </div>
        </div>

        <div v-else class="flex flex-col gap-4">
            <div
                v-for="course in courses.data"
                :key="course.id"
                class="kt-card"
            >
                <div class="kt-card-content p-5 flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="font-semibold text-mono">
                            {{ course.title }}
                        </h2>
                        <p v-if="course.summary" class="text-sm text-muted-foreground mt-1 line-clamp-2">
                            {{ course.summary }}
                        </p>
                        <p class="text-xs text-secondary-foreground mt-2">
                            <span v-if="course.instructor">{{ course.instructor.name }} · {{ course.instructor.email }}</span>
                            <span v-if="course.category"> · {{ course.category }}</span>
                            · {{ course.sections_count }} sections · {{ course.lessons_count }} lessons
                        </p>
                    </div>
                    <Link
                        :href="'/courses/' + course.id + '/review'"
                        class="kt-btn kt-btn-primary shrink-0"
                    >
                        Review
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
