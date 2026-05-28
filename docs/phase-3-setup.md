# Phase 3 — Learning experience

## Student URLs

| URL | Method | Description |
|-----|--------|-------------|
| `/learn/courses/{slug}/lessons/{lesson}` | GET | Lesson player (video/article, summary, quiz) |
| `/learn/courses/{slug}/continue` | GET | Redirect to next incomplete lesson (auth) |
| `/learn/lessons/{lesson}/video` | GET | Protected video stream |
| `/learn/lessons/{lesson}/progress` | PATCH | Save watch position / mark article read |
| `/learn/lessons/{lesson}/quiz` | GET | Random quiz questions (no correct flags) |
| `/learn/lessons/{lesson}/quiz` | POST | Submit answers, grade, complete lesson |

## Completion rules

1. **Video:** `watched_percent` ≥ 90% (auto-saved every 15s).
2. **Article:** click **Mark as read** (sets `content_completed_at`).
3. **Quiz (if required):** pass score ≥ `quiz_pass_percent` (default 70%). Up to 3 random questions per attempt.
4. When content + quiz (if any) are satisfied, `lesson_progress.completed_at` is set.

Course progress % (My learning) = completed lessons ÷ total lessons.

## Access

- **Enrolled** students: full course + progress + quiz.
- **Preview** lessons on published courses: viewable without enrollment (progress not saved until enrolled).
- Video stream uses `learn` policy (preview or enrolled).

## Try it

1. Enroll in a published course.
2. **Continue learning** from My learning or course page.
3. Complete an article lesson or watch 90% of a video.
4. Pass the quiz on lessons that require it (demo: *HTML structure basics* after re-seed).

```bash
php artisan migrate:fresh --seed
```
