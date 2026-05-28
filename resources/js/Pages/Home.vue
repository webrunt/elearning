<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Pages/Layouts/PublicLayout.vue';
</script>

<script>
export default {
    layout: PublicLayout,
};
</script>

<template>
    <section class="relative overflow-hidden border-b border-border">
        <div class="absolute inset-0 hero-bg opacity-90 dark:opacity-80" aria-hidden="true" />
        <div class="kt-container-fixed relative py-16 lg:py-24">
            <div class="max-w-3xl">
                <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline mb-4">
                    Free enrollment · Learn anywhere
                </span>
                <h1 class="text-4xl lg:text-5xl font-semibold text-mono leading-tight mb-5">
                    Master new skills with courses built for real progress
                </h1>
                <p class="text-lg text-secondary-foreground mb-8 max-w-2xl">
                    A Udemy-style learning experience: watch lessons, read summaries, check your understanding with quick quizzes, and pick up where you left off.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a class="kt-btn kt-btn-primary" href="#courses">
                        Explore courses
                    </a>
                    <Link class="kt-btn kt-btn-outline" href="/register">
                        Create free account
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="kt-container-fixed py-16 lg:py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl lg:text-3xl font-semibold text-mono mb-3">
                Everything you need to learn
            </h2>
            <p class="text-secondary-foreground">
                Built for students first — catalog and checkout arrive in the next phase.
            </p>
        </div>
        <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
            <div class="kt-card">
                <div class="kt-card-content p-6 flex flex-col gap-3">
                    <span class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary">
                        <i class="ki-filled ki-book text-xl" />
                    </span>
                    <h3 class="text-lg font-semibold text-mono">
                        Structured courses
                    </h3>
                    <p class="text-sm text-secondary-foreground">
                        Sections and lessons organized like the platforms you already know.
                    </p>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-6 flex flex-col gap-3">
                    <span class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary">
                        <i class="ki-filled ki-youtube text-xl" />
                    </span>
                    <h3 class="text-lg font-semibold text-mono">
                        Video lessons
                    </h3>
                    <p class="text-sm text-secondary-foreground">
                        Watch, resume, and track progress automatically as you learn.
                    </p>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-6 flex flex-col gap-3">
                    <span class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary">
                        <i class="ki-filled ki-questionnaire-tablet text-xl" />
                    </span>
                    <h3 class="text-lg font-semibold text-mono">
                        Quick quizzes
                    </h3>
                    <p class="text-sm text-secondary-foreground">
                        Short checks after each lesson so knowledge sticks.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="courses" class="bg-accent/30 border-y border-border">
        <div class="kt-container-fixed py-16 lg:py-20">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-mono mb-2">
                        Popular courses
                    </h2>
                    <p class="text-secondary-foreground">
                        Course catalog publishing is coming in Phase 2. Sign up now to be ready.
                    </p>
                </div>
                <Link class="kt-btn kt-btn-primary shrink-0" href="/register">
                    Get started free
                </Link>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="n in 3" :key="n" class="kt-card">
                    <div class="kt-card-content p-0">
                        <div class="h-40 rounded-t-xl bg-muted/50 flex items-center justify-center">
                            <i class="ki-filled ki-picture text-3xl text-muted-foreground" />
                        </div>
                        <div class="p-5">
                            <span class="kt-badge kt-badge-xs kt-badge-light mb-2">
                                Coming soon
                            </span>
                            <h3 class="font-semibold text-mono mb-1">
                                Course placeholder {{ n }}
                            </h3>
                            <p class="text-sm text-secondary-foreground mb-3">
                                Instructor-led · Multiple lessons
                            </p>
                            <span class="text-sm font-medium text-primary">
                                Free enrollment
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="kt-container-fixed py-16 lg:py-20">
        <div class="kt-card bg-primary/5 border-primary/20">
            <div class="kt-card-content p-8 lg:p-12 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 text-center lg:text-start">
                <div>
                    <h2 class="text-2xl font-semibold text-mono mb-2">
                        Ready to start learning?
                    </h2>
                    <p class="text-secondary-foreground">
                        Create your student account in under a minute.
                    </p>
                </div>
                <div class="flex flex-wrap justify-center lg:justify-end gap-3 shrink-0">
                    <Link class="kt-btn kt-btn-primary" href="/register">
                        Sign up free
                    </Link>
                    <Link class="kt-btn kt-btn-outline" href="/login">
                        Sign in
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.hero-bg {
    background-image: url('@assets/media/images/2600x1200/bg-10.png');
    background-size: cover;
    background-position: center;
}
:global(.dark) .hero-bg {
    background-image: url('@assets/media/images/2600x1200/bg-10-dark.png');
}
</style>
