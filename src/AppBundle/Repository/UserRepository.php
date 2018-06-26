<?php

namespace AppBundle\Repository;

class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllUsername()
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.username')
            ->getQuery()
            ->getResult();

        $allUsername = [];

        foreach($qb as $user) {
            $allUsername[] = $user['username'];
        }
        return $allUsername;

    }
}
