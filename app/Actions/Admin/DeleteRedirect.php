<?php

namespace App\Actions\Admin;

use App\Models\Redirect;
use Illuminate\Support\Facades\Gate;

class DeleteRedirect
{
    public function handle(Redirect $redirect): void
    {
        Gate::authorize('delete', $redirect);
        activity('redirects')->causedBy(auth()->user())->performedOn($redirect)->event('redirect_deleted')->log('redirect_deleted');
        $redirect->delete();
    }
}
