<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    lesson: { type: Object, required: true },
    course: { type: Object, required: true },
    section: { type: Object, required: true },
    lesson_types: { type: Object, required: true },
});

const blankOption = () => ({ id: null, label: '', is_correct: false });

const blankQuestion = () => ({
    id: null,
    prompt: '',
    options: [blankOption(), blankOption()],
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
    quiz_pass_percent: props.lesson.quiz_pass_percent,
    is_preview: props.lesson.is_preview,
    video: null,
    quiz_questions: initialQuestions,
});

const onVideo = (event) => {
    const file = event.target.files[0];
    form.video = file || null;
};

const addQuestion = () => {
    form.quiz_questions.push(blankQuestion());
};

const removeQuestion = (index) => {
    form.quiz_questions.splice(index, 1);
};

const addOption = (questionIndex) => {
    form.quiz_questions[questionIndex].options.push(blankOption());
};

const removeOption = (questionIndex, optionIndex) => {
    form.quiz_questions[questionIndex].options.splice(optionIndex, 1);
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
                Edit lesson
            </h1>
        </div>

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="kt-card">
                <div class="kt-card-content p-5 lg:p-8 flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Title</label>
                        <input v-model="form.title" class="kt-input" type="text" required />
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Type</label>
                            <select v-model="form.type" class="kt-select">
                                <option v-for="(label, value) in lesson_types" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Duration (seconds)</label>
                            <input v-model="form.duration_seconds" class="kt-input" type="number" min="0" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Summary</label>
                        <textarea v-model="form.summary" class="kt-input" rows="3" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Content (article body)</label>
                        <textarea v-model="form.content" class="kt-input" rows="6" />
                    </div>
                    <div v-if="form.type === 'video'" class="flex flex-col gap-2">
                        <label class="kt-form-label">Video file (MP4 / WebM)</label>
                        <video
                            v-if="lesson.video_url && !form.video"
                            :src="lesson.video_url"
                            class="w-full max-h-64 rounded-lg bg-black"
                            controls
                        />
                        <input class="kt-input" type="file" accept="video/mp4,video/webm" @change="onVideo" />
                    </div>
                    <label class="kt-label">
                        <input v-model="form.is_preview" class="kt-checkbox kt-checkbox-sm" type="checkbox" />
                        <span class="kt-checkbox-label">Free preview lesson</span>
                    </label>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header px-5 py-4 flex items-center justify-between">
                    <h3 class="kt-card-title text-sm font-semibold">
                        Quiz questions
                    </h3>
                    <button class="kt-btn kt-btn-sm kt-btn-outline" type="button" @click="addQuestion">
                        Add question
                    </button>
                </div>
                <div class="kt-card-content p-5 flex flex-col gap-6">
                    <label class="kt-label">
                        <input v-model="form.require_quiz_to_complete" class="kt-checkbox kt-checkbox-sm" type="checkbox" />
                        <span class="kt-checkbox-label">Require passing quiz to complete lesson</span>
                    </label>
                    <div v-if="form.require_quiz_to_complete" class="flex flex-col gap-1 max-w-xs">
                        <label class="kt-form-label">Pass score (%)</label>
                        <input v-model.number="form.quiz_pass_percent" class="kt-input" type="number" min="1" max="100" />
                    </div>

                    <div
                        v-for="(question, qIndex) in form.quiz_questions"
                        :key="qIndex"
                        class="border border-border rounded-xl p-4"
                    >
                        <div class="flex justify-between gap-3 mb-3">
                            <span class="text-sm font-medium text-mono">Question {{ qIndex + 1 }}</span>
                            <button class="kt-btn kt-btn-sm kt-btn-ghost text-destructive" type="button" @click="removeQuestion(qIndex)">
                                Remove
                            </button>
                        </div>
                        <textarea v-model="question.prompt" class="kt-input mb-4" rows="2" placeholder="Question prompt" />
                        <div class="flex flex-col gap-2">
                            <div
                                v-for="(option, oIndex) in question.options"
                                :key="oIndex"
                                class="flex items-center gap-2"
                            >
                                <input
                                    :checked="option.is_correct"
                                    class="kt-radio"
                                    type="radio"
                                    :name="'correct-' + qIndex"
                                    @change="setCorrectOption(qIndex, oIndex)"
                                />
                                <input v-model="option.label" class="kt-input grow" type="text" placeholder="Answer option" />
                                <button
                                    v-if="question.options.length > 2"
                                    class="kt-btn kt-btn-sm kt-btn-ghost"
                                    type="button"
                                    @click="removeOption(qIndex, oIndex)"
                                >
                                    ×
                                </button>
                            </div>
                            <button class="kt-btn kt-btn-sm kt-btn-ghost self-start" type="button" @click="addOption(qIndex)">
                                Add option
                            </button>
                        </div>
                    </div>
                    <p v-if="form.quiz_questions.length === 0" class="text-sm text-secondary-foreground">
                        No quiz questions. Students will skip the quiz step.
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
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
