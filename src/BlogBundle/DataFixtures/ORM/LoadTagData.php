<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTagData extends AbstractFixture  implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $tag = new Tag();
        $tag->setTitle("metal");
        $tag2 = new Tag();
        $tag2->setTitle("classic");
        $tag3 = new Tag();
        $tag3->setTitle("rock");
        $tag4 = new Tag();
        $tag4->setTitle("parodie");
        $tag5 = new Tag();
        $tag5->setTitle("humour");

        $manager->persist($tag);
        $manager->persist($tag2);
        $manager->persist($tag3);
        $manager->persist($tag4);
        $manager->persist($tag5);
        $manager->flush();

        $this->addReference('tag-metal', $tag);
        $this->addReference('tag-classic', $tag2);
        $this->addReference('tag-rock', $tag3);
        $this->addReference('tag-parodie', $tag4);
        $this->addReference('tag-humour', $tag5);
    }

    public function getOrder()
    {
        return 2;
    }
}
