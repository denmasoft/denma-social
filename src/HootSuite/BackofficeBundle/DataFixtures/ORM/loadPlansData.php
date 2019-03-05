<?php
namespace HootSuite\BackofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HootSuite\BackofficeBundle\Entity\Plan;

class LoadPlansData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $plan_0 = new Plan(); $plan_0->setName('Básico'); $plan_0->setPrice('0'); $manager->persist($plan_0); $manager->flush();$plan_1 = new Plan(); $plan_1->setName('Profesional'); $plan_1->setPrice('8.99'); $manager->persist($plan_1); $manager->flush();$plan_2 = new Plan(); $plan_2->setName('Enterprise'); $plan_2->setPrice('15.35'); $manager->persist($plan_2); $manager->flush();
    }
    public function getOrder()
    {
        return 3;
    }
}