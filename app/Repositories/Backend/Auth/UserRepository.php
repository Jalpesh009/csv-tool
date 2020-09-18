<?php

namespace App\Repositories\Backend\Auth;

use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use App\Events\Backend\Auth\User\UserCreated;
use App\Events\Backend\Auth\User\UserUpdated;
use App\Events\Backend\Auth\User\UserRestored;
use App\Events\Backend\Auth\User\UserConfirmed;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\Backend\Auth\User\UserDeactivated;
use App\Events\Backend\Auth\User\UserReactivated;
use App\Events\Backend\Auth\User\UserUnconfirmed;
use App\Events\Backend\Auth\User\UserPasswordChanged;
use App\Notifications\Backend\Auth\UserAccountActive;
use App\Events\Backend\Auth\User\UserPermanentlyDeleted;
use App\Notifications\Frontend\Auth\UserNeedsConfirmation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Backend\Auth\User\StoreUserRequest;
/**
 * Class UserRepository.
 */
class UserRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    /**
     * @return mixed
     */
    public function getUnconfirmedCount() : int
    {
        return $this->model
            ->where('confirmed', false)
            ->count();
    }

    /**
     * @param int    $paged
     * @param string $orderBy
     * @param string $sort
     *
     * @return mixed
     */
    public function getActivePaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc',$allRequest=NULL) : LengthAwarePaginator
    {
        //echo "<pre>";print_r($allRequest);die;
        $allUserData = $this->model->with('roles', 'permissions', 'providers');
        if($allRequest){
            if(isset($allRequest['first_name'])){
                $allUserData = $allUserData->orWhere('first_name', 'like', '%' .$allRequest['first_name']. '%');
            }
            if(isset($allRequest['last_name'])){
                $allUserData = $allUserData->orWhere('last_name', 'like', '%' .$allRequest['last_name']. '%');
            }
            if(isset($allRequest['email'])){
                $allUserData = $allUserData->orWhere('email', 'like', '%' .$allRequest['email']. '%');
            }
            // if($allRequest['role']){
            //     $roleName = $allRequest['role'];
            //     $allUserData = $allUserData->whereHas('roles', function($query) use($roleName) {
            //         $query->orWhere('name', 'like', '%' .$roleName. '%');
            //     });
            // }
        }
        $allUserData = $allUserData->active();
        $allUserData = $allUserData->orderBy($orderBy, $sort);
        //  ->paginate($paged);
        $allUserData = $allUserData->paginate($paged);
        //echo "<pre>";print_r($allUserData);die;
        return $allUserData;
    }

    /**
     * @param int    $paged
     * @param string $orderBy
     * @param string $sort
     *
     * @return LengthAwarePaginator
     */
    public function getInactivePaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc') : LengthAwarePaginator
    {
        return $this->model
            ->with('roles', 'permissions', 'providers')
            ->active(false)
            ->orderBy($orderBy, $sort)
            ->paginate($paged);
    }

    /**
     * @param int    $paged
     * @param string $orderBy
     * @param string $sort
     *
     * @return LengthAwarePaginator
     */
    public function getDeletedPaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc') : LengthAwarePaginator
    {
        return $this->model
            ->with('roles', 'permissions', 'providers')
            ->onlyTrashed()
            ->orderBy($orderBy, $sort)
            ->paginate($paged);
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     * @throws \Throwable
     * @return User
     */
    public function create(array $data) : User
    {
        return DB::transaction(function () use ($data) {
            $user = parent::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'active' => isset($data['active']) && $data['active'] === '1',
                'confirmation_code' => md5(uniqid(mt_rand(), true)),
                'confirmed' => isset($data['confirmed']) && $data['confirmed'] === '1',
                'avatar_location' =>  $data['avatar_location'],
            ]);

            // See if adding any additional permissions
            if (! isset($data['permissions']) || ! count($data['permissions'])) {
                $data['permissions'] = [];
            }

            if ($user) {
                // User must have at least one role
                if (! count($data['roles'])) {
                    throw new GeneralException(__('exceptions.backend.access.users.role_needed_create'));
                }

                // Add selected roles/permissions
                $user->syncRoles($data['roles']);
                $user->syncPermissions($data['permissions']);

                //Send confirmation email if requested and account approval is off
                if ($user->confirmed === false && isset($data['confirmation_email']) && ! config('access.users.requires_approval')) {
                    $user->notify(new UserNeedsConfirmation($user->confirmation_code));
                }

                event(new UserCreated($user));

                return $user;
            }

            throw new GeneralException(__('exceptions.backend.access.users.create_error'));
        });
    }

    /**
     * @param User  $user
     * @param array $data
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \Throwable
     * @return User
     */
    public function update(User $user, array $data ,$image = false) : User
    {
       // echo "<pre>";print_r($user->avatar_location);
       // die;
        $this->checkUserByEmail($user, $data['email']);
        $isAdmin = true;
        if(!isset($data['roles']) && empty($data['roles'])){
            $isAdmin = false;
            $user = $this->getById($user->id);
        }else{
            $user1 = $this->getById($user->id);
            if ($image) {
                $user1->avatar_location = $image->store('/avatars', 'public_c');
            }else{
                $user1->avatar_location = $user->avatar_location;
            }
            $user1->avatar_type= 'storage';
            $user1->save();
        }

        // See if adding any additional permissions
        if (! isset($data['permissions']) || ! count($data['permissions'])) {
            $data['permissions'] = [];
        }

        return DB::transaction(function () use ($user, $data,$image,$isAdmin) {
            if ($user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
            ])) {
                //echo $image->store('/avatars', 'public');
               // echo "<pre>";print_r($data);
                 // Upload profile image if necessary
                if ($image) {
                    //$user->avatar_location = $image->store('/avatars', 'public');
                } else {
                    // No image being passed
                    if (!empty($data['avatar_type']) === 'storage') {
                        // If there is no existing image
                        if (auth()->user()->avatar_location === '') {
                            throw new GeneralException('You must supply a profile image.');
                        }
                    } else {
                        // If there is a current image, and they are not using it anymore, get rid of it
                        if (auth()->user()->avatar_location !== '') {
                            Storage::disk('public')->delete(auth()->user()->avatar_location);
                        }

                        $user->avatar_location = $user->avatar_location;
                    }
                }
                 //echo "<pre>";print_r($data);die;
                // echo "<pre>";print_r($user);die;
                // Add selected roles/permissions
                if(isset($data['roles'])){
                    $user->syncRoles($data['roles']);
                }
                if(isset($data['permissions'])){
                    $user->syncPermissions($data['permissions']);
                }
                if($isAdmin == false){

                    if ($image) {
                        $user->avatar_location = $image->store('/avatars', 'public_c');
                    }else{
                        $user->avatar_location = $user->avatar_location;
                    }
                    $user->avatar_type= 'storage';
                    $user->save();
                }else{

                    event(new UserUpdated($user));
                }


                return $user;
            }

            throw new GeneralException(__('exceptions.backend.access.users.update_error'));
        });
    }

    /**
     * @param User $user
     * @param      $input
     *
     * @throws GeneralException
     * @return User
     */
    public function updatePassword(User $user, $input) : User
    {
        if ($user->update(['password' => $input['password']])) {
            event(new UserPasswordChanged($user));

            return $user;
        }

        throw new GeneralException(__('exceptions.backend.access.users.update_password_error'));
    }

    /**
     * @param User $user
     * @param      $status
     *
     * @throws GeneralException
     * @return User
     */
    public function mark(User $user, $status) : User
    {
        if ($status === 0 && auth()->id() === $user->id) {
            throw new GeneralException(__('exceptions.backend.access.users.cant_deactivate_self'));
        }

        $user->active = $status;

        switch ($status) {
            case 0:
                event(new UserDeactivated($user));
            break;
            case 1:
                event(new UserReactivated($user));
            break;
        }

        if ($user->save()) {
            return $user;
        }

        throw new GeneralException(__('exceptions.backend.access.users.mark_error'));
    }

    /**
     * @param User $user
     *
     * @throws GeneralException
     * @return User
     */
    public function confirm(User $user) : User
    {
        if ($user->confirmed) {
            throw new GeneralException(__('exceptions.backend.access.users.already_confirmed'));
        }

        $user->confirmed = true;
        $confirmed = $user->save();

        if ($confirmed) {
            event(new UserConfirmed($user));

            // Let user know their account was approved
            if (config('access.users.requires_approval')) {
                $user->notify(new UserAccountActive);
            }

            return $user;
        }

        throw new GeneralException(__('exceptions.backend.access.users.cant_confirm'));
    }

    /**
     * @param User $user
     *
     * @throws GeneralException
     * @return User
     */
    public function unconfirm(User $user) : User
    {
        if (! $user->confirmed) {
            throw new GeneralException(__('exceptions.backend.access.users.not_confirmed'));
        }

        if ($user->id === 1) {
            // Cant un-confirm admin
            throw new GeneralException(__('exceptions.backend.access.users.cant_unconfirm_admin'));
        }

        if ($user->id === auth()->id()) {
            // Cant un-confirm self
            throw new GeneralException(__('exceptions.backend.access.users.cant_unconfirm_self'));
        }

        $user->confirmed = false;
        $unconfirmed = $user->save();

        if ($unconfirmed) {
            event(new UserUnconfirmed($user));

            return $user;
        }

        throw new GeneralException(__('exceptions.backend.access.users.cant_unconfirm'));
    }

    /**
     * @param User $user
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \Throwable
     * @return User
     */
    public function forceDelete(User $user) : User
    {
        if ($user->deleted_at === null) {
            throw new GeneralException(__('exceptions.backend.access.users.delete_first'));
        }

        return DB::transaction(function () use ($user) {
            // Delete associated relationships
            $user->passwordHistories()->delete();
            $user->providers()->delete();

            if ($user->forceDelete()) {
                event(new UserPermanentlyDeleted($user));

                return $user;
            }

            throw new GeneralException(__('exceptions.backend.access.users.delete_error'));
        });
    }

    /**
     * @param User $user
     *
     * @throws GeneralException
     * @return User
     */
    public function restore(User $user) : User
    {
        if ($user->deleted_at === null) {
            throw new GeneralException(__('exceptions.backend.access.users.cant_restore'));
        }

        if ($user->restore()) {
            event(new UserRestored($user));

            return $user;
        }

        throw new GeneralException(__('exceptions.backend.access.users.restore_error'));
    }

    /**
     * @param User $user
     * @param      $email
     *
     * @throws GeneralException
     */
    protected function checkUserByEmail(User $user, $email)
    {
        // Figure out if email is not the same and check to see if email exists
        if ($user->email !== $email && $this->model->where('email', '=', $email)->first()) {
            throw new GeneralException(trans('exceptions.backend.access.users.email_error'));
        }
    }
}
