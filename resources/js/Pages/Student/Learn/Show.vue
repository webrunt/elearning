<script setup>
import HtmlContent from '@/components/Admin/HtmlContent.vue';
import { getJson, patchJson, postJson } from '@/utils/api';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Pages/Layouts/PublicLayout.vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    lesson: { type: Object, required: true },
    curriculum: { type: Array, required: true },
    progress: { type: Object, default: null },
    can_track_progress: { type: Boolean, default: false },
    is_enrolled: { type: Boolean, default: false },
    quiz: { type: Object, required: true },
    lesson_complete: { type: Boolean, default: false },
    next_lesson: { type: Object, default: null },
    previous_lesson: { type: Object, default: null },
    enroll_url: { type: String, required: true },
});

const activeTab = ref('learn');
const localProgress = ref(props.progress ? { ...props.progress } : null);
const lessonComplete = ref(props.lesson_complete);
const contentComplete = ref(props.progress ? props.progress.content_complete : false);
const quizPassed = ref(props.quiz.passed);
const quizLoading = ref(false);
const quizQuestions = ref([]);
const quizAnswers = ref({});
const quizResult = ref(null);
const quizError = ref('');

const videoRef = ref(null);
let progressTimer = null;
let lastSavedAt = 0;

const hasQuiz = computed(() => props.lesson.has_quiz);

const canAccessQuiz = computed(() => {
    return hasQuiz.value && contentComplete.value;
});

const canTakeQuiz = computed(() => {
    return props.can_track_progress && canAccessQuiz.value;
});

const showQuizPrompt = computed(() => {
    return hasQuiz.value && contentComplete.value && !quizPassed.value;
});

const progressHint = computed(() => {
    if (!props.can_track_progress) {
        return '';
    }

    if (!hasQuiz.value) {
        return 'Progress saves automatically.';
    }

    if (props.quiz.required) {
        return 'Progress saves automatically. Watch at least 90% to unlock the quiz and complete this lesson.';
    }

    return 'Progress saves automatically. Watch at least 90% to unlock the optional practice quiz.';
});

const quizPromptMessage = computed(() => {
    if (props.quiz.required) {
        return 'Content complete — pass the quiz to finish this lesson.';
    }

    return 'Content complete — try the optional quiz to check your understanding.';
});

const syncProgressState = (data) => {
    if (!data || !data.progress) {
        return;
    }

    localProgress.value = data.progress;
    contentComplete.value = data.progress.content_complete;
    lessonComplete.value = data.lesson_complete;
};

const saveProgress = async (payload) => {
    if (!props.can_track_progress) {
        return;
    }

    const response = await patchJson('/learn/lessons/' + props.lesson.id + '/progress', payload);

    if (!response.ok) {
        return;
    }

    const data = await response.json();
    syncProgressState(data);
};

const onVideoTimeUpdate = () => {
    if (!videoRef.value || !props.can_track_progress) {
        return;
    }

    const video = videoRef.value;
    const duration = video.duration;

    if (!duration || duration <= 0) {
        return;
    }

    const current = Math.floor(video.currentTime);
    const percent = Math.min(100, Math.round((video.currentTime / duration) * 100));

    if (localProgress.value) {
        localProgress.value.last_position_seconds = current;
        localProgress.value.watched_percent = percent;
    }

    if (percent >= 90) {
        contentComplete.value = true;
    }

    const now = Date.now();
    const shouldSaveNow = percent >= 90 && (now - lastSavedAt >= 15000 || lastSavedAt === 0);

    if (!shouldSaveNow && now - lastSavedAt < 15000) {
        return;
    }

    lastSavedAt = now;

    saveProgress({
        last_position_seconds: current,
        watched_percent: percent,
    });
};

const markArticleComplete = async () => {
    await saveProgress({ mark_content_complete: true });
    contentComplete.value = true;
};

