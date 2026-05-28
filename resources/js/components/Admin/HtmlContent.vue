<script setup>
defineProps({
    html: { type: String, default: '' },
    emptyText: { type: String, default: 'No content provided.' },
});
</script>

<template>
    <div
        v-if="html"
        class="html-content text-sm text-foreground"
        v-html="html"
    />
    <p v-else class="text-sm text-secondary-foreground">
        {{ emptyText }}
    </p>
</template>

<style scoped>
.html-content :deep(p) {
    margin-bottom: 0.75rem;
}

.html-content :deep(p:last-child) {
    margin-bottom: 0;
}

.html-content :deep(strong),
.html-content :deep(b) {
    font-weight: 600;
}

.html-content :deep(ul),
.html-content :deep(ol) {
    margin: 0.5rem 0 0.75rem 1.25rem;
    padding: 0;
}

.html-content :deep(li) {
    margin-bottom: 0.25rem;
}

.html-content :deep(a) {
    color: var(--primary);
    text-decoration: underline;
}

.html-content :deep(h3),
.html-content :deep(h4) {
    font-weight: 600;
    margin: 0.75rem 0 0.5rem;
}
</style>
