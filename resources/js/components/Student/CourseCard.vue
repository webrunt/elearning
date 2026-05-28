<script setup>
import { stripHtml } from '@/utils/stripHtml';
import { Link } from '@inertiajs/vue3';

defineProps({
    course: { type: Object, required: true },
    showEnrolled: { type: Boolean, default: true },
});
</script>

<template>
    <Link
        :href="'/courses/' + course.slug"
        class="kt-card hover:shadow-md transition-shadow block h-full"
    >
        <div class="kt-card-content p-0 flex flex-col h-full">
            <div class="h-44 rounded-t-xl bg-muted/50 overflow-hidden shrink-0">
                <img
                    v-if="course.thumbnail_url"
                    :src="course.thumbnail_url"
                    :alt="course.title"
                    class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                    <i class="ki-filled ki-book text-3xl text-muted-foreground" />
                </div>
            </div>
            <div class="p-5 flex flex-col grow">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span v-if="course.category" class="kt-badge kt-badge-xs kt-badge-light">
                        {{ course.category.name || course.category }}
                    </span>
                    <span
                        v-if="showEnrolled && course.is_enrolled"
                        class="kt-badge kt-badge-xs kt-badge-primary kt-badge-outline"
                    >
                        Enrolled
                    </span>
                </div>
                <h3 class="font-semibold text-mono mb-2 line-clamp-2">
                    {{ course.title }}
                </h3>
                <p v-if="course.summary" class="text-sm text-secondary-foreground line-clamp-2 mb-3 grow">
                    {{ stripHtml(course.summary) }}
                </p>
                <div class="flex items-center justify-between text-sm mt-auto pt-2 border-t border-border">
                    <span class="text-muted-foreground">
                        {{ course.lessons_count }} lesson<span v-if="course.lessons_count !== 1">s</span>
                    </span>
                    <span class="font-medium text-primary">
                        {{ course.is_free !== false ? 'Free' : course.price }}
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>
