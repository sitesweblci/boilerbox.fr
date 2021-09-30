<?php
//src/Lci/BoilerBoxBundle/Services/ServiceCGU
namespace Lci\BoilerBoxBundle\Services;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Lci\BoilerBoxBundle\Entity\CGU;
use Lci\BoilerBoxBundle\Entity\User;

class ServiceCGU
{
    private $em;

    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public function createCGU(CGU $cgu, User $user)
    {
        $cgu->setUser($user)
            ->setDtImportation(new DateTime('now'));

        $cguCourants = $this->em->getRepository(CGU::class)->findBy(['cguCourant' => true]);
        foreach ($cguCourants as $cguCourant) {
            $cguCourant->setCguCourant(false);
        }

        if ($cgu->getCguObligatoire()) {
            //si les nouvelles CGU doivent être acceptées alors on reset tous les users
            $users = $this->em->getRepository(User::class)->findAll();
            foreach ($users as $user) {
                $user->setCguAccepte(false);
            }
        }

        $this->em->persist($cgu);
        $this->em->flush();
    }
}
