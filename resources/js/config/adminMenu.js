/**
 * Admin sidebar menu. Items may set roles: only users with one of these roles see the item.
 */
export const adminMenuItems = [
    {
        title: 'Dashboard',
        icon: 'ki-element-11',
        href: '/dashboard',
        roles: ['instructor', 'admin', 'super_admin'],
    },
    {
        title: 'Courses',
        icon: 'ki-book',
        href: '/courses',
        roles: ['instructor', 'admin', 'super_admin'],
    },
    {
        title: 'Pending review',
        icon: 'ki-time',
        href: '/courses/pending',
        roles: ['admin', 'super_admin'],
        badgeKey: 'pending_review_count',
    },
    {
        title: 'Categories',
        icon: 'ki-category',
        href: '/categories',
        roles: ['admin', 'super_admin'],
    },
    {
        title: 'Users',
        icon: 'ki-profile-circle',
        href: '/dashboard',
        roles: ['admin', 'super_admin'],
    },
    {
        title: 'Settings',
        icon: 'ki-setting-2',
        href: '/dashboard',
        roles: ['admin', 'super_admin'],
    },
];

/**
 * @param {string[]} userRoles
 * @returns {typeof adminMenuItems}
 */
export function filterAdminMenuForRoles(userRoles) {
    return adminMenuItems.filter((item) => {
        if (!item.roles || item.roles.length === 0) {
            return true;
        }

        return item.roles.some((role) => userRoles.includes(role));
    });
}
