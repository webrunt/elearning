<script setup>
import HtmlContent from '@/components/Admin/HtmlContent.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Pages/Layouts/PublicLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    progress_percent: { type: Number, default: 0 },
    can_enroll: { type: Boolean, default: false },
    login_required: { type: Boolean, default: false },
    continue_url: { type: String, default: null },
    can_submit_review: { type: Boolean, default: false },
    my_review: { type: Object, default: null },
    reviews_summary: { type: Object, required: true },
    reviews: { type: Array, default: () => [] },
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

const reviewForm = useForm({
    rating: props.my_review?.rating ?? 5,
    body: props.my_review?.body ?? '',
});

const submitReview = () => {
    reviewForm.post('/courses/' + props.course.slug + '/reviews', {
        preserveScroll: true,
    });
};

const reviewStatusLabel = (status) => {
    if (status === 'pending') {
        return 'Pending moderation';
    }

    if (status === 'approved') {
        return 'Published';
    }

    if (status === 'rejected') {
        return 'Not published';
    }

    return status;
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
                            Reviews
                        </h2>
                        <p v-if="reviews_summary.count > 0" class="text-xs text-muted-foreground mt-1">
                            {{ reviews_summary.average_rating }} average · {{ reviews_summary.count }} review{{ reviews_summary.count === 1 ? '' : 's' }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground mt-1">
                            No published reviews yet.
                        </p>
                    </div>
                    <div class="kt-card-content p-5 flex flex-col gap-4">
                        <div
                            v-for="(review, index) in reviews"
                            :key="index"
                            class="border border-border rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="font-medium text-sm">{{ review.user_name }}</span>
                                <span class="text-xs text-muted-foreground">{{ review.rating }} / 5</span>
                            </div>
                            <p v-if="review.body" class="text-sm whitespace-pre-wrap">
                                {{ review.body }}
                            </p>
                        </div>

                        <div v-if="can_submit_review" class="border border-border rounded-xl p-4 flex flex-col gap-3">
                            <h3 class="text-sm font-semibold">
                                {{ my_review ? 'Update your review' : 'Leave a review' }}
                            </h3>
                            <p v-if="my_review" class="text-xs text-muted-foreground">
                                Status: {{ reviewStatusLabel(my_review.status) }}
                                <span v-if="my_review.moderation_note"> — {{ my_review.moderation_note }}</span>
                            </p>
                            <label class="text-sm">
                                Rating
                                <select v-model="reviewForm.rating" class="kt-input mt-1 w-full">
                                    <option v-for="n in 5" :key="n" :value="n">
                                        {{ n }} star{{ n === 1 ? '' : 's' }}
                                    </option>
                                </select>
                            </label>
                            <label class="text-sm">
                                Comment (optional)
                                <textarea
                                    v-model="reviewForm.body"
                                    class="kt-input mt-1 min-h-24 w-full"
                                    rows="4"
                                    maxlength="5000"
                                    placeholder="What did you think of this course?"
                                />
                            </label>
                            <button
                                type="button"
                                class="kt-btn kt-btn-primary w-fit"
                                :disabled="reviewForm.processing"
                                @click="submitReview"
                            >
                                Submit review
                            </button>
                        </div>
                    </div>
                </div>

                <div class="kt-card">
                    <div class="kt-card-header px-5 py-4 border-b border-border">
                        <h2 class="text-sm font-semibold">
                            Curriculum
                        </h2>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ totalLessons }} lessons
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
                                    <Link
                                        v-if="lesson.learn_url"
                                        :href="lesson.learn_url"
                                        class="kt-btn kt-btn-sm kt-btn-ghost shrink-0"
                                    >
                                        Open
                                    </Link>
                                    <span v-else class="text-xs text-muted-foreground shrink-0">
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
                            <Link
                                v-if="continue_url"
                                :href="continue_url"
                                class="kt-btn kt-btn-primary w-full"
                            >
                                Continue learning
                            </Link>
                            <Link href="/my-learning" class="kt-btn kt-btn-outline w-full">
                                My learning
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
