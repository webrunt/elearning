<script setup>
import RichTextEditor from '@/components/Admin/RichTextEditor.vue';
import { useConfirmModal } from '@/composables/useConfirmModal';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const confirmModal = useConfirmModal();

const props = defineProps({
    lesson: { type: Object, required: true },
    course: { type: Object, required: true },
    section: { type: Object, required: true },
    lesson_types: { type: Object, required: true },
});

const blankOption = (isCorrect) => ({ id: null, label: '', is_correct: isCorrect });

const blankQuestion = () => ({
    id: null,
    prompt: '',
    options: [blankOption(true), blankOption(false)],
});

const initialQuestions = props.lesson.quiz_questions.length > 0
    ? props.lesson.quiz_questions.map((q) => ({
        id: q.id,
        prompt: q.prompt,
        options: q.options.map((o) => ({
            id: o.id,
            label: o.label,
            is_correct: o.is_correct,
        })),
    }))
    : [];

const form = useForm({
    title: props.lesson.title,
    type: props.lesson.type,
    summary: props.lesson.summary || '',
    content: props.lesson.content || '',
    duration_seconds: props.lesson.duration_seconds || '',
    require_quiz_to_complete: props.lesson.require_quiz_to_complete,
    quiz_pass_percent: props.lesson.quiz_pass_percent || 70,
    is_preview: props.lesson.is_preview,
    video: null,
    quiz_questions: initialQuestions,
});

const hasQuizQuestions = computed(() => form.quiz_questions.length > 0);

const quizCompletionMode = computed({
    get() {
        return form.require_quiz_to_complete ? 'required' : 'optional';
    },
    set(value) {
        form.require_quiz_to_complete = value === 'required';
    },
});

const showPassScore = computed(() => quizCompletionMode.value === 'required' && hasQuizQuestions.value);

const showQuizRequiredWarning = computed(() => quizCompletionMode.value === 'required' && !hasQuizQuestions.value);

const durationMinutes = computed(() => {
    const seconds = parseInt(form.duration_seconds, 10);

    if (!seconds || seconds <= 0) {
        return null;
    }

    return Math.round(seconds / 60);
});

const onVideo = (event) => {
    const file = event.target.files[0];
    form.video = file || null;
};

const setQuizMode = (mode) => {
    quizCompletionMode.value = mode;
};

const addQuestion = () => {
    form.quiz_questions.push(blankQuestion());
};

const removeQuestion = async (index) => {
    const confirmed = await confirmModal.confirm({
        title: 'Remove question',
        message: 'Delete question ' + (index + 1) + '? This cannot be undone until you save the lesson.',
        confirmLabel: 'Remove',
        cancelLabel: 'Cancel',
        variant: 'danger',
    });

    if (!confirmed) {
        return;
    }

    form.quiz_questions.splice(index, 1);
};

const addOption = (questionIndex) => {
    form.quiz_questions[questionIndex].options.push(blankOption(false));
};

const removeOption = (questionIndex, optionIndex) => {
    const options = form.quiz_questions[questionIndex].options;
    const wasCorrect = options[optionIndex].is_correct;
    options.splice(optionIndex, 1);

    if (wasCorrect && options.length > 0) {
        options[0].is_correct = true;
    }
};

const setCorrectOption = (questionIndex, optionIndex) => {
    const options = form.quiz_questions[questionIndex].options;
    for (let i = 0; i < options.length; i++) {
        options[i].is_correct = i === optionIndex;
    }
};

