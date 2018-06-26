<?php

namespace AppBundle\Repository;

class TaskRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByUserId($id)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult()
            ;
    }
}
