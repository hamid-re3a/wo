<?php


namespace User\Services;


use Illuminate\Http\Request;
use User\Repository\RoleRepository;
use User\Repository\UserRepository;

class RoleService{
    private $role_repository;

    public function __construct(RoleRepository $role_repository)
    {
        $this->role_repository = $role_repository;
    }

    public function getAllRoles()
    {
        return $this->role_repository->getAllRoles();
    }

    public function createRole($request)
    {
        return $this->role_repository->createRole($request);
    }

}
