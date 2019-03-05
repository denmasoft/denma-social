<?php
namespace HootSuite\BackofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HootSuite\BackofficeBundle\Entity\Rol;

class LoadRolesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $role_0 = new Rol(); $role_0->setRol('ROLE_USUARIO'); $role_0->setAlias('usuarios'); $role_0->setSlug('role-usuario'); $manager->persist($role_0); $manager->flush();$role_1 = new Rol(); $role_1->setRol('ROLE_ADMIN'); $role_1->setAlias('administradores'); $role_1->setSlug('role-admin'); $manager->persist($role_1); $manager->flush();
    }
    public function getOrder()
    {
        return 5;
    }
}