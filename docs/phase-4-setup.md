# Phase 4 — Trust and admin ops

Student course reviews, admin moderation, user role management, and email notifications for key workflow events.

## What was added

- **`course_reviews` table** — one review per enrolled student per course; statuses: `pending`, `approved`, `rejected`
- **Student** — submit or update a review on the course detail page (`/courses/{slug}`) when enrolled
- **Admin** — **Review moderation** at `/reviews/pending` (approve / reject with optional note)
- **Admin** — **Users** at `/users` (search, change role via dropdown)
- **Notifications** (queued mail) — course submitted for review, approved, rejected; student review submitted (to admins), moderated (to student)

## Local setup

```bash
php artisan migrate
php artisan queue:work   # required for queued notifications and video jobs
```

Configure mail in `.env` (e.g. `MAIL_MAILER=log` for local) so notifications are visible in `storage/logs/laravel.log` or Mailpit.

## Demo flows

1. **Student review** — Sign in as `student@elearning.local`, enroll in a free published course, open course detail, submit a rating under **Reviews**.
2. **Moderation** — Sign in to admin as `admin@elearning.local`, open **Review moderation**, approve or reject pending items.
3. **Users** — Admin → **Users**, change a user’s role (e.g. student → instructor).
4. **Course workflow mail** — Instructor submits course for review → admins receive email; approve/reject → instructor receives email.

## Sidebar badges (admin)

- **Pending review** — courses awaiting publish approval (`pending_review_count`)
- **Review moderation** — student reviews awaiting moderation (`pending_course_reviews_count`)

## Phase 3c

Speech-to-text (Deepgram / Speechmatics) is **deferred**; Phase 3b MP3 extract can feed STT when you pick it up.

## Tests

```bash
php artisan test --filter=Phase4TrustAndAdminTest
```
