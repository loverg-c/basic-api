<?php


namespace BlogBundle\Controller;

use BlogBundle\Form\ArticleType;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use BlogBundle\Entity\Article as Article;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
//use BlogBundle\Form\ArticleType;
use FOS\RestBundle\Request\ParamFetcher;


class ArticleController extends FOSRestController
{

    /**
     * Return an article by id.
     *
     * @Get("/articles/{id}")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Return a article by id",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Article id" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      404 = "Returned when the article cannot be found"
     *  }
     * )
     * @param int $id
     * @return Response
     */
    public function getArticleByIdAction($id)
    {
        /** @var Article $article */
        $article = $this->getDoctrine()->getRepository("BlogBundle:Article")->find($id);
        if (!$article) {
            throw new HttpException(404, "Article cannot be found.");
        }
        $article->eraseAuthorSensitive();

        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
    }


    /**
     * Return list of article.
     *
     * @Get("/articles")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Return list of article",
     *  statusCodes = {
     *      200 = "Returned when successful",
     *  }
     * )
     * @return Response
     */
    public function getArticlesAction()
    {
        /** @var Article[] $articles */
        $articles = $this->getDoctrine()->getRepository("BlogBundle:Article")->findAll();
        foreach ($articles as $article) {
            $article->eraseAuthorSensitive();
        }

        return $this->handleView(View::create()->setData($articles)->setStatusCode(200));
    }

    /**
     * Create an article.
     *
     * @Post("/articles")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Creates a new article",
     *  parameters = {
     *      { "name"="title", "dataType"="string", "required"=true, "format"="", "description"="Title" },
     *      { "name"="content", "dataType"="string", "required"=true, "format"="", "description"="Content" },
     *      { "name"="category", "dataType"="integer", "required"=true, "format"="", "description"="Category" },
     *      { "name"="tags", "dataType"="string[]", "required"=false, "format"="", "description"="Tags" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      404 = "Returned when a category is not found"
     *  }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="title", nullable=false, strict=true, description="Title")
     * @RequestParam(name="content", nullable=false, strict=true, description="Content")
     * @RequestParam(name="category", nullable=false, strict=true, description="Category")
     * @RequestParam(name="tags", nullable=true, strict=true, description="Tags (array of tags, or string) if one not exist, it will be created")
     *
     * @return Response
     */
    public function postArticleAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $paramFetcher->all();

        if (isset($params["tags"]) && !is_array($params["tags"])) {
            throw new HttpException(400, "Tags should be array of tags object or at least array of string");
        }
        if (($category = $em->getRepository("BlogBundle:Category")->find($params["category"])) == null) {
            throw new HttpException(404, "This category does not exist.");
        }
        $params['author'] = $this->getUser()->getId();
        $params["tags"] = $em->getRepository('BlogBundle:Tag')->findAllOrCreate($params['tags']);

