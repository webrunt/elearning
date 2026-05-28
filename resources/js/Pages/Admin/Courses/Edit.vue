<script setup>
import RichTextEditor from '@/components/Admin/RichTextEditor.vue';
import { useConfirmModal } from '@/composables/useConfirmModal';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const confirmModal = useConfirmModal();

const props = defineProps({
    course: { type: Object, required: true },
    categories: { type: Array, required: true },
    statuses: { type: Object, required: true },
    instructors: { type: Array, required: true },
    can_pick_instructor: { type: Boolean, default: false },
    is_admin: { type: Boolean, default: false },
    can_submit_for_review: { type: Boolean, default: false },
    can_review: { type: Boolean, default: false },
});

const form = useForm({
    title: props.course.title,
    summary: props.course.summary || '',
    category_id: props.course.category_id || '',
    status: props.is_admin ? props.course.status : undefined,
    instructor_id: props.course.instructor_id || '',
    thumbnail: null,
});

const sectionTitle = ref('');
const newLessonTitle = ref('');
const newLessonType = ref('video');
const lessonFormSectionId = ref(null);
const lessonFormProcessing = ref(false);

const lessonTypeOptions = [
    {
        value: 'video',
        label: 'Video lesson',
        description: 'You will upload the video file on the lesson editor screen.',
    },
    {
        value: 'article',
        label: 'Article lesson',
        description: 'You will write the lesson with the rich text editor.',
    },
];

const selectedLessonTypeHelp = computed(() => {
    for (let i = 0; i < lessonTypeOptions.length; i++) {
        if (lessonTypeOptions[i].value === newLessonType.value) {
            return lessonTypeOptions[i].description;
        }
    }

    return '';
});

const totalLessons = computed(() => {
    let count = 0;

    for (let i = 0; i < props.course.sections.length; i++) {
        count += props.course.sections[i].lessons.length;
    }

    return count;
});

const lessonTypeLabel = (type) => {
    if (type === 'video') {
        return 'Video';
    }

    if (type === 'article') {
        return 'Article';
    }

    return type;
};

const openLessonForm = (sectionId) => {
    lessonFormSectionId.value = sectionId;
    newLessonTitle.value = '';
    newLessonType.value = 'video';
};

const closeLessonForm = () => {
    lessonFormSectionId.value = null;
};

const onThumbnail = (event) => {
    const file = event.target.files[0];
    form.thumbnail = file || null;
};

const saveCourse = () => {
    form.put('/courses/' + props.course.id, {
        forceFormData: true,
    });
};

const addSection = () => {
    if (sectionTitle.value.trim() === '') {
        return;
    }

    router.post('/courses/' + props.course.id + '/sections', {
        title: sectionTitle.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            sectionTitle.value = '';
        },
    });
};

const addLesson = (sectionId) => {
    if (newLessonTitle.value.trim() === '') {
        return;
    }

    lessonFormProcessing.value = true;

    router.post('/sections/' + sectionId + '/lessons', {
        title: newLessonTitle.value,
        type: newLessonType.value,
    }, {
        onSuccess: () => {
            newLessonTitle.value = '';
            closeLessonForm();
        },
        onFinish: () => {
            lessonFormProcessing.value = false;
        },
    });
};

const deleteSection = async (section) => {
    const confirmed = await confirmModal.confirm({
        title: 'Delete section',
        message: 'Delete "' + section.title + '" and all lessons inside it? This cannot be undone.',
        confirmLabel: 'Delete section',
        cancelLabel: 'Cancel',
        variant: 'danger',
    });

    if (!confirmed) {
        return;
    }

    router.delete('/courses/' + props.course.id + '/sections/' + section.id, {
        preserveScroll: true,
    });
};

const submitForReview = async () => {
    const confirmed = await confirmModal.confirm({
        title: 'Submit for review',
        message: 'Send "' + props.course.title + '" to admins for approval? You will not be able to publish it yourself.',
        confirmLabel: 'Submit',
        cancelLabel: 'Cancel',
        variant: 'primary',
    });

    if (!confirmed) {
        return;
    }

    router.post('/courses/' + props.course.id + '/submit-review', {}, {
        preserveScroll: true,
    });
};

const deleteCourse = async () => {
    const confirmed = await confirmModal.confirm({
        title: 'Delete course',
        message: 'Delete "' + props.course.title + '" permanently? All sections, lessons, and uploads will be removed.',
        confirmLabel: 'Delete course',
        cancelLabel: 'Cancel',
        variant: 'danger',
    });

    if (!confirmed) {
        return;
    }

    router.delete('/courses/' + props.course.id);
};
</script>

