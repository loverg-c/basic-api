<?php

namespace BlogBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="BlogBundle\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotNull()
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;


    /**
     * @var \DateTime
     *
     * @Assert\NotNull()
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    //todo add bundles : blamable, timestamptable ....

    /**
     * @var User
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $author;

    /**
     * @var Article
     *
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="BlogBundle\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", nullable=false)
     */
    private $article;

    /**
     * @var Comment[]
     * One Comment has Many Comments.
     * @ORM\OneToMany(targetEntity="BlogBundle\Entity\Comment", mappedBy="parent")
     */
    private $childrens;

    /**
     * @var Comment
     * Many Comments have One Comment.
     * @ORM\ManyToOne(targetEntity="BlogBundle\Entity\Comment", inversedBy="childrens",  cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $parent;

    /**
     * @var User[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinTable(name="user_like_comment",
     *      joinColumns={@ORM\JoinColumn(name="comment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $likes;

    /**
     * Comment constructor.
     */
    public function __construct()
    {
        $this->likes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * @return Comment[]
     */
    public function getChildrens()
    {
        return $this->childrens;
    }

    /**
     * @param Comment[] $childrens
     */
    public function setChildrens($childrens)
    {
        $this->childrens = $childrens;
    }

    /**
     * @param Comment $comment
     */
    public function addChildren(Comment $comment)
    {
        $this->childrens->add($comment);
    }

    /**
     * @param Comment $comment
     */
    public function removeChildren(Comment $comment)
    {
        $this->childrens->removeElement($comment);
    }

    /**
     * @return Comment
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Comment $parent
     */
    public function setParent(Comment $parent)
    {
        $this->parent = $parent;
        $parent->addChildren($this);
    }

    /**
     * @return User[]
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param User[] $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
    }

    /**
     * @param User $user
     */
    public function addLike(User $user)
    {
        $this->likes->add($user);
    }

    /**
     * @param User $user
     */
    public function removeLike(User $user)
    {
        $this->likes->removeElement($user);
    }

    /**
     * Erase auhtor sensitive data
     */
    public function eraseAuthorSensitive()
    {
        $this->author->eraseSensitive();
    }
}

