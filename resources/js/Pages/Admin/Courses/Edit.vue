<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    categories: { type: Array, required: true },
    statuses: { type: Object, required: true },
    instructors: { type: Array, required: true },
    can_pick_instructor: { type: Boolean, default: false },
});

const form = useForm({
    title: props.course.title,
    summary: props.course.summary || '',
    category_id: props.course.category_id || '',
    status: props.course.status,
    instructor_id: props.course.instructor_id || '',
    thumbnail: null,
});

const sectionTitle = ref('');
const newLessonTitle = ref('');
const newLessonType = ref('video');
const activeSectionId = ref(null);

const onThumbnail = (event) => {
    const file = event.target.files[0];
    form.thumbnail = file || null;
};

const saveCourse = () => {
    form.post('/courses/' + props.course.id, {
        forceFormData: true,
        _method: 'put',
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

    router.post('/sections/' + sectionId + '/lessons', {
        title: newLessonTitle.value,
        type: newLessonType.value,
    }, {
        onSuccess: () => {
            newLessonTitle.value = '';
            activeSectionId.value = null;
        },
    });
};

const deleteSection = (section) => {
    if (!window.confirm('Delete section "' + section.title + '" and all its lessons?')) {
        return;
    }

    router.delete('/courses/' + props.course.id + '/sections/' + section.id, {
        preserveScroll: true,
    });
};

const deleteCourse = () => {
    if (!window.confirm('Delete this course permanently?')) {
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
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Summary</label>
                            <textarea v-model="form.summary" class="kt-input" rows="4" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Category</label>
                            <select v-model="form.category_id" class="kt-select">
                                <option value="">None</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <select v-model="form.status" class="kt-select">
                                <option v-for="(label, value) in statuses" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
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
                    </div>
                </form>
            </div>

            <div class="xl:col-span-3">
                <div class="kt-card">
                    <div class="kt-card-header px-5 py-4 flex items-center justify-between">
                        <h3 class="kt-card-title text-sm font-semibold">
                            Curriculum
                        </h3>
                    </div>
                    <div class="kt-card-content p-5 flex flex-col gap-6">
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
                                            {{ lesson.type }}
                                            <span v-if="lesson.is_preview"> · Preview</span>
                                        </p>
                                    </div>
                                    <Link
                                        :href="'/lessons/' + lesson.id + '/edit'"
                                        class="kt-btn kt-btn-sm kt-btn-outline"
                                    >
                                        Edit
                                    </Link>
                                </li>
                                <li v-if="section.lessons.length === 0" class="text-sm text-secondary-foreground">
                                    No lessons yet.
                                </li>
                            </ul>

                            <div v-if="activeSectionId === section.id" class="flex flex-col sm:flex-row gap-2">
                                <input
                                    v-model="newLessonTitle"
                                    class="kt-input grow"
                                    type="text"
                                    placeholder="Lesson title"
                                />
                                <select v-model="newLessonType" class="kt-select w-32">
                                    <option value="video">Video</option>
                                    <option value="article">Article</option>
                                </select>
                                <button class="kt-btn kt-btn-sm kt-btn-primary" type="button" @click="addLesson(section.id)">
                                    Add
                                </button>
                                <button class="kt-btn kt-btn-sm kt-btn-ghost" type="button" @click="activeSectionId = null">
                                    Cancel
                                </button>
                            </div>
                            <button
                                v-else
                                class="kt-btn kt-btn-sm kt-btn-outline"
                                type="button"
                                @click="activeSectionId = section.id; newLessonTitle = ''"
                            >
                                <i class="ki-filled ki-plus" />
                                Add lesson
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