<script>
import AdminLayout from '@/Pages/Layouts/AdminDashboardLayout.vue';

export default {
    layout: AdminLayout,
};
</script>

<template>
    <Head :title="'Edit: ' + course.title" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <Link href="/courses" class="text-sm link mb-2 inline-block">
                    ← Courses
                </Link>
                <h1 class="text-xl font-semibold text-mono">
                    {{ course.title }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ course.slug }}
                </p>
            </div>
            <button class="kt-btn kt-btn-outline text-destructive" type="button" @click="deleteCourse">
                Delete course
            </button>
        </div>

        <div
            v-if="course.rejection_feedback"
            class="mb-6 rounded-xl border border-destructive/30 bg-destructive/5 p-4"
        >
            <p class="text-sm font-semibold text-destructive mb-1">
                Changes requested
            </p>
            <p class="text-sm whitespace-pre-wrap">
                {{ course.rejection_feedback }}
            </p>
        </div>

        <div
            v-if="course.status === 'pending_review'"
            class="mb-6 rounded-xl border border-primary/30 bg-primary/5 p-4 flex flex-wrap items-center justify-between gap-3"
        >
            <p class="text-sm">
                This course is awaiting admin review.
            </p>
            <Link
                v-if="can_review"
                :href="'/courses/' + course.id + '/review'"
                class="kt-btn kt-btn-sm kt-btn-primary"
            >
                Open review
            </Link>
        </div>

        <div
            v-if="course.review_summary && course.status === 'published'"
            class="mb-6 rounded-xl border border-border bg-accent/30 p-4"
        >
            <p class="text-sm font-semibold text-mono mb-1">
                Admin review summary
            </p>
            <p class="text-sm whitespace-pre-wrap">
                {{ course.review_summary }}
            </p>
        </div>

        <div class="grid xl:grid-cols-5 gap-6">
            <div class="xl:col-span-2">
                <form class="kt-card" @submit.prevent="saveCourse">
                    <div class="kt-card-header px-5 py-4">
                        <h3 class="kt-card-title text-sm font-semibold">
                            Course details
                        </h3>
                    </div>
                    <div class="kt-card-content p-5 flex flex-col gap-4">
                        <div v-if="course.thumbnail_url" class="rounded-lg overflow-hidden border border-border">
                            <img :src="course.thumbnail_url" alt="" class="w-full h-40 object-cover" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Title</label>
                            <input v-model="form.title" class="kt-input" type="text" required />
                        </div>
                        <RichTextEditor
                            v-model="form.summary"
                            variant="minimal"
                            label="Summary"
                            hint="Shown on the course page and in admin review."
                            placeholder="What students will learn in this course"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Category</label>
                            <select v-model="form.category_id" class="kt-select">
                                <option value="">None</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                        <div v-if="is_admin" class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <select v-model="form.status" class="kt-select">
                                <option v-for="(label, value) in statuses" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div v-else class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <p class="text-sm font-medium text-mono py-2">
                                {{ course.status_label }}
                            </p>
                        </div>
                        <div v-if="can_pick_instructor" class="flex flex-col gap-1">
                            <label class="kt-form-label">Instructor</label>
                            <select v-model="form.instructor_id" class="kt-select">
                                <option v-for="inst in instructors" :key="inst.id" :value="inst.id">
                                    {{ inst.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Replace thumbnail</label>
                            <input class="kt-input" type="file" accept="image/*" @change="onThumbnail" />
                        </div>
                        <button class="kt-btn kt-btn-primary" type="submit" :disabled="form.processing">
                            Save details
                        </button>
                        <button
                            v-if="can_submit_for_review"
                            class="kt-btn kt-btn-outline"
                            type="button"
                            @click="submitForReview"
                        >
                            Submit for review
                        </button>
                    </div>
                </form>
            </div>

            <div class="xl:col-span-3">
                <div class="kt-card">
                    <div class="kt-card-header px-5 py-4 border-b border-border">
                        <h3 class="kt-card-title text-sm font-semibold">
                            Curriculum
                        </h3>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ course.sections.length }} section<span v-if="course.sections.length !== 1">s</span>
                            · {{ totalLessons }} lesson<span v-if="totalLessons !== 1">s</span>
                        </p>
                    </div>
                    <div class="kt-card-content p-5 flex flex-col gap-6">
                        <div class="rounded-xl border border-border bg-accent/30 p-4">
                            <p class="text-sm font-medium text-mono mb-2">
                                How to build your course
                            </p>
                            <ol class="text-sm text-secondary-foreground flex flex-col gap-1.5 list-decimal list-inside">
                                <li>Add a <strong class="text-mono font-medium">section</strong> (a chapter or module).</li>
                                <li>Add <strong class="text-mono font-medium">lessons</strong> inside each section.</li>
                                <li>Open each lesson to add video, article text, and optional quiz.</li>
                            </ol>
                        </div>

                        <form class="flex gap-2" @submit.prevent="addSection">
                            <input
                                v-model="sectionTitle"
                                class="kt-input grow"
                                type="text"
                                placeholder="New section title"
                            />
                            <button class="kt-btn kt-btn-primary shrink-0" type="submit">
                                Add section
                            </button>
                        </form>

                        <div v-for="section in course.sections" :key="section.id" class="border border-border rounded-xl p-4">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h4 class="font-semibold text-mono">
                                    {{ section.title }}
                                </h4>
                                <button
                                    class="kt-btn kt-btn-sm kt-btn-ghost text-destructive"
                                    type="button"
                                    @click="deleteSection(section)"
                                >
                                    Remove
                                </button>
                            </div>

                            <ul class="flex flex-col gap-2 mb-4">
                                <li
                                    v-for="lesson in section.lessons"
                                    :key="lesson.id"
                                    class="flex items-center justify-between gap-3 p-3 rounded-lg bg-accent/40"
                                >
                                    <div>
                                        <p class="text-sm font-medium text-mono">
                                            {{ lesson.title }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ lessonTypeLabel(lesson.type) }}
                                            <span v-if="lesson.is_preview"> · Free preview</span>
                                        </p>
                                    </div>
                                    <Link
                                        :href="'/lessons/' + lesson.id + '/edit'"
                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                    >
                                        Edit content
                                    </Link>
                                </li>
                                <li v-if="section.lessons.length === 0" class="text-sm text-secondary-foreground">
                                    No lessons yet.
                                </li>
                            </ul>

                            <div
                                v-if="lessonFormSectionId === section.id"
                                class="rounded-xl border border-primary/30 bg-primary/5 p-4 flex flex-col gap-4"
                            >
                                <div>
                                    <p class="text-sm font-semibold text-mono">
                                        New lesson in “{{ section.title }}”
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        Step 1 of 2 — name and type. Next you’ll add content on the lesson editor.
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="kt-form-label">Lesson title</label>
                                    <input
                                        v-model="newLessonTitle"
                                        class="kt-input"
                                        type="text"
                                        placeholder="e.g. Introduction to variables"
                                        @keydown.enter.prevent="addLesson(section.id)"
                                    />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-form-label">Lesson type</label>
                                    <div class="grid sm:grid-cols-2 gap-2">
                                        <button
                                            v-for="option in lessonTypeOptions"
                                            :key="option.value"
                                            type="button"
                                            class="rounded-lg border p-3 text-left text-sm transition-colors"
                                            :class="newLessonType === option.value
                                                ? 'border-primary bg-background ring-1 ring-primary/20'
                                                : 'border-border hover:bg-accent/40'"
                                            @click="newLessonType = option.value"
                                        >
                                            <span class="font-medium text-mono block">{{ option.label }}</span>
                                        </button>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        {{ selectedLessonTypeHelp }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        class="kt-btn kt-btn-primary"
                                        type="button"
                                        :disabled="lessonFormProcessing || newLessonTitle.trim() === ''"
                                        @click="addLesson(section.id)"
                                    >
                                        Create lesson & edit content
                                    </button>
                                    <button
                                        class="kt-btn kt-btn-ghost"
                                        type="button"
                                        :disabled="lessonFormProcessing"
                                        @click="closeLessonForm"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                            <button
                                v-else
                                class="kt-btn kt-btn-sm kt-btn-outline w-full sm:w-auto"
                                type="button"
                                @click="openLessonForm(section.id)"
                            >
                                <i class="ki-filled ki-plus" />
                                Add lesson to this section
                            </button>
                        </div>

                        <p v-if="course.sections.length === 0" class="text-sm text-secondary-foreground text-center py-6">
                            Add a section to start building your curriculum.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
