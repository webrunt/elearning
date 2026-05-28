<script setup>
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import { computed } from 'vue';
import { ClassicEditor, getCKEditorConfig } from '@/config/ckeditorConfigs';
import 'ckeditor5/ckeditor5.css';

const props = defineProps({
    modelValue: { type: String, default: '' },
    variant: {
        type: String,
        default: 'full',
        validator: (value) => value === 'full' || value === 'minimal',
    },
    label: { type: String, default: '' },
    hint: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const config = computed(() => getCKEditorConfig(props.variant, props.placeholder));

const onUpdate = (value) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <label v-if="label" class="kt-form-label">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </label>
        <p v-if="hint" class="text-xs text-muted-foreground -mt-0.5 mb-1">
            {{ hint }}
        </p>
        <div
            class="rich-text-editor rounded-lg border border-border bg-background overflow-hidden"
            :class="{ 'border-destructive ring-1 ring-destructive/30': error }"
        >
            <Ckeditor
                :editor="ClassicEditor"
                :model-value="modelValue"
                :config="config"
                :disabled="disabled"
                @update:model-value="onUpdate"
            />
        </div>
        <p v-if="error" class="text-sm text-destructive">
            {{ error }}
        </p>
    </div>
</template>

<style>
.rich-text-editor .ck-editor__editable {
    min-height: 10rem;
}

.rich-text-editor .ck.ck-editor__main > .ck-editor__editable {
    border: 0;
    border-radius: 0;
    box-shadow: none;
}

.rich-text-editor .ck.ck-toolbar {
    border: 0;
    border-bottom: 1px solid var(--border);
    border-radius: 0;
    background: var(--accent);
}
</style>
