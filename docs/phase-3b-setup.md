# Phase 3b — Video processing (PHP-FFMpeg)

After an instructor uploads a lesson video, a queued job probes the file with **FFprobe** (duration, validity) and optionally extracts an **MP3** audio track for future speech-to-text (Phase 3c).

## Requirements

1. **Composer package:** `php-ffmpeg/php-ffmpeg` (installed with the app).
2. **System binaries:** `ffmpeg` and `ffprobe` on the server PATH, or explicit paths in `.env`.

### macOS (Homebrew)

```bash
brew install ffmpeg
ffmpeg -version
ffprobe -version
```

### Ubuntu / Debian

```bash
sudo apt update && sudo apt install -y ffmpeg
```

## Large video uploads

### `PostTooLargeException` — The POST data is too large

This happens **before** Laravel runs. PHP’s **`post_max_size`** must be **larger than the entire form** (video file + summary + quiz fields). If your video is 160 MB but `post_max_size` is 64M, you will see this error.

Check current limits (CLI may differ from the web server):

```bash
php -i | grep -E "post_max_size|upload_max_filesize|memory_limit"
```

**Fix:** edit the **php.ini used by your web server** (Valet/Herd/nginx-php-fpm), not only CLI:

```ini
upload_max_filesize = 256M
post_max_size = 260M
memory_limit = 512M
```

Rules of thumb:

- `post_max_size` ≥ video size + ~10 MB (form overhead)
- `post_max_size` ≥ `upload_max_filesize`
- `memory_limit` ≥ `post_max_size` (often 512M for large uploads)

Homebrew: `/opt/homebrew/etc/php/8.x/php.ini` then `valet restart` or restart php-fpm.

### 500 / memory exhausted

If the log shows `memory exhausted`, also set in `.env`:

```env
LESSON_VIDEO_MAX_KB=204800
LESSON_VIDEO_MEMORY_LIMIT=512M
```

## Environment

Add to `.env` (see `.env.example`):

```env
FFMPEG_BINARIES=
FFPROBE_BINARIES=
FFMPEG_TIMEOUT=3600
FFMPEG_EXTRACT_AUDIO=true
```

Leave `FFMPEG_BINARIES` and `FFPROBE_BINARIES` empty when binaries are on PATH. If processing is skipped, check logs and install ffmpeg.

## Queue worker

Video processing runs on the queue (`ProcessLessonVideo` job). Local dev with `composer dev` already runs `queue:listen`. Production must run a worker, e.g.:

```bash
php artisan queue:work
```

## Storage

- Videos: `storage/app/public/courses/{course_id}/videos/`
- Extracted audio: `storage/app/public/courses/{course_id}/audio/{lesson_id}.mp3`
- Temp files: `storage/app/ffmpeg-tmp/` (created automatically)

## Admin UI

On **Lesson edit**, after uploading a video you should see a processing badge. Duration is filled automatically when status is **completed**. If ffmpeg is missing, status is **skipped** and you can enter duration manually.

## Tests

```bash
php artisan test --filter=LessonVideoProcessing
```

Tests mock `LessonVideoProcessor` so ffmpeg is not required in CI.
