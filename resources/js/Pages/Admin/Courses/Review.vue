<script setup>
import HtmlContent from '@/components/Admin/HtmlContent.vue';
import { useConfirmModal } from '@/composables/useConfirmModal';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    course: { type: Object, required: true },
    instructor: { type: Object, default: null },
    category: { type: Object, default: null },
    reviewer: { type: Object, default: null },
    stats: { type: Object, required: true },
    curriculum: { type: Array, required: true },
});

const confirmModal = useConfirmModal();

const approveForm = useForm({
    review_summary: '',
});

const rejectForm = useForm({
    rejection_feedback: '',
    review_summary: '',
});

const submitApprove = async () => {
    const confirmed = await confirmModal.confirm({
        title: 'Approve and publish',
        message: 'Publish "' + props.course.title + '" to the catalog?',
        confirmLabel: 'Publish',
        cancelLabel: 'Cancel',
        variant: 'primary',
    });

    if (!confirmed) {
        return;
    }

    approveForm.post('/courses/' + props.course.id + '/approve');
};

const submitReject = async () => {
    const confirmed = await confirmModal.confirm({
        title: 'Request changes',
        message: 'Send this course back to the instructor as a draft?',
        confirmLabel: 'Send back',
        cancelLabel: 'Cancel',
        variant: 'danger',
    });

    if (!confirmed) {
        return;
    }

    rejectForm.post('/courses/' + props.course.id + '/reject');
};

const formatDuration = (seconds) => {
    if (!seconds) {
        return '—';
    }

    const minutes = Math.round(seconds / 60);

    return minutes + ' min';
};
</script>

<script>
import AdminLayout from '@/Pages/Layouts/AdminDashboardLayout.vue';

export default {
    layout: AdminLayout,
};
</script>

<template>
    <Head :title="'Review: ' + course.title" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <Link href="/courses/pending" class="text-sm link mb-2 inline-block">
                    ← Pending review
                </Link>
                <h1 class="text-xl font-semibold text-mono">
                    {{ course.title }}
                </h1>
                <p v-if="instructor" class="text-sm text-muted-foreground">
                    {{ instructor.name }} · {{ instructor.email }}
                </p>
            </div>
            <img
                v-if="course.thumbnail_url"
                :src="course.thumbnail_url"
                alt=""
                class="w-32 h-20 object-cover rounded-lg border border-border"
            />
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <div class="kt-card lg:col-span-2">
                <div class="kt-card-header px-5 py-4">
                    <h3 class="kt-card-title text-sm font-semibold">
                        Course summary
                    </h3>
                </div>
                <div class="kt-card-content p-5">
                    <HtmlContent :html="course.summary" empty-text="No summary provided." />
                    <p v-if="category" class="text-xs text-muted-foreground mt-4">
                        Category: {{ category.name }}
                    </p>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header px-5 py-4">
                    <h3 class="kt-card-title text-sm font-semibold">
                        Review checklist
                    </h3>
                </div>
                <div class="kt-card-content p-5">
                    <ul class="text-sm flex flex-col gap-2">
                        <li>{{ stats.sections_count }} sections</li>
                        <li>{{ stats.lessons_count }} lessons</li>
                        <li>{{ stats.video_lessons }} video · {{ stats.article_lessons }} article</li>
                        <li>{{ stats.lessons_with_video_file }} with video file</li>
                        <li>{{ stats.lessons_with_quiz }} with quiz</li>
                        <li>{{ stats.preview_lessons }} preview lessons</li>
                        <li>~{{ stats.total_duration_minutes }} min total</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="kt-card mb-6">
            <div class="kt-card-header px-5 py-4">
                <h3 class="kt-card-title text-sm font-semibold">
                    Curriculum
                </h3>
            </div>
            <div class="kt-card-content p-5 flex flex-col gap-6">
                <div v-for="section in curriculum" :key="section.id" class="border border-border rounded-xl p-4">
                    <h4 class="font-semibold text-mono mb-3">
                        {{ section.title }}
                    </h4>
                    <ul class="flex flex-col gap-2">
                        <li
                            v-for="lesson in section.lessons"
                            :key="lesson.id"
                            class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-lg bg-accent/40 text-sm"
                        >
                            <span class="font-medium">{{ lesson.title }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ lesson.type }}
                                <span v-if="lesson.is_preview"> · preview</span>
                                <span v-if="lesson.has_video"> · video</span>
                                <span v-if="lesson.quiz_count"> · {{ lesson.quiz_count }} quiz Q</span>
                                · {{ formatDuration(lesson.duration_seconds) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <form class="kt-card border-primary/30" @submit.prevent="submitApprove">
                <div class="kt-card-header px-5 py-4">
                    <h3 class="kt-card-title text-sm font-semibold text-primary">
                        Approve & publish
                    </h3>
                </div>
                <div class="kt-card-content p-5 flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Admin review summary</label>
                        <textarea
                            v-model="approveForm.review_summary"
                            class="kt-input"
                            rows="4"
                            required
                            placeholder="Brief note on what was reviewed (visible internally)."
                        />
                        <p v-if="approveForm.errors.review_summary" class="text-sm text-destructive">
                            {{ approveForm.errors.review_summary }}
                        </p>
                    </div>
                    <button class="kt-btn kt-btn-primary" type="submit" :disabled="approveForm.processing">
                        Approve and publish
                    </button>
                </div>
            </form>

            <form class="kt-card" @submit.prevent="submitReject">
                <div class="kt-card-header px-5 py-4">
                    <h3 class="kt-card-title text-sm font-semibold">
                        Request changes
                    </h3>
                </div>
                <div class="kt-card-content p-5 flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Feedback for instructor</label>
                        <textarea
                            v-model="rejectForm.rejection_feedback"
                            class="kt-input"
                            rows="4"
                            required
                            placeholder="What must be fixed before resubmission?"
                        />
                        <p v-if="rejectForm.errors.rejection_feedback" class="text-sm text-destructive">
                            {{ rejectForm.errors.rejection_feedback }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Internal review note (optional)</label>
                        <textarea v-model="rejectForm.review_summary" class="kt-input" rows="2" />
                    </div>
                    <button class="kt-btn kt-btn-outline text-destructive" type="submit" :disabled="rejectForm.processing">
                        Send back to draft
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