        $article = new Article();
        $article->setCreatedAt((new \DateTime()));
        $form = $this->createForm(ArticleType::class, $article);
        //verification validité
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
        }

        //push bdd
        $em->persist($article);
        $em->flush();

        $article->getAuthor()->eraseSensitive();

        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
    }

    /**
     * Replace old article by new article at ID.
     *
     * @Put("/articles/{id}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Replace an existing article",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Article id" }
     *  },
     *  parameters = {
     *      { "name"="title", "dataType"="string", "required"=true, "format"="", "description"="Title" },
     *      { "name"="content", "dataType"="string", "required"=true, "format"="", "description"="Content" },
     *      { "name"="category", "dataType"="integer", "required"=true, "format"="", "description"="Category" },
     *      { "name"="tags", "dataType"="string[]", "required"=false, "format"="", "description"="Tags" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      404 = "Returned when a category is not found"
     *  }
     * )
     *
     * @RequestParam(name="title", nullable=false, strict=true, description="Title")
     * @RequestParam(name="content", nullable=false, strict=true, description="Content")
     * @RequestParam(name="category", nullable=false, strict=true, description="Category")
     * @RequestParam(name="tags", nullable=true, strict=true, description="Tags (array of tags, or string) if one not exist, it will be created")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function putArticlesAction($id, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $paramFetcher->all();
        $article = $em->getRepository('BlogBundle:Article')->find($id);

        if (!$article) {
            throw new HttpException(404, "Article cannot be found.");
        }
        if (isset($params["tags"]) && !is_array($params["tags"])) {
            throw new HttpException(400, "Tags should be array of tags object or at least array of string");
        }
        if (($category = $em->getRepository("BlogBundle:Category")->find($params["category"])) == null) {
            throw new HttpException(404, "This category does not exist.");
        }
        $params['author'] = $this->getUser()->getId();
        $params["tags"] = $em->getRepository('BlogBundle:Tag')->findAllOrCreate($params['tags']);

        $article->setCreatedAt((new \DateTime()));

        $form = $this->createForm(ArticleType::class, $article);
        //verification validité
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
        }

        //push bdd
        $em->persist($article);
        $em->flush();

        $article->getAuthor()->eraseSensitive();

        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
    }

    /**
     * Update some data for a article.
     *
     * @Patch("/articles/{id}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Update an existing article",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Article id" }
     *  },
     *  parameters = {
     *      { "name"="title", "dataType"="string", "required"=false, "format"="", "description"="Title" },
     *      { "name"="content", "dataType"="string", "required"=false, "format"="", "description"="Content" },
     *      { "name"="category", "dataType"="integer", "required"=false, "format"="", "description"="Category" },
     *      { "name"="tags", "dataType"="string[]", "required"=false, "format"="", "description"="Tags" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      404 = "Returned when a category is not found"
     *  }
     * )
     *
     * @RequestParam(name="title", nullable=true, strict=true, description="Title")
     * @RequestParam(name="content", nullable=true, strict=true, description="Content")
     * @RequestParam(name="category", nullable=true, strict=true, description="Category")
     * @RequestParam(name="tags", nullable=true, strict=true, description="Tags (array of tags, or string) if one not exist, it will be created")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function patchArticlesAction($id, ParamFetcher $paramFetcher)
    {
        $params = $paramFetcher->all();
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('BlogBundle:Article')->find($id);

        if (!$article) {
            throw new HttpException(404, "Article cannot be found.");
        }

        if (isset($params["tags"]) && !is_array($params["tags"])) {
            throw new HttpException(400, "Tags should be array of tags object or at least array of string");
        }
        if (isset($params["category"]) && ($category = $em->getRepository("BlogBundle:Category")->find(
                $params["category"]
            )) == null
        ) {
            throw new HttpException(404, "This category does not exist.");
        }
        $params['author'] = $this->getUser()->getId();

        if (isset($params["tags"])) {
            $params["tags"] = $em->getRepository('BlogBundle:Tag')->findAllOrCreate($params['tags']);
        }


        $articleAsArr = json_decode($this->get("custom_serializer")->serializeJson($article), true);
        foreach ($articleAsArr as $key => $p) {
            if (!isset($params[$key]) || $params[$key] == null || $params[$key] == '') {
                if (is_array($p)) {
                    if (isset($p['id'])) {
                        $params[$key] = $p['id'];
                    } else {
                        if ($key == "tags") {
                            $params["tags"] = $em->getRepository('BlogBundle:Tag')->findAllOrCreate($p);
                        }
                    }
                } else {
                        $params[$key] = $p;
                }
            }
        }

        if (isset($params["id"])) {
            unset($params["id"]);
        }
        if (isset($params["createdAt"])) {
            unset($params["createdAt"]);
        }


        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
        }

        $em->persist($article);
        $em->flush();

        $article->getAuthor()->eraseSensitive();

        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
    }

    /**
     * Delete a article.
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Delete a article",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Article id" }
     *  },
     *  statusCodes = {
     *      204 = "Returned when successful",
     *      403 = "Returned when the request is forbidden",
     *      404 = "Returned when the article cannot be found"
     *  }
     * )
     *
     * @param int $id
     * @return Response;
     */
    public function deleteArticleAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //check existance
        /** @var Article $article */
        $article = $em->getRepository("BlogBundle:Article")->find($id);
        if (!$article) {
            throw new HttpException(404, "Article cannot be found.");
        }

        //remove
        $em->remove($article);
        $em->flush();

        return $this->handleView(
            View::create()->setData('Delete has been done, you will never see it again')->setStatusCode(200)
        );
    }

}
