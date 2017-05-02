<?php

namespace BlogBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCommentData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        /** @var User $ileouser */
        $ileouser = $this->getReference("ileo-user");
        /** @var User $ileoadmin */
        $ileoadmin = $this->getReference("ileo-admin");
        /** @var Article $article */
        $article = $this->getReference('article-ultra-vomit');

        $comment = new Comment();
        $comment->setCreatedAt(new \DateTime());
        $comment->setContent("Nous vivons tous dans un chien géant !");
        $comment->setAuthor($ileouser);
        $comment->addLike($ileoadmin);
        $comment->setArticle($article);
        $commentchild = new Comment();
        $commentchild->setCreatedAt(new \DateTime());
        $commentchild->setContent("Tous le monde le sait, mais personne ne dit rien du tout !");
        $commentchild->setAuthor($ileoadmin);
        $commentchild->addLike($ileouser);
        $commentchild->setArticle($article);
        $commentchild->setParent($comment);


        $manager->persist($comment);
        $manager->persist($commentchild);

        $manager->flush();

        $this->addReference('comment-1', $comment);
        $this->addReference('comment-2', $commentchild);

    }

    public function getOrder()
    {
        return 5;
    }
}
