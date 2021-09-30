<?php

namespace Ipc\ProgBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SiteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SiteRepository extends EntityRepository {

    public function myFindCourant() 
	{
		$query = $this
			->createQueryBuilder('s')
			->where('s.siteCourant = :courant')
			->setParameter('courant', true)
			->getQuery();
        try {
            $result = $query ->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $result = null;
        }
		return $result;
    }
}
