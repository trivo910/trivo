<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateTheme
{
    public function handle(User $user, string $theme): void
    {
        Gate::authorize('updatePreferences', $user);
        $user->forceFill(['theme' => $theme])->save();
        activity('preferences')->causedBy($user)->event('theme_updated')->log('theme_updated');
    }
}
