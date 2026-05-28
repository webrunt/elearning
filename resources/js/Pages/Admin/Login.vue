<script setup>
import { useForm } from '@inertiajs/vue3';

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
import LoginLayout from '@/Pages/Layouts/AdminLoginLayout.vue';

export default {
    layout: LoginLayout,
};
</script>

<template>
    <div class="kt-card max-w-[370px] w-full">
        <form class="kt-card-content flex flex-col gap-5 p-10" @submit.prevent="submit">
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                    Staff sign in
                </h3>
                <p class="text-sm text-secondary-foreground">
                    Admin and instructor accounts only
                </p>
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
                    placeholder="admin@elearning.local"
                />
                <p v-if="form.errors.email" class="text-sm text-destructive">
                    {{ form.errors.email }}
                </p>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="password">
                    Password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    class="kt-input"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter password"
                />
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
        </form>
    </div>
</template>
