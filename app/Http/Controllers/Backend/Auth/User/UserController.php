<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Models\Auth\User;
use App\Http\Controllers\Controller;
use App\Events\Backend\Auth\User\UserDeleted;
use App\Repositories\Backend\Auth\RoleRepository;
use App\Repositories\Backend\Auth\UserRepository;
use App\Repositories\Backend\Auth\PermissionRepository;
use App\Http\Requests\Backend\Auth\User\StoreUserRequest;
use App\Http\Requests\Backend\Auth\User\ManageUserRequest;
use App\Http\Requests\Backend\Auth\User\UpdateUserRequest;

use Illuminate\Support\Facades\View;
/**
 * Class UserController.
 */
class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->middleware('permission:view backend', ['only' => ['edit','update']]);
        $this->middleware('permission:view-user-access', ['only' => ['index','create','store','show','destroy']]);
        
        $this->userRepository = $userRepository;
        View::share('menu_item', 'user');

    }

    /**
     * @param ManageUserRequest $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ManageUserRequest $request)
    {
        //echo "<pre>";print_r();die;
        return view('backend.auth.user.index')
            ->withrequestdata($request->all())
            ->withUsers($this->userRepository->getActivePaginated(15, 'id', 'desc',$request->all()));
    }

    /**
     * @param ManageUserRequest    $request
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     *
     * @return mixed
     */
    public function create(ManageUserRequest $request, RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        return view('backend.auth.user.create')
            ->withRoles($roleRepository->with('permissions')->get(['id', 'name']))
            ->withPermissions($permissionRepository->get(['id', 'name']));
    }

    /**
     * @param StoreUserRequest $request
     *
     * @throws \Throwable
     * @return mixed
     */
    public function store(StoreUserRequest $request)
    {
        $this->userRepository->create($request->only(
            'first_name',
            'last_name',
            'email',
            'password',
            'active',
            'confirmed',
            'confirmation_email',
            'roles',
            'permissions',
            'avatar_location'
        ));

        return redirect()->route('admin.auth.user.index')->withFlashSuccess(__('alerts.backend.users.created'));
    }

    /**
     * @param ManageUserRequest $request
     * @param User              $user
     *
     * @return mixed
     */
    public function show(ManageUserRequest $request, User $user)
    {
        return view('backend.auth.user.show')
            ->withUser($user);
    }

    /**
     * @param ManageUserRequest    $request
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     * @param User                 $user
     *
     * @return mixed
     */
    public function edit(ManageUserRequest $request, RoleRepository $roleRepository, PermissionRepository $permissionRepository, User $user)
    {
        return view('backend.auth.user.edit')
            ->withUser($user)
            ->withRoles($roleRepository->get())
            ->withUserRoles($user->roles->pluck('name')->all())
            ->withPermissions($permissionRepository->get(['id', 'name']))
            ->withUserPermissions($user->permissions->pluck('name')->all());
    }

    /**
     * @param UpdateUserRequest $request
     * @param User              $user
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     * @return mixed
     */
    public function update(UpdateUserRequest $request, User $user)
    {

        // echo "<pre>";print_r( $request->all() ); die;
        // if($user->isAdmin()){
        if ( $user->can('view-user-access') || $user->isAdmin()   ) {   
            $this->userRepository->update($user, $request->only(
                'first_name',
                'last_name',
                'email',
                'roles',
                'permissions',
                'avatar_type',
                'avatar_location'
            ),$request->has('avatar_location') ? $request->file('avatar_location') : false);
            return redirect()->route('admin.auth.user.index')->withFlashSuccess(__('alerts.backend.users.updated'));
        }else{ 
            $this->userRepository->update($user, $request->only(
                'first_name',
                'last_name',
                'email', 
                'roles',
                'avatar_type',
                'avatar_location'
            ),$request->has('avatar_location') ? $request->file('avatar_location') : false);              
            return redirect()->route('admin.auth.user.edit',auth()->id())->withFlashSuccess(__('alerts.backend.users.updated'));
        }

    }

    /**
     * @param ManageUserRequest $request
     * @param User              $user
     *
     * @throws \Exception
     * @return mixed
     */
    public function destroy(ManageUserRequest $request, User $user)
    {
        $this->userRepository->deleteById($user->id);

        event(new UserDeleted($user));

        return redirect()->route('admin.auth.user.deleted')->withFlashSuccess(__('alerts.backend.users.deleted'));
    }
}
