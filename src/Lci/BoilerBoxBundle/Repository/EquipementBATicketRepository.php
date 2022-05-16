<?php

namespace Lci\BoilerBoxBundle\Repository;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

/**
 * @method EquipementBATicket|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipementBATicket|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipementBATicket[]    findAll()
 * @method EquipementBATicket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipementBATicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipementBATicket::class);
    }

    // /**
    //  * @return EquipementBATicket[] Returns an array of EquipementBATicket objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EquipementBATicket
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
