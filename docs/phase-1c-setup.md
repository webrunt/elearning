# Phase 1c — Admin UX (rich text & lessons)

## Rich text (CKEditor 5)

Long-form fields use the shared **`RichTextEditor`** component:

- Path: `resources/js/components/Admin/RichTextEditor.vue`
- Config: `resources/js/config/ckeditorConfigs.js`
- License: GPL (open source)

| Variant | Used for | Toolbar |
|---------|----------|---------|
| `minimal` | Course summary, lesson summary | Bold, italic, link, lists |
| `full` | Article lesson body | Headings, lists, block quote, link |

**Do not** use plain `<textarea>` for article bodies or course summaries in admin forms — use `RichTextEditor` instead.

```vue
<RichTextEditor
    v-model="form.content"
    variant="full"
    label="Article body"
    hint="Main lesson content."
/>
```

## Building a curriculum

On **course edit** (`/courses/{id}/edit`):

1. Add **sections** (chapters).
2. **Add lesson to this section** — pick title + type (video or article).
3. Click **Create lesson & edit content** — redirects to the lesson editor.
4. On the lesson editor: upload video or write article in CKEditor, then optional quiz.

Lesson types:

- **Video** — upload MP4/WebM on the lesson editor.
- **Article** — write HTML content in the full CKEditor.

## Dependencies

```bash
npm install ckeditor5 @ckeditor/ckeditor5-vue
```

Rebuild assets after changes: `npm run build` or `npm run dev`.
