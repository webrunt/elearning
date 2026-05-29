<?php

namespace App\Http\Middleware;

use App\Enums\CourseReviewStatus;
use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $parent = parent::share($request);

        $user = $request->user();
        $authUser = null;

        if ($user !== null) {
            $authUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->values()->all(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            ];
        }

        $adminHost = (string) config('domains.admin');
        $appUrl = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        $adminScheme = $scheme !== null && $scheme !== '' ? $scheme : 'https';
        $adminLoginUrl = $adminHost !== '' ? $adminScheme.'://'.$adminHost.'/login' : '/';

        $admin = [];
        if ($user !== null && $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            $admin['pending_review_count'] = Course::where('status', CourseStatus::PendingReview)->count();
            $admin['pending_course_reviews_count'] = CourseReview::where('status', CourseReviewStatus::Pending)->count();
        }

        return array_merge($parent, [
            'auth' => [
                'user' => $authUser,
            ],
            'admin' => $admin,
            'urls' => [
                'admin_login' => $adminLoginUrl,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }
}
