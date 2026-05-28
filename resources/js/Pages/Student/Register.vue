<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
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
                    Create account
                </h3>
                <div class="flex items-center justify-center font-medium">
                    <span class="text-sm text-secondary-foreground me-1.5">
                        Already have an account?
                    </span>
                    <Link class="text-sm link" href="/login">
                        Sign in
                    </Link>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="name">
                    Name
                </label>
                <input id="name" v-model="form.name" class="kt-input" type="text" autocomplete="name" />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                    {{ form.errors.name }}
                </p>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="email">
                    Email
                </label>
                <input id="email" v-model="form.email" class="kt-input" type="email" autocomplete="username" />
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
                        autocomplete="new-password"
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
                <p v-if="form.errors.password" class="text-sm text-destructive">
                    {{ form.errors.password }}
                </p>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="password_confirmation">
                    Confirm password
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    class="kt-input"
                    type="password"
                    autocomplete="new-password"
                />
            </div>
            <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit" :disabled="form.processing">
                Sign up
            </button>
            <p class="text-center text-sm">
                <Link href="/" class="link">
                    ← Back to home
                </Link>
            </p>
        </form>
    </div>
</template>
