<?php

namespace App\Providers;

use App\Models\StaffUser;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings needed.
    }

    public function boot(): void
    {
        // Gate for opcodesio/log-viewer — checks the app's own session-based auth
        // and the staff user's route permission for 'log-viewer.index'.
        LogViewer::auth(function ($request) {
            $staffUserId = $request->session()->get('staff_user_id');

            if (!$staffUserId) {
                return false;
            }

            $staff = StaffUser::find($staffUserId);

            return $staff && $staff->hasAccess('log-viewer.index');
        });
    }
}
