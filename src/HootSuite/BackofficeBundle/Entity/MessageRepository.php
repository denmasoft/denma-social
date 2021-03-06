<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * MessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageRepository extends EntityRepository
{
    public function findPendings($profile_id){
        //devolver solo los programados para una fecha a futuro....??
        $em   = $this->getEntityManager();
        $consulta = $em->createQuery("
            SELECT m,p FROM BackofficeBundle:Message m
            JOIN m.profiles_usuario p
            WHERE p.id = $profile_id
            AND m.programed IS NOT NULL
            ORDER BY m.created_at DESC");
        return $consulta->getResult();
    }
}
