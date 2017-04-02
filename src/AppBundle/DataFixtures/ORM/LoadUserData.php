<?php

namespace AppBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture  implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $user = new User();
        $user->setEmail("user@ileotech.com");
        $user->setUsername('ileo-user');
        $user->setSalt(md5(uniqid()));
        $user->setRole('ROLE_USER');
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, "d1zqvdzz");
        $user->setPassword($encoded);

        $admin = new User();
        $admin->setEmail("admin@ileotech.com");
        $admin->setUsername('ileo-admin');
        $admin->setSalt(md5(uniqid()));
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($admin, "d1zqvdzz");
        $admin->setPassword($encoded);
        $admin->setRole('ROLE_SUPER_ADMIN');

        $manager->persist($admin);
        $manager->persist($user);
        $manager->flush();

        $this->addReference('ileo-admin', $admin);
        $this->addReference('ileo-user', $user);
    }

    public function getOrder()
    {
        return 1;
    }
}
