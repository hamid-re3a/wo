<?php


namespace User\Repository;

use User\Models\User as UserModel;
use User\Services\User;

class UserRepository
{
    protected $entity_name = UserModel::class;

    public function editOrCreate(User $user)
    {
        $user_entity = new $this->entity_name;
        $user_find = $user_entity->whereId($user->getId())->firstOrNew();
        $user_find->first_name = $user->getFirstName() ? $user->getFirstName() : $user_find->first_name;
        $user_find->last_name = $user->getLastName() ? $user->getLastName() : $user_find->last_name;
        $user_find->username = $user->getUsername() ? $user->getUsername() : $user_find->username ;
        $user_find->email = $user->getEmail() ? $user->getEmail() : $user_find->email;
        if (!empty($user_find->getDirty())) {
            $user_find->save();
        }
        return $user_find;
    }

}
