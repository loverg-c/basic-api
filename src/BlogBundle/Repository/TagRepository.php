<?php

namespace BlogBundle\Repository;

use BlogBundle\Entity\Tag;

/**
 * TagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param array $list liste of title or tags object
     * @return integer[]
     */
    public function findAllOrCreate($list)
    {
        $finalTags = array();
        foreach ($list as $tag) {
            $tname = '';
            if (is_array($tag)) {
                if (isset($tag['title'])) {
                    $tname = $tag['title'];
                }
            } else {
                $tname = $tag;
            }
            array_push($finalTags, $this->findOneOrCreate(strtolower($tname))->getId());
        }

        return $finalTags;
    }

    /**
     * @param string $tname
     * @return Tag
     */
    public function findOneOrCreate($tname)
    {
        if (($result = $this->findOneBy(array('title' => $tname))) == null) {
            $result = new Tag();
            $result->setTitle($tname);
            $this->getEntityManager()->persist($result);
            $this->getEntityManager()->flush($result);
        }

        return $result;
    }
}
