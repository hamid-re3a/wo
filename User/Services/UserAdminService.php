<?php


namespace User\Services;


use App\Jobs\User\UserDataJob;
use Illuminate\Http\Request;
use User\Repository\RoleRepository;
use User\Repository\UserRepository;

class UserAdminService
{
    private $user_repository;
    private $role_repository;

    public function __construct(UserRepository $user_repository, RoleRepository $role_repository)
    {
        $this->user_repository = $user_repository;
        $this->role_repository = $role_repository;
    }

    /**
     * create admin user and produce on rabbitMQ
     * @param $request
     * @return mixed
     */
    public function createAdmin($request)
    {
        $admin = $this->user_repository->createAdmin($request);
        $role = $this->role_repository->getRole($request->role_id);
        $admin->assignRole($role->id);

        $userObject = new \User\Services\User();
        $userObject->setId($admin->id);
        $userObject->setEmail($admin->email);
        $userObject->setFirstName($admin->first_name);
        $userObject->setLastName($admin->last_name);
        $userObject->setUsername($admin->username);
        $userObject->setBlockType($admin->block_type);
        $userObject->setIsDeactivate($admin->is_deactivate);
        $userObject->setIsFreeze($admin->is_freeze);
        $userObject->setSponsorId($admin->sponsor_id);
        $role_name = implode(",",$admin->getRoleNames()->toArray());
        $userObject->setRole($role_name);
        $serializeUser = serialize($userObject);
        UserDataJob::dispatch($serializeUser)->onConnection('rabbit')->onQueue('subscriptions');
        UserDataJob::dispatch($serializeUser)->onConnection('rabbit')->onQueue('kyc');
        UserDataJob::dispatch($serializeUser)->onConnection('rabbit')->onQueue('mlm');

        return $admin;

    }

    /**
     * get userService Object by id
     * @param $user_update
     * @return mixed
     */
    public function getUserData($user_update){
        return $this->user_repository->getUserData($user_update->getId());
    }

}
