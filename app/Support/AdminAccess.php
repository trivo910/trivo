<?php

namespace App\Support;

class AdminAccess
{
    public const ROLES = ['Super Admin', 'Admin', 'Editor', 'SEO Manager'];

    public const THEME_ROUTES = ['admin.dashboard', 'admin.styleguide', 'admin.activity', 'admin.mail.index', 'admin.redirects.index', 'admin.not-found', 'admin.media.index', 'admin.website.edit'];

    /** @return list<string> */
    public static function permissions(): array
    {
        $permissions = ['dashboard.view', 'leads.view', 'leads.manage', 'media.manage', 'redirects.manage',
            'settings.site', 'settings.mail', 'settings.seo', 'users.manage', 'activity.view'];
        foreach (['pages', 'posts', 'services', 'portfolio', 'testimonials', 'faqs', 'clients', 'menus'] as $module) {
            foreach (['view', 'create', 'update', 'delete', 'publish', 'seo'] as $action) {
                $permissions[] = $module.'.'.$action;
            }
        }

        return $permissions;
    }
}