const openQuizTab = () => {
    activeTab.value = 'quiz';

    if (canAccessQuiz.value && quizQuestions.value.length === 0) {
        loadQuiz();
    }
};

const loadQuiz = async () => {
    if (!props.can_track_progress || !canAccessQuiz.value) {
        return;
    }

    quizLoading.value = true;
    quizError.value = '';

    const response = await getJson('/learn/lessons/' + props.lesson.id + '/quiz');

    quizLoading.value = false;

    if (!response.ok) {
        quizError.value = 'Could not load quiz.';
        return;
    }

    const data = await response.json();
    quizQuestions.value = data.questions || [];
    quizAnswers.value = {};
    quizResult.value = null;
    activeTab.value = 'quiz';
};

const submitQuiz = async () => {
    const answers = [];

    for (let i = 0; i < quizQuestions.value.length; i++) {
        const question = quizQuestions.value[i];
        const optionId = quizAnswers.value[question.id];

        if (!optionId) {
            quizError.value = 'Please answer every question.';
            return;
        }

        answers.push({
            question_id: question.id,
            option_id: optionId,
        });
    }

    quizError.value = '';
    quizLoading.value = true;

    const response = await postJson('/learn/lessons/' + props.lesson.id + '/quiz', { answers });

    quizLoading.value = false;

    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        quizError.value = data.message || 'Quiz submission failed.';
        return;
    }

    const data = await response.json();
    quizResult.value = data;

    if (data.passed) {
        quizPassed.value = true;
        lessonComplete.value = data.lesson_complete;
    }
};

const goToNextLesson = () => {
    if (props.next_lesson && props.next_lesson.href) {
        router.visit(props.next_lesson.href);
    }
};

onMounted(() => {
    if (localProgress.value && props.lesson.type === 'video' && localProgress.value.watched_percent >= 90) {
        contentComplete.value = true;
    }

    if (localProgress.value && localProgress.value.content_complete) {
        contentComplete.value = true;
    }

    if (videoRef.value && localProgress.value && localProgress.value.last_position_seconds > 0) {
        videoRef.value.currentTime = localProgress.value.last_position_seconds;
    }

    progressTimer = window.setInterval(() => {
        if (props.lesson.type === 'video' && videoRef.value) {
            onVideoTimeUpdate();
        }
    }, 15000);
});

onBeforeUnmount(() => {
    if (progressTimer !== null) {
        window.clearInterval(progressTimer);
    }
});

watch(() => props.lesson.id, () => {
    localProgress.value = props.progress ? { ...props.progress } : null;
    lessonComplete.value = props.lesson_complete;
    contentComplete.value = props.progress ? props.progress.content_complete : false;
    quizPassed.value = props.quiz.passed;
    quizQuestions.value = [];
    quizResult.value = null;
    activeTab.value = 'learn';
});
</script>

<script>
export default {
    layout: PublicLayout,
};
</script>

