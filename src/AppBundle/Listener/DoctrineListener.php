<?php

namespace AppBundle\Listener;

use Doctrine\ORM\Event\PreFlushEventArgs;

class DoctrineListener
{
    public function preFlush(PreFlushEventArgs $event)
    {
        $em = $event->getEntityManager();

        foreach($em->getUnitOfWork()->getScheduledEntityDeletions() as $entity) {
            if(method_exists($entity, 'getDeletedAt')) {
                if($entity->getDeletedAt() instanceof \DateTime) {
                    continue;
                } else {
                    $entity->setDeletedAt(new \DateTime());
                    $em->merge($entity);
                    $em->persist($entity);
                }
            }
        }
    }
}
