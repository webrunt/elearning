<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<script>
import StudentAuthLayout from '@/Pages/Layouts/StudentAuthLayout.vue';

export default {
    layout: StudentAuthLayout,
};
</script>

<template>
    <div class="kt-card max-w-[370px] w-full">
        <form class="kt-card-content flex flex-col gap-5 p-10" @submit.prevent="submit">
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                    Sign in
                </h3>
                <div class="flex items-center justify-center font-medium">
                    <span class="text-sm text-secondary-foreground me-1.5">
                        Need an account?
                    </span>
                    <Link class="text-sm link" href="/register">
                        Sign up
                    </Link>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="email">
                    Email
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    class="kt-input"
                    type="email"
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="text-sm text-destructive">
                    {{ form.errors.email }}
                </p>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="password">
                    Password
                </label>
                <div class="kt-input" data-kt-toggle-password="true">
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Enter password"
                    />
                    <button
                        class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5"
                        data-kt-toggle-password-trigger="true"
                        type="button"
                    >
                        <span class="kt-toggle-password-active:hidden">
                            <i class="ki-filled ki-eye text-muted-foreground" />
                        </span>
                        <span class="hidden kt-toggle-password-active:block">
                            <i class="ki-filled ki-eye-slash text-muted-foreground" />
                        </span>
                    </button>
                </div>
            </div>
            <label class="kt-label">
                <input v-model="form.remember" class="kt-checkbox kt-checkbox-sm" type="checkbox" />
                <span class="kt-checkbox-label">
                    Remember me
                </span>
            </label>
            <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit" :disabled="form.processing">
                Sign in
            </button>
            <p class="text-center text-sm">
                <Link href="/" class="link">
                    ← Back to home
                </Link>
            </p>
        </form>
    </div>
</template>
