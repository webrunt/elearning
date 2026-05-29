<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    reviews: { type: Object, required: true },
});

const rejectingId = ref(null);
const rejectForm = useForm({
    moderation_note: '',
});

const startReject = (reviewId) => {
    rejectingId.value = reviewId;
    rejectForm.moderation_note = '';
};

const cancelReject = () => {
    rejectingId.value = null;
    rejectForm.reset();
};

const approve = (reviewId) => {
    router.post('/reviews/' + reviewId + '/approve', {}, { preserveScroll: true });
};

const submitReject = (reviewId) => {
    rejectForm.post('/reviews/' + reviewId + '/reject', {
        preserveScroll: true,
        onSuccess: () => cancelReject(),
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
    <Head title="Review moderation" />
    <div class="kt-container-fixed py-5 lg:py-7.5">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-mono">
                Student review moderation
            </h1>
            <p class="text-sm text-secondary-foreground">
                Approve or reject course ratings and comments from enrolled students.
            </p>
        </div>

        <div v-if="reviews.data.length === 0" class="kt-card">
            <div class="kt-card-content p-8 text-center text-secondary-foreground">
                No reviews are waiting for moderation.
            </div>
        </div>

        <div v-else class="flex flex-col gap-4">
            <div
                v-for="review in reviews.data"
                :key="review.id"
                class="kt-card"
            >
                <div class="kt-card-content p-5 flex flex-col gap-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-mono">
                                {{ review.course.title }}
                            </p>
                            <p class="text-xs text-secondary-foreground mt-1">
                                {{ review.user.name }} · {{ review.user.email }}
                                · {{ review.rating }} / 5 stars
                            </p>
                        </div>
                        <span class="kt-badge kt-badge-sm kt-badge-warning">
                            Pending
                        </span>
                    </div>

                    <p v-if="review.body" class="text-sm text-foreground whitespace-pre-wrap">
                        {{ review.body }}
                    </p>
                    <p v-else class="text-sm text-muted-foreground italic">
                        No written comment.
                    </p>

                    <div v-if="rejectingId === review.id" class="border border-border rounded-xl p-4 flex flex-col gap-3">
                        <label class="text-sm font-medium">
                            Rejection note (optional, emailed to student)
                        </label>
                        <textarea
                            v-model="rejectForm.moderation_note"
                            class="kt-input min-h-24"
                            rows="3"
                            placeholder="Explain why this review was not published…"
                        />
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="kt-btn kt-btn-danger"
                                :disabled="rejectForm.processing"
                                @click="submitReject(review.id)"
                            >
                                Confirm reject
                            </button>
                            <button type="button" class="kt-btn kt-btn-outline" @click="cancelReject">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div v-else class="flex flex-wrap gap-2">
                        <button type="button" class="kt-btn kt-btn-primary" @click="approve(review.id)">
                            Approve
                        </button>
                        <button type="button" class="kt-btn kt-btn-outline" @click="startReject(review.id)">
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
