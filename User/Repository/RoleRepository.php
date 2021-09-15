<?php
namespace User\Repository;

use Spatie\Permission\Models\Role;

class RoleRepository
{
    private $entity_name = Role::class;

    public function createRole($request)
    {
        $user_entity = new $this->entity_name;
        $user_entity->name = $request->name;
        $user_entity->save();
        return $user_entity;
    }

    public function getRole($id)
    {
        $role_entity = new $this->entity_name;
        return $role_entity->where('id',$id)->first();
    }


    public function getAllRoles()
    {
        $role_entity = new $this->entity_name;
        return $role_entity->get();
    }
}
