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
            'email' => $user->getEmail(),
            'member_id' => $user->getMemberId(),
            'block_type' => empty($user->getBlockType()) ? null : $user->getBlockType(),
            'is_deactivate' => empty($user->getIsDeactivate()) ? 0 : $user->getIsDeactivate(),
            'is_freeze' => empty($user->getIsFreeze()) ? 0 : $user->getIsFreeze(),
            'sponsor_id' => empty($user->getSponsorId()) ? null : $user->getSponsorId(),
        ]);
        return $user_find;
    }

}
