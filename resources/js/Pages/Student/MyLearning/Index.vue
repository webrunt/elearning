<script setup>
import { stripHtml } from '@/utils/stripHtml';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Pages/Layouts/PublicLayout.vue';

defineProps({
    enrollments: { type: Array, required: true },
});
</script>

<script>
export default {
    layout: PublicLayout,
};
</script>

<template>
    <Head title="My learning" />
    <div class="kt-container-fixed py-10 lg:py-14">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-semibold text-mono mb-2">
                    My learning
                </h1>
                <p class="text-secondary-foreground">
                    Courses you have enrolled in and your progress.
                </p>
            </div>
            <Link href="/courses" class="kt-btn kt-btn-outline">
                Browse more courses
            </Link>
        </div>

        <div v-if="enrollments.length === 0" class="kt-card">
            <div class="kt-card-content p-12 text-center">
                <p class="font-medium text-mono mb-2">
                    You have not enrolled in any courses yet
                </p>
                <p class="text-sm text-secondary-foreground mb-6">
                    Explore the catalog and enroll for free to start learning.
                </p>
                <Link href="/courses" class="kt-btn kt-btn-primary">
                    Explore courses
                </Link>
            </div>
        </div>

        <div v-else class="grid md:grid-cols-2 gap-6">
            <div
                v-for="enrollment in enrollments"
                :key="enrollment.id"
                class="kt-card flex flex-col"
            >
                <div class="flex gap-4 p-5 border-b border-border">
                    <div class="size-20 rounded-lg bg-muted/50 overflow-hidden shrink-0">
                        <img
                            v-if="enrollment.course.thumbnail_url"
                            :src="enrollment.course.thumbnail_url"
                            :alt="enrollment.course.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <i class="ki-filled ki-book text-xl text-muted-foreground" />
                        </div>
                    </div>
                    <div class="min-w-0 grow">
                        <span v-if="enrollment.course.category" class="kt-badge kt-badge-xs kt-badge-light mb-2">
                            {{ enrollment.course.category.name }}
                        </span>
                        <h2 class="font-semibold text-mono line-clamp-2">
                            {{ enrollment.course.title }}
                        </h2>
                        <p v-if="enrollment.course.instructor" class="text-xs text-muted-foreground mt-1">
                            {{ enrollment.course.instructor.name }}
                        </p>
                    </div>
                </div>
                <div class="p-5 flex flex-col gap-4 grow">
                    <p v-if="enrollment.course.summary" class="text-sm text-secondary-foreground line-clamp-2">
                        {{ stripHtml(enrollment.course.summary) }}
                    </p>
                    <div>
                        <div class="flex justify-between text-xs text-muted-foreground mb-1">
                            <span>Progress</span>
                            <span>
                                {{ enrollment.completed_lessons_count }} / {{ enrollment.total_lessons_count }} lessons
                                · {{ enrollment.progress_percent }}%
                            </span>
                        </div>
                        <div class="h-2.5 rounded-full bg-muted overflow-hidden">
                            <div
                                class="h-full bg-primary transition-all"
                                :style="{ width: enrollment.progress_percent + '%' }"
                            />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 mt-auto">
                        <Link
                            :href="enrollment.continue_url"
                            class="kt-btn kt-btn-primary"
                        >
                            Continue learning
                        </Link>
                        <Link
                            :href="'/courses/' + enrollment.course.slug"
                            class="kt-btn kt-btn-outline"
                        >
                            Course details
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
