<?php

namespace BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCategoryData extends AbstractFixture  implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $category = new Category();
        $category2 = new Category();
        $category3 = new Category();
        $category->setTitle("musique");
        $category2->setTitle("politique");
        $category3->setTitle("technologie");

        $manager->persist($category);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->flush();

        $this->addReference('category-musique', $category);
        $this->addReference('category-politique', $category);
        $this->addReference('category-technologie', $category);
    }

    public function getOrder()
    {
        return 3;
    }
}
