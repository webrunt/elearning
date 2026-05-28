# Phase 1 — Course management (admin)

## Admin URLs

| URL | Who |
|-----|-----|
| https://admin.elearning.local/courses | All staff |
| https://admin.elearning.local/courses/create | All staff |
| https://admin.elearning.local/categories | Admin / super_admin only |

## Workflow

1. **Admin:** seed categories at `/categories` (or use defaults from seeder).
2. **Instructor / admin:** create a course → add **sections** → add **lessons** per section.
3. **Lesson editor:** upload video (MP4/WebM), summary, article content, quiz questions with one correct answer per question.
4. Set course **status** to `published` when ready (student catalog in Phase 2).

## Storage

Run once if not done:

```bash
php artisan storage:link
```

Uploads:

- Thumbnails → `storage/app/public/courses/thumbnails`
- Videos → `storage/app/public/courses/{course_id}/videos`

## Demo

Log in as `instructor@elearning.local` / `password` on the admin portal.
