<?php
namespace HootSuite\BackofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use HootSuite\BackofficeBundle\Entity\SocialNetwork;

class LoadSocialNetworksData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $social_0 = new SocialNetwork(); $social_0->setName('Twitter'); $social_0->setUniquename('TWITTER'); $social_0->setAvatar(''); $social_0->setPrivacity(''); $social_0->setSegmentation(''); $social_0->setGlyphicon('glyphicon1-twitter'); $social_0->setGlyphicon1('glyphicon1-twitter2'); $social_0->setKeyword('1'); $social_0->setSearch('1'); $manager->persist($social_0); $manager->flush();$social_1 = new SocialNetwork(); $social_1->setName('Facebook'); $social_1->setUniquename('FACEBOOK'); $social_1->setAvatar(''); $social_1->setPrivacity('1'); $social_1->setSegmentation('1'); $social_1->setGlyphicon('glyphicon1-facebook'); $social_1->setGlyphicon1('glyphicon1-facebook2'); $social_1->setKeyword(''); $social_1->setSearch('1'); $manager->persist($social_1); $manager->flush();$social_2 = new SocialNetwork(); $social_2->setName('Google'); $social_2->setUniquename('GOOGLE'); $social_2->setAvatar(''); $social_2->setPrivacity(''); $social_2->setSegmentation(''); $social_2->setGlyphicon('glyphicon1-google'); $social_2->setGlyphicon1('glyphicon1-googleplus22'); $social_2->setKeyword(''); $social_2->setSearch('1'); $manager->persist($social_2); $manager->flush();$social_3 = new SocialNetwork(); $social_3->setName('LinkedIn'); $social_3->setUniquename('LINKEDIN'); $social_3->setAvatar('0'); $social_3->setPrivacity(''); $social_3->setSegmentation(''); $social_3->setGlyphicon('glyphicon1-linkedin'); $social_3->setGlyphicon1('glyphicon1-linkedin2'); $social_3->setKeyword(''); $social_3->setSearch('1'); $manager->persist($social_3); $manager->flush();$social_4 = new SocialNetwork(); $social_4->setName('Wordpress'); $social_4->setUniquename('WORDPRESS'); $social_4->setAvatar(''); $social_4->setPrivacity(''); $social_4->setSegmentation(''); $social_4->setGlyphicon('glyphicon1-wordpress'); $social_4->setGlyphicon1('glyphicon1-wordpress2'); $social_4->setKeyword(''); $social_4->setSearch('1'); $manager->persist($social_4); $manager->flush();$social_5 = new SocialNetwork(); $social_5->setName('Instagram'); $social_5->setUniquename('INSTAGRAM'); $social_5->setAvatar(''); $social_5->setPrivacity(''); $social_5->setSegmentation(''); $social_5->setGlyphicon('glyphicon1-instagram'); $social_5->setGlyphicon1('glyphicon1-instagram2'); $social_5->setKeyword(''); $social_5->setSearch(''); $manager->persist($social_5); $manager->flush();$social_6 = new SocialNetwork(); $social_6->setName('Pinterest'); $social_6->setUniquename('PINTEREST'); $social_6->setAvatar(''); $social_6->setPrivacity(''); $social_6->setSegmentation(''); $social_6->setGlyphicon('glyphicon1-pinterest'); $social_6->setGlyphicon1('glyphicon1-pinterest2'); $social_6->setKeyword(''); $social_6->setSearch(''); $manager->persist($social_6); $manager->flush();
    }
    public function getOrder()
    {
        return 2;
    }
}