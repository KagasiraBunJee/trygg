<?php

namespace Manager\Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CompanyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyRepository extends EntityRepository
{
    public function getCompanies($step, $searchText = "")
    {
        $em = $this->getEntityManager();
        $repository = $em->getRepository("ManagerBundle:Company");
        $query = $repository->createQueryBuilder('c')
            ->innerJoin('c.step','u')
            ->where("u = :step and c.name LIKE '%$searchText%' and c.rejected = 0 and c.trashed = 0")
            ->setParameter('step', $step->getId())
            ->getQuery();
        return $query;
    }

    public function getAllCompaniesQuery($searchText = "")
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(" SELECT p FROM ManagerBundle:Company p WHERE p.name LIKE '%$searchText%'");
        return $query;
    }

    public function getRejectedListQuery($searchText = "")
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(" SELECT p FROM ManagerBundle:Company p WHERE p.rejected = 1 and p.name LIKE '%$searchText%' and p.trashed = 0");
        return $query;
    }

    public function getReportedCompanies($searchText = "")
    {
        $em = $this->getEntityManager();
        $date = strtotime("now");
        $date = strtotime("-5 day", $date);
        $searchDate = date('Y-m-d', $date)." 23:59:59";
        $query = $em->createQuery(" SELECT p FROM ManagerBundle:Company p WHERE p.name LIKE '%$searchText%' and p.updated <= :dataTime");
        $query->setParameter("dataTime",$searchDate);

        return $query;
    }

}
