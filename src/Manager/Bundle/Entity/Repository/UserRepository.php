<?php

namespace Manager\Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    public function getManagers($searchText = "")
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('
            SELECT p FROM ManagerBundle:User p WHERE p.name LIKE :name
        ');
        $query->setParameter('name','%'.$searchText.'%');

        return $query;
        //return $query->getResult();
    }
}
