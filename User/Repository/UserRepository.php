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
        $user_find = $user_entity->query()->firstOrCreate(['id' => $user->getId()]);
        $user_find->update([
            'id' => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'email' => $user->getFirstName(),
            'block_type' => $user->getBlockType(),
            'is_deactivate' => $user->getIsDeactivate(),
            'is_freeze' => $user->getIsFreeze(),
            'sponsor_id' => $user->getSponsorId(),
        ]);
        if (!empty($user_find->getDirty())) {
            $user_find->save();
        }
        return $user_find;
    }

}
