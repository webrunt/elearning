# Phase 1 — Course management (admin)

## Admin URLs

| URL | Who |
|-----|-----|
| https://admin.elearning.local/courses | All staff |
| https://admin.elearning.local/courses/create | All staff |
| https://admin.elearning.local/courses/pending | Admin / super_admin — approval queue |
| https://admin.elearning.local/courses/{id}/review | Admin / super_admin — review summary + approve/reject |
| https://admin.elearning.local/categories | Admin / super_admin only |

## Workflow

1. **Admin:** seed categories at `/categories` (or use defaults from seeder).
2. **Instructor / admin:** create a course → add **sections** → add **lessons** per section.
3. **Lesson editor:** upload video (MP4/WebM), summary, article content, quiz questions with one correct answer per question.
4. **Instructor:** when the course has a summary, sections, and lessons, use **Submit for review** on the course edit page. Status becomes `pending_review`.
5. **Admin / super_admin:** open **Pending review** in the sidebar → **Review** → checklist stats, curriculum, then **Approve & publish** (requires an admin review summary) or **Request changes** (feedback returned to instructor as draft).
6. **Admin** can still set status directly on the edit form; instructors cannot publish themselves.

## Storage

Run once if not done:

```bash
php artisan storage:link
```

Uploads:

- Thumbnails → `storage/app/public/courses/thumbnails`
- Videos → `storage/app/public/courses/{course_id}/videos`

## Phase 1c (UX)

See [phase-1c-setup.md](phase-1c-setup.md) for CKEditor rich text and the lesson creation flow.

## Demo

Log in as `instructor@elearning.local` / `password` on the admin portal.
