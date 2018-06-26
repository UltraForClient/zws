<?php

namespace AppBundle\Repository\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeletedFilter extends SQLFilter
{

    public function addFilterConstraint(ClassMetadata $targetEntity, $alias)
    {
        if($targetEntity->hasField('deletedAt')) {
            return $alias . '.deleted_at IS NULL';
        }
        return '';
    }
}