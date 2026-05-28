<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectionRequest;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(StoreSectionRequest $request, Course $course): RedirectResponse
    {
        $maxOrder = $course->sections()->max('sort_order');

        $course->sections()->create([
            'title' => $request->string('title')->toString(),
            'sort_order' => $maxOrder !== null ? ((int) $maxOrder) + 1 : 0,
        ]);

        return back()->with('success', 'Section added.');
    }

    public function update(Request $request, Course $course, Section $section): RedirectResponse
    {
        $this->authorize('update', $course);

        if ((int) $section->course_id !== (int) $course->id) {
            abort(404);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $section->update([
            'title' => $request->string('title')->toString(),
        ]);

        return back()->with('success', 'Section updated.');
    }

    public function destroy(Course $course, Section $section): RedirectResponse
    {
        $this->authorize('update', $course);

        if ((int) $section->course_id !== (int) $course->id) {
            abort(404);
        }

        $section->delete();

        return back()->with('success', 'Section removed.');
    }
}