<template>
    <Head :title="lesson.title + ' · ' + course.title" />
    <div class="kt-container-fixed py-6 lg:py-8">
        <div class="flex flex-wrap items-center gap-2 text-sm mb-4">
            <Link :href="'/courses/' + course.slug" class="link">
                {{ course.title }}
            </Link>
            <span class="text-muted-foreground">/</span>
            <span class="text-mono font-medium">{{ lesson.title }}</span>
        </div>

        <div v-if="!is_enrolled && lesson.is_preview" class="rounded-xl border border-amber-500/40 bg-amber-500/10 p-4 mb-6 text-sm">
            <strong>Free preview</strong> — enroll to save progress and access all lessons.
            <button class="kt-btn kt-btn-sm kt-btn-primary ms-2" type="button" @click="router.post(enroll_url)">
                Enroll free
            </button>
        </div>

        <div class="grid lg:grid-cols-12 gap-6">
            <aside class="lg:col-span-3 order-2 lg:order-1">
                <div class="kt-card lg:sticky lg:top-24">
                    <div class="kt-card-header px-4 py-3 border-b border-border">
                        <h2 class="text-sm font-semibold">
                            Course content
                        </h2>
                    </div>
                    <div class="kt-card-content p-3 max-h-[28rem] overflow-y-auto flex flex-col gap-3">
                        <div v-for="item in curriculum" :key="item.id">
                            <Link
                                :href="item.href"
                                class="flex items-start gap-2 rounded-lg px-2 py-2 text-sm transition-colors"
                                :class="item.is_current
                                    ? 'bg-primary/10 text-primary font-medium'
                                    : 'hover:bg-accent/50 text-foreground'"
                            >
                                <i
                                    class="ki-filled text-base mt-0.5 shrink-0"
                                    :class="item.is_completed ? 'ki-check-circle text-primary' : 'ki-book'"
                                />
                                <span class="line-clamp-2">{{ item.title }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-9 order-1 lg:order-2 flex flex-col gap-6">
                <div class="flex flex-wrap gap-2 border-b border-border pb-2">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="activeTab === 'learn' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-accent'"
                        @click="activeTab = 'learn'"
                    >
                        Lesson
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="activeTab === 'summary' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-accent'"
                        @click="activeTab = 'summary'"
                    >
                        Summary
                    </button>
                    <button
                        v-if="hasQuiz"
                        type="button"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="activeTab === 'quiz' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-accent'"
                        :disabled="!canAccessQuiz && !quizPassed"
                        :title="!canAccessQuiz ? 'Complete the lesson content first' : ''"
                        @click="openQuizTab"
                    >
                        Quiz
                        <span v-if="!quiz.required" class="opacity-80 font-normal">(optional)</span>
                    </button>
                </div>

                <div v-if="lesson_complete" class="rounded-xl border border-primary/30 bg-primary/5 p-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-medium text-mono">
                        Lesson complete
                    </p>
                    <button v-if="next_lesson" class="kt-btn kt-btn-primary" type="button" @click="goToNextLesson">
                        Next: {{ next_lesson.title }}
                    </button>
                </div>

                <div v-show="activeTab === 'learn'" class="flex flex-col gap-4">
                    <h1 class="text-2xl font-semibold text-mono">
                        {{ lesson.title }}
                    </h1>

                    <div v-if="lesson.type === 'video'" class="rounded-xl overflow-hidden bg-black">
                        <video
                            v-if="lesson.video_url"
                            ref="videoRef"
                            class="w-full max-h-[32rem]"
                            controls
                            @timeupdate="onVideoTimeUpdate"
                        >
                            <source :src="lesson.video_url" type="video/mp4" />
                        </video>
                        <div v-else class="p-12 text-center text-sm text-muted-foreground">
                            No video uploaded for this lesson yet.
                        </div>
                    </div>

                    <div v-else class="kt-card">
                        <div class="kt-card-content p-5 lg:p-8">
                            <HtmlContent :html="lesson.content" empty-text="No article content yet." />
                        </div>
                    </div>

                    <div v-if="can_track_progress && lesson.type === 'article' && !contentComplete" class="flex justify-end">
                        <button class="kt-btn kt-btn-primary" type="button" @click="markArticleComplete">
                            Mark as read
                        </button>
                    </div>

                    <div v-if="progressHint" class="text-xs text-muted-foreground">
                        {{ progressHint }}
                        <span v-if="localProgress && lesson.type === 'video'"> ({{ localProgress.watched_percent }}% watched)</span>
                    </div>

                    <div
                        v-if="showQuizPrompt"
                        class="rounded-xl border border-border p-4 flex flex-wrap items-center justify-between gap-3"
                    >
                        <p class="text-sm">
                            {{ quizPromptMessage }}
                        </p>
                        <button class="kt-btn kt-btn-primary" type="button" :disabled="!canTakeQuiz" @click="loadQuiz">
                            {{ quiz.required ? 'Start quiz' : 'Try practice quiz' }}
                        </button>
                    </div>

                    <p v-if="hasQuiz && !contentComplete && can_track_progress" class="text-xs text-muted-foreground">
                        The quiz tab unlocks after you watch at least 90% of the video.
                    </p>
                </div>

                <div v-show="activeTab === 'summary'" class="kt-card">
                    <div class="kt-card-content p-5 lg:p-8">
                        <HtmlContent :html="lesson.summary" empty-text="No summary for this lesson." />
                    </div>
                </div>

                <div v-show="activeTab === 'quiz' && hasQuiz" class="kt-card">
                    <div class="kt-card-content p-5 lg:p-8 flex flex-col gap-6">
                        <div v-if="quizPassed && !quizResult" class="text-center py-6">
                            <p class="font-medium text-mono text-primary mb-2">
                                You passed this quiz
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Minimum score: {{ quiz.pass_percent }}%
                            </p>
                        </div>

                        <div v-else-if="!canAccessQuiz" class="text-center py-6 text-sm text-secondary-foreground">
                            Complete the lesson content first (watch at least 90% of the video).
                        </div>

                        <div v-else-if="quizQuestions.length === 0 && !quizLoading" class="text-center py-6">
                            <p v-if="!quiz.required" class="text-sm text-muted-foreground mb-4">
                                This quiz is optional and does not block lesson completion.
                            </p>
                            <button class="kt-btn kt-btn-primary" type="button" :disabled="!canTakeQuiz" @click="loadQuiz">
                                Load quiz questions
                            </button>
                        </div>

                        <template v-else>
                            <p class="text-sm text-muted-foreground">
                                Answer all questions. You need {{ quiz.pass_percent }}% to pass.
                            </p>

                            <div
                                v-for="(question, qIndex) in quizQuestions"
                                :key="question.id"
                                class="border border-border rounded-xl p-4"
                            >
                                <p class="font-medium text-mono mb-3">
                                    {{ qIndex + 1 }}. {{ question.prompt }}
                                </p>
                                <div class="flex flex-col gap-2">
                                    <label
                                        v-for="option in question.options"
                                        :key="option.id"
                                        class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition-colors"
                                        :class="quizAnswers[question.id] === option.id
                                            ? 'border-primary bg-primary/5'
                                            : 'border-border hover:bg-accent/40'"
                                    >
                                        <input
                                            v-model="quizAnswers[question.id]"
                                            class="kt-radio"
                                            type="radio"
                                            :name="'q-' + question.id"
                                            :value="option.id"
                                        />
                                        <span class="text-sm">{{ option.label }}</span>
                                    </label>
                                </div>
                            </div>

                            <p v-if="quizError" class="text-sm text-destructive">
                                {{ quizError }}
                            </p>

                            <div v-if="quizResult" class="rounded-xl p-4 text-center" :class="quizResult.passed ? 'bg-primary/10' : 'bg-destructive/10'">
                                <p class="font-semibold text-mono">
                                    Score: {{ quizResult.score }}%
                                </p>
                                <p class="text-sm mt-1">
                                    {{ quizResult.passed ? 'Passed! Lesson marked complete.' : 'Not passed — try again.' }}
                                </p>
                            </div>

                            <button
                                class="kt-btn kt-btn-primary self-start"
                                type="button"
                                :disabled="quizLoading"
                                @click="submitQuiz"
                            >
                                Submit answers
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex flex-wrap justify-between gap-3 pt-2">
                    <Link
                        v-if="previous_lesson"
                        :href="previous_lesson.href"
                        class="kt-btn kt-btn-outline"
                    >
                        ← {{ previous_lesson.title }}
                    </Link>
                    <span v-else />
                    <Link
                        v-if="next_lesson && lesson_complete"
                        :href="next_lesson.href"
                        class="kt-btn kt-btn-primary"
                    >
                        {{ next_lesson.title }} →
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