const submit = () => {
    form.put('/lessons/' + props.lesson.id, {
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
    <Head :title="'Lesson: ' + lesson.title" />
    <div class="kt-container-fixed py-5 lg:py-7.5 max-w-4xl">
        <div class="mb-6">
            <Link :href="'/courses/' + course.id + '/edit'" class="text-sm link mb-2 inline-block">
                ← {{ course.title }}
            </Link>
            <p class="text-sm text-secondary-foreground">
                {{ section.title }}
            </p>
            <h1 class="text-xl font-semibold text-mono mt-1">
                {{ lesson.title }}
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Edit lesson content and optional end-of-lesson quiz.
            </p>
        </div>

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="kt-card">
                <div class="kt-card-header px-5 py-4 border-b border-border">
                    <div>
                        <h3 class="kt-card-title text-sm font-semibold">
                            Lesson details
                        </h3>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Title, type, and how this lesson appears in the course.
                        </p>
                    </div>
                </div>
                <div class="kt-card-content p-5 lg:p-8 flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Title</label>
                        <input v-model="form.title" class="kt-input" type="text" required />
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Lesson type</label>
                            <select v-model="form.type" class="kt-select">
                                <option v-for="(label, value) in lesson_types" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Duration (seconds)</label>
                            <input v-model="form.duration_seconds" class="kt-input" type="number" min="0" placeholder="e.g. 600" />
                            <p v-if="durationMinutes" class="text-xs text-muted-foreground">
                                ≈ {{ durationMinutes }} minutes — used for progress display.
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                Optional. Helps show estimated course length to students.
                            </p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-border bg-accent/30 p-4">
                        <label class="kt-label items-start gap-3">
                            <input v-model="form.is_preview" class="kt-checkbox kt-checkbox-sm mt-0.5" type="checkbox" />
                            <span>
                                <span class="kt-checkbox-label font-medium text-mono">Free preview lesson</span>
                                <span class="block text-xs text-muted-foreground mt-1 font-normal">
                                    Students can watch or read this lesson before enrolling in the course.
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header px-5 py-4 border-b border-border">
                    <div>
                        <h3 class="kt-card-title text-sm font-semibold">
                            Lesson content
                        </h3>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            <span v-if="form.type === 'video'">Upload the video and add a short summary.</span>
                            <span v-else>Write the article body and an optional summary.</span>
                        </p>
                    </div>
                </div>
                <div class="kt-card-content p-5 lg:p-8 flex flex-col gap-4">
                    <RichTextEditor
                        v-model="form.summary"
                        variant="minimal"
                        label="Summary"
                        hint="Short description shown in the course outline. Plain formatting only."
                        placeholder="What will students learn in this lesson?"
                    />
                    <RichTextEditor
                        v-if="form.type === 'article'"
                        v-model="form.content"
                        variant="full"
                        label="Article body"
                        hint="Main lesson content students read after opening the lesson."
                        placeholder="Write your lesson here…"
                        required
                    />
                    <div v-if="form.type === 'video'" class="flex flex-col gap-2">
                        <label class="kt-form-label">Video file</label>
                        <p class="text-xs text-muted-foreground -mt-1">
                            MP4 or WebM. Replace the file below to update the video.
                        </p>
                        <video
                            v-if="lesson.video_url && !form.video"
                            :src="lesson.video_url"
                            class="w-full max-h-64 rounded-lg bg-black"
                            controls
                        />
                        <input class="kt-input" type="file" accept="video/mp4,video/webm" @change="onVideo" />
                        <p v-if="form.video" class="text-xs text-primary">
                            New file selected: {{ form.video.name }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header px-5 py-4 border-b border-border flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="kt-card-title text-sm font-semibold">
                            End-of-lesson quiz
                        </h3>
                        <p class="text-xs text-muted-foreground mt-0.5 max-w-xl">
                            Optional multiple-choice questions shown after the lesson. Choose whether students must pass to mark the lesson complete.
                        </p>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-primary shrink-0" type="button" @click="addQuestion">
                        <i class="ki-filled ki-plus" />
                        Add question
                    </button>
                </div>
                <div class="kt-card-content p-5 flex flex-col gap-6">
                    <div>
                        <p class="text-sm font-medium text-mono mb-3">
                            Completion rule
                        </p>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <button
                                type="button"
                                class="rounded-xl border p-4 text-left transition-colors"
                                :class="quizCompletionMode === 'optional'
                                    ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
                                    : 'border-border hover:bg-accent/40'"
                                @click="setQuizMode('optional')"
                            >
                                <span class="flex items-center gap-2 mb-2">
                                    <span
                                        class="size-4 rounded-full border-2 flex items-center justify-center"
                                        :class="quizCompletionMode === 'optional' ? 'border-primary' : 'border-muted-foreground'"
                                    >
                                        <span
                                            v-if="quizCompletionMode === 'optional'"
                                            class="size-2 rounded-full bg-primary"
                                        />
                                    </span>
                                    <span class="font-semibold text-sm text-mono">Quiz is optional</span>
                                </span>
                                <p class="text-xs text-muted-foreground ps-6">
                                    Students can finish the lesson without answering the quiz. Use questions for practice or self-check only.
                                </p>
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border p-4 text-left transition-colors"
                                :class="quizCompletionMode === 'required'
                                    ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
                                    : 'border-border hover:bg-accent/40'"
                                @click="setQuizMode('required')"
                            >
                                <span class="flex items-center gap-2 mb-2">
                                    <span
                                        class="size-4 rounded-full border-2 flex items-center justify-center"
                                        :class="quizCompletionMode === 'required' ? 'border-primary' : 'border-muted-foreground'"
                                    >
                                        <span
                                            v-if="quizCompletionMode === 'required'"
                                            class="size-2 rounded-full bg-primary"
                                        />
                                    </span>
                                    <span class="font-semibold text-sm text-mono">Quiz required to complete</span>
                                </span>
                                <p class="text-xs text-muted-foreground ps-6">
                                    Students must pass the quiz before this lesson counts as completed in their progress.
                                </p>
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="showPassScore"
                        class="rounded-xl border border-border bg-accent/20 p-4 flex flex-col sm:flex-row sm:items-end gap-4"
                    >
                        <div class="flex flex-col gap-1 grow max-w-xs">
                            <label class="kt-form-label">Minimum pass score</label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model.number="form.quiz_pass_percent"
                                    class="kt-input w-24"
                                    type="number"
                                    min="1"
                                    max="100"
                                />
                                <span class="text-sm text-muted-foreground">%</span>
                            </div>
                        </div>
                        <p class="text-sm text-secondary-foreground sm:pb-2">
                            Students need at least <strong class="text-mono">{{ form.quiz_pass_percent }}%</strong> correct
                            ({{ form.quiz_questions.length }} question<span v-if="form.quiz_questions.length !== 1">s</span>).
                        </p>
                    </div>

                    <div
                        v-if="showQuizRequiredWarning"
                        class="rounded-xl border border-amber-500/40 bg-amber-500/10 p-4 text-sm text-amber-950 dark:text-amber-100"
                    >
                        <strong class="font-medium">Add at least one question</strong>
                        — you chose “Quiz required to complete” but there are no questions yet. Add a question below or switch to optional.
                    </div>

                    <div v-if="hasQuizQuestions" class="flex flex-col gap-4">
                        <p class="text-sm font-medium text-mono">
                            Questions
                        </p>
                        <p class="text-xs text-muted-foreground -mt-2">
                            For each question, enter the prompt and mark exactly one correct answer.
                        </p>

                        <div
                            v-for="(question, qIndex) in form.quiz_questions"
                            :key="qIndex"
                            class="border border-border rounded-xl overflow-hidden"
                        >
                            <div class="flex justify-between items-center gap-3 px-4 py-3 bg-accent/40 border-b border-border">
                                <span class="text-sm font-semibold text-mono">
                                    Question {{ qIndex + 1 }}
                                </span>
                                <button
                                    class="kt-btn kt-btn-sm kt-btn-ghost text-destructive"
                                    type="button"
                                    @click="removeQuestion(qIndex)"
                                >
                                    Remove
                                </button>
                            </div>
                            <div class="p-4 flex flex-col gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="kt-form-label">Question</label>
                                    <textarea
                                        v-model="question.prompt"
                                        class="kt-input"
                                        rows="2"
                                        placeholder="What should students answer?"
                                        required
                                    />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">
                                        Answers — select one as correct
                                    </p>
                                    <div class="flex flex-col gap-2">
                                        <div
                                            v-for="(option, oIndex) in question.options"
                                            :key="oIndex"
                                            class="flex items-stretch gap-2 rounded-lg border transition-colors"
                                            :class="option.is_correct
                                                ? 'border-primary bg-primary/5'
                                                : 'border-border bg-background'"
                                        >
                                            <button
                                                type="button"
                                                class="shrink-0 flex flex-col items-center justify-center gap-1 px-3 py-2 border-e border-inherit min-w-[4.5rem] hover:bg-accent/50"
                                                :class="option.is_correct ? 'text-primary' : 'text-muted-foreground'"
                                                :title="option.is_correct ? 'Correct answer' : 'Mark as correct'"
                                                @click="setCorrectOption(qIndex, oIndex)"
                                            >
                                                <i
                                                    class="ki-filled text-lg"
                                                    :class="option.is_correct ? 'ki-check-circle' : 'ki-abstract-26'"
                                                />
                                                <span class="text-[10px] font-medium uppercase leading-none">
                                                    {{ option.is_correct ? 'Correct' : 'Set' }}
                                                </span>
                                            </button>
                                            <input
                                                v-model="option.label"
                                                class="kt-input border-0 bg-transparent grow min-w-0 rounded-none shadow-none focus:ring-0"
                                                type="text"
                                                placeholder="Answer choice"
                                            />
                                            <button
                                                v-if="question.options.length > 2"
                                                class="kt-btn kt-btn-sm kt-btn-ghost shrink-0 self-center me-1 text-muted-foreground"
                                                type="button"
                                                aria-label="Remove answer"
                                                @click="removeOption(qIndex, oIndex)"
                                            >
                                                <i class="ki-filled ki-cross" />
                                            </button>
                                        </div>
                                    </div>
                                    <button class="kt-btn kt-btn-sm kt-btn-ghost mt-2" type="button" @click="addOption(qIndex)">
                                        <i class="ki-filled ki-plus" />
                                        Add answer choice
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-xl border border-dashed border-border py-10 px-6 text-center"
                    >
                        <p class="text-sm font-medium text-mono mb-1">
                            No quiz questions yet
                        </p>
                        <p class="text-sm text-secondary-foreground mb-4 max-w-md mx-auto">
                            Leave this empty if the lesson has no quiz. Students will go straight to the next lesson after the content.
                        </p>
                        <button class="kt-btn kt-btn-outline" type="button" @click="addQuestion">
                            Add first question
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 sticky bottom-0 py-4 bg-background/95 border-t border-border -mx-1 px-1">
                <button class="kt-btn kt-btn-primary" type="submit" :disabled="form.processing">
                    Save lesson
                </button>
                <Link :href="'/courses/' + course.id + '/edit'" class="kt-btn kt-btn-outline">
                    Cancel
                </Link>
            </div>
        </form>
    </div>
</template>
