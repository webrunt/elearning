# Phase 2 — Student catalog & free enrollment

## Student URLs (main domain `APP_URL`)

| URL | Who | Description |
|-----|-----|-------------|
| `/courses` | Public | Published course catalog (search + category filter) |
| `/courses/{slug}` | Public | Course detail + curriculum outline |
| `/courses/{slug}/enroll` | Student (POST) | Free enrollment |
| `/my-learning` | Student | Enrolled courses with progress % |

## Flow

1. Browse **Courses** or home page featured list.
2. Open a **published** course (status `published`, price empty or 0).
3. **Sign in** or register as a student.
4. Click **Enroll for free** → redirected to **My learning**.
5. Progress % = completed lessons ÷ total lessons (0% until Phase 3 player marks lessons complete).

## Demo data

After `php artisan migrate:fresh --seed`:

- Published demo: **Introduction to Web Development** (`/courses/intro-to-web-development`)
- Student: `student@elearning.local` / `password`

To publish your own course: admin → set status **Published** and leave price empty.

## Phase 3

See [phase-3-setup.md](phase-3-setup.md) for the lesson player and quizzes.
