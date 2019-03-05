<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SocialNetworkRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SocialNetworkRepository extends EntityRepository
{
    public function findWidthColumns(){
        $em   = $this->getEntityManager();
        $consulta = $em->createQuery("
            SELECT s,c FROM BackofficeBundle:SocialNetwork s
            LEFT JOIN s.columns c");
        return $consulta->getResult();
    }
}