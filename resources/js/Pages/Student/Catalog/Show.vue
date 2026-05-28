<script setup>
import HtmlContent from '@/components/Admin/HtmlContent.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Pages/Layouts/PublicLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    progress_percent: { type: Number, default: 0 },
    can_enroll: { type: Boolean, default: false },
    login_required: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const totalLessons = computed(() => {
    let count = 0;

    for (let i = 0; i < props.course.sections.length; i++) {
        count += props.course.sections[i].lessons.length;
    }

    return count;
});

const enroll = () => {
    router.post('/courses/' + props.course.slug + '/enroll');
};

const lessonTypeLabel = (type) => {
    if (type === 'video') {
        return 'Video';
    }

    if (type === 'article') {
        return 'Article';
    }

    return type;
};
</script>

<script>
export default {
    layout: PublicLayout,
};
</script>

<template>
    <Head :title="course.title" />
    <div class="kt-container-fixed py-10 lg:py-14">
        <Link href="/courses" class="text-sm link mb-4 inline-block">
            ← All courses
        </Link>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 flex flex-col gap-6">
                <div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span v-if="course.category" class="kt-badge kt-badge-sm kt-badge-light">
                            {{ course.category.name }}
                        </span>
                        <span v-if="course.is_enrolled" class="kt-badge kt-badge-sm kt-badge-primary">
                            Enrolled
                        </span>
                    </div>
                    <h1 class="text-3xl font-semibold text-mono mb-2">
                        {{ course.title }}
                    </h1>
                    <p v-if="course.instructor" class="text-sm text-muted-foreground">
                        Instructor: {{ course.instructor.name }}
                    </p>
                </div>

                <div class="kt-card">
                    <div class="kt-card-header px-5 py-4 border-b border-border">
                        <h2 class="text-sm font-semibold">
                            About this course
                        </h2>
                    </div>
                    <div class="kt-card-content p-5">
                        <HtmlContent :html="course.summary" empty-text="No description yet." />
                    </div>
                </div>

                <div class="kt-card">
                    <div class="kt-card-header px-5 py-4 border-b border-border">
                        <h2 class="text-sm font-semibold">
                            Curriculum
                        </h2>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ totalLessons }} lessons · Lesson player arrives in Phase 3
                        </p>
                    </div>
                    <div class="kt-card-content p-5 flex flex-col gap-4">
                        <div
                            v-for="section in course.sections"
                            :key="section.id"
                            class="border border-border rounded-xl p-4"
                        >
                            <h3 class="font-semibold text-mono mb-3">
                                {{ section.title }}
                            </h3>
                            <ul class="flex flex-col gap-2">
                                <li
                                    v-for="lesson in section.lessons"
                                    :key="lesson.id"
                                    class="flex items-center justify-between gap-3 text-sm p-2 rounded-lg bg-accent/40"
                                >
                                    <span>{{ lesson.title }}</span>
                                    <span class="text-xs text-muted-foreground shrink-0">
                                        {{ lessonTypeLabel(lesson.type) }}
                                        <span v-if="lesson.is_preview"> · Preview</span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="kt-card sticky top-24">
                    <div class="rounded-t-xl overflow-hidden h-48 bg-muted/50">
                        <img
                            v-if="course.thumbnail_url"
                            :src="course.thumbnail_url"
                            :alt="course.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <i class="ki-filled ki-book text-4xl text-muted-foreground" />
                        </div>
                    </div>
                    <div class="kt-card-content p-5 flex flex-col gap-4">
                        <div class="text-2xl font-semibold text-mono">
                            Free
                        </div>
                        <p class="text-sm text-secondary-foreground">
                            {{ course.lessons_count }} lessons included
                        </p>

                        <div v-if="course.is_enrolled" class="flex flex-col gap-3">
                            <div>
                                <div class="flex justify-between text-xs text-muted-foreground mb-1">
                                    <span>Your progress</span>
                                    <span>{{ progress_percent }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-muted overflow-hidden">
                                    <div
                                        class="h-full bg-primary transition-all"
                                        :style="{ width: progress_percent + '%' }"
                                    />
                                </div>
                            </div>
                            <Link href="/my-learning" class="kt-btn kt-btn-primary w-full">
                                Go to My learning
                            </Link>
                        </div>

                        <template v-else-if="can_enroll">
                            <button class="kt-btn kt-btn-primary w-full" type="button" @click="enroll">
                                Enroll for free
                            </button>
                        </template>

                        <template v-else-if="login_required">
                            <p class="text-sm text-secondary-foreground">
                                Sign in with a student account to enroll.
                            </p>
                            <Link href="/login" class="kt-btn kt-btn-primary w-full">
                                Sign in to enroll
                            </Link>
                            <Link href="/register" class="kt-btn kt-btn-outline w-full">
                                Create free account
                            </Link>
                        </template>

                        <p v-else class="text-sm text-secondary-foreground">
                            Enrollment is not available for this course.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
