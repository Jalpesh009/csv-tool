<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Models\Auth\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Auth\User\ManageUserRequest;
use Illuminate\Support\Facades\View;
/**
 * Class UserSessionController.
 */
class UserSessionController extends Controller
{
    public function __construct()
    {

        View::share('menu_item', 'user');
    }
    /**
     * @param ManageUserRequest $request
     * @param User              $user
     *
     * @return mixed
     */
    public function clearSession(ManageUserRequest $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->withFlashDanger(__('exceptions.backend.access.users.cant_delete_own_session'));
        }

        $user->update(['to_be_logged_out' => true]);

        return redirect()->back()->withFlashSuccess(__('alerts.backend.users.session_cleared'));
    }
}
