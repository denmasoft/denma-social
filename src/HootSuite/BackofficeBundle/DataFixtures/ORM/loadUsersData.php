<?php
namespace HootSuite\BackofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HootSuite\BackofficeBundle\Entity\Usuario;

class LoadUsersData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user_0 = new Usuario(); $user_0->setPlan($user_0->getPlan()); $user_0->setName('jolo'); $user_0->setEmail('jolo@gmail.com'); $user_0->setPassword('2DGFsu0QwN9sTcdhxqbQuosN18Jb52SIPodMdV7QrbTrItaOpYC5K/rAdAH5cZiYIc6GW36GqVY+U3ok5T7NPQ=='); $user_0->setSalt('9bb04e2004b924cba57ca99765705193'); $user_0->setActive('1');$manager->persist($user_0); $manager->flush();$user_1 = new Usuario(); $user_1->setPlan($user_1->getPlan()); $user_1->setName('jonco'); $user_1->setEmail('jonco@gmail.com'); $user_1->setPassword('wcNBQWn9mswqb21AQdbSy/LzJNLadM9GGz0Uny9w3BGMDjqlVfpR96QfaKrZBz7owVAeHQ3wWHQ7u98Bv+g+Xw=='); $user_1->setSalt('8ae0b2897971c1c78209eaad68a69afe'); $user_1->setActive('1');$manager->persist($user_1); $manager->flush();$user_2 = new Usuario(); $user_2->setPlan($user_2->getPlan()); $user_2->setName('maria'); $user_2->setEmail('maria@gmail.com'); $user_2->setPassword('I2JL5v8rb8Z0MqTngxKkW0Tl0gfdfugQvw0bSLeynzYo/bHBjjhEWZpjNc0UMm1pv7Zr5fPhz4L6EwyAWpYR0g=='); $user_2->setSalt('92647ea4cb886fa9486b527b7e8134e4'); $user_2->setActive('1');$manager->persist($user_2); $manager->flush();
    }
    public function getOrder()
    {
        return 4;
    }
}