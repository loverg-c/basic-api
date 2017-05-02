<?php

namespace BlogBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use BlogBundle\Entity\Category;
use BlogBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BlogBundle\Entity\Article;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadArticleData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        /** @var User $ileoadmin */
        $ileoadmin = $this->getReference("ileo-admin");
        /** @var User $ileouser */
        $ileouser = $this->getReference("ileo-user");
        /** @var Tag $metal */
        $metal = $this->getReference("tag-metal");
        /** @var Tag $rock */
        $rock = $this->getReference("tag-rock");
        /** @var Tag $classic */
        $classic = $this->getReference("tag-classic");
        /** @var Tag $parodie */
        $parodie = $this->getReference("tag-parodie");
        /** @var Tag $humour */
        $humour = $this->getReference("tag-humour");
        /** @var Category $musique */
        $musique = $this->getReference("category-musique");


        $article = new Article();
        $article->setTitle("Sortie du nouvel album d\'Ultra Vomit");
        $article->setAuthor($ileoadmin);
        $article->setCreatedAt(new \DateTime('2017-04-28'));
        $article->addTag($humour);
        $article->addTag($metal);
        $article->addTag($parodie);
        $article->addTag($rock);
        $article->addLike($ileouser);
        $article->setCategory($musique);
        $article->setContent(
            htmlspecialchars(
                "<div class=\"entry\">
				<p>
				    <strong>ULTRA VOMIT</strong> dévoile les premiers détails de son nouvel album, intitulé Panzer Surprise, prévu le 28 avril chez Verycords.
				    <span id=\"more-251927\"></span>
				</p>
                <p>Teaser réalisé par Nicolas Leroy :</p>
                <p></p>
                <center>
                    <iframe src=\"https://www.youtube.com/embed/geH49uzJooU\" allowfullscreen=\"\" height=\"315\" frameborder=\"0\" width=\"560\"></iframe>
                </center>
                <p></p>
                <p>Artwork :</p>
                <p>
                    <img src=\"http://www.radiometal.com/wp-content/uploads/2017/03/17498539_10155131581851972_2622969336406760238_n.jpg\" alt=\"\"
                        class=\"aligncenter size-full wp-image-251929\"
                        sizes=\"(max-width: 960px) 100vw, 960px\" height=\"500\">
                </p>
            "
            )
        );
        $manager->persist($metal);
        $manager->persist($humour);
        $manager->persist($parodie);
        $manager->persist($rock);
        $manager->persist($article);
        $manager->flush();

        $this->addReference('article-ultra-vomit', $article);
    }

    public function getOrder()
    {
        return 4;
    }
}
