<?php


namespace User\Services;


use Spatie\Permission\Models\Role;
use User\Repository\UserRepository;

class UserService
{
    private $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    public function userUpdate(User $user)
    {
        $user_updated = $this->user_repository->editOrCreate($user);
        $user_updated->roles()->detach();
        $roles = explode(",", $user->getRole());
        foreach ($roles as $role) {
            $roleExist = Role::whereName($role)->first();
            if ($roleExist === null) {
                $role = Role::create(['name' => $role, 'guard_name' => 'api']);
                $user_updated->assignRole($role);
            } else {
                $user_updated->assignRole($roleExist);
            }
        }
    }

}
