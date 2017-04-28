<?php


namespace BlogBundle\Controller;

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
     * Return a article by id.
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
     * Create a article.
     *
     * @Post("/articles")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Creates a new article",
     *  parameters = {
     *
     *      { "name"="articlename", "dataType"="string", "required"=true, "format"="", "description"="Articlename" },
     *      { "name"="email", "dataType"="string", "required"=true, "format"="", "description"="Email" },
     *      { "name"="password", "dataType"="string", "required"=true, "format"="", "description"="Plain Password" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when the email or articlename is already used"
     *  }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="articlename", nullable=false, strict=true, description="Articlename")
     * @RequestParam(name="email", nullable=false, strict=true, description="Email")
     * @RequestParam(name="password", nullable=false, strict=true, description="Plain Password")
     *
     * @return Response
     */
//    public function postArticleAction(ParamFetcher $paramFetcher)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $params = $paramFetcher->all();
//
//
//        if (preg_match('/\s/', $params["articlename"])) {
//            throw new HttpException(400, "The articlename contains space(s)");
//        }
//
//        if (preg_match('/\s/', $params["password"])) {
//            throw new HttpException(400, "The password contains space(s)");
//        }
//
//
//        //check doublon
//        $res = $em->getRepository("AppBundle:Article")->findOneBy(["email" => $params["email"]]);
//        if ($res) {
//            throw new HttpException(409, "This email already exists.");
//        }
//        $res = $em->getRepository("AppBundle:Article")->findOneBy(["articlename" => $params["articlename"]]);
//        if ($res) {
//            throw new HttpException(409, "This articlename already exists.");
//        }
//
//
//        $article = new Article();
//
//        //encodage password
//        $params["salt"] = bin2hex(random_bytes(255));
//        $encoder = $this->get("security.password_encoder");
//        $encoded = $encoder->encodePassword($article, $params["password"]);
//        $params["password"] = $encoded;
//
//        $params['role'] = 'ROLE_ARTICLE';
//
//        if (isset($params["id"])) {
//            unset($params["id"]);
//        }
//
//        $form = $this->createForm(ArticleType::class, $article);
//        //verification validité
//        $form->submit($params);
//
//        if ($form->isValid() == false) {
//            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
//        }
//
//        //push bdd
//        $em->persist($article);
//        $em->flush();
//
//        $article->eraseSensitive();
//
//        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
//    }

    /**
     * Replace old article by new article at ID.
     *
     * @Put("/articles/{id}")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Replace an existing article",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Article id" }
     *  },
     *  parameters = {
     *      { "name"="articlename", "dataType"="string", "required"=true, "format"="", "description"="Articlename" },
     *      { "name"="email", "dataType"="string", "required"=true, "format"="", "description"="Email" },
     *      { "name"="password", "dataType"="string", "required"=true, "format"="", "description"="Plain Password" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when the email or articlename is already used"
     *  }
     * )
     *
     * @RequestParam(name="articlename", nullable=false, strict=true, description="The articlename")
     * @RequestParam(name="email", nullable=false, strict=true, description="The email")
     * @RequestParam(name="password", nullable=false, strict=true, description="The password")
     *
     * @param int $id
     * @param ParamFetcher $paramfetcher
     * @return Response
     */
//    public function putArticlesAction($id, ParamFetcher $paramfetcher)
//    {
//        $params = $paramfetcher->all();
//        $em = $this->getDoctrine()->getManager();
//        $article = $em->getRepository('AppBundle:Article')->find($id);
//
//        if (!$article) {
//            throw new HttpException(404, "Article cannot be found.");
//        }
//        if ($id != $this->getArticle()->getId() && !$this->get('security.authorization_checker')->isGranted(
//                'ROLE_ADMIN'
//            )
//        ) {
//            throw new HttpException(403, "You do not have the proper right to update this article.");
//        }
//
//        $res = $em->getRepository("AppBundle:Article")->findOneBy(["email" => $params["email"]]);
//        if ($res && $res->getId() != $id) {
//            throw new HttpException(409, "This email already exists.");
//        }
//
//        $res = $em->getRepository("AppBundle:Article")->findOneBy(["articlename" => $params["articlename"]]);
//        if ($res && $res->getId() != $id) {
//            throw new HttpException(409, "This articlename already exists.");
//        }
//
//        //encodage password
//        $params["salt"] = bin2hex(random_bytes(255));
//        $encoder = $this->get("security.password_encoder");
//        $encoded = $encoder->encodePassword($article, $params["password"]);
//        $params["password"] = $encoded;
//        $params["role"] = "ROLE_ARTICLE";
//
//        if (isset($params["id"])) {
//            unset($params["id"]);
//        }
//
//        $form = $this->createForm(ArticleType::class, $article);
//        $form->submit($params);
//
//        if ($form->isValid() == false) {
//            throw new HttpException(400, $form->getErrors());
//        }
//
//        $em->persist($article);
//        $em->flush();
//
//        $article->eraseSensitive();
//
//        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
//    }

    /**
     * Update some data for a article.
     *
     * @Patch("/articles/{id}")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @ApiDoc(
     *  resource = "Article",
     *  description = "Update an existing article",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Article id" }
     *  },
     *  parameters = {
     *      { "name"="articlename", "dataType"="string", "required"=false, "format"="", "description"="Articlename" },
     *      { "name"="email", "dataType"="string", "required"=false, "format"="", "description"="Email" },
     *      { "name"="password", "dataType"="string", "required"=false, "format"="", "description"="Plain Password" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when the email or articlename is already used"
     *  }
     * )
     *
     * @RequestParam(name="articlename", nullable=true, strict=true, description="The articlename")
     * @RequestParam(name="email", nullable=true, strict=true, description="The email")
     * @RequestParam(name="password", nullable=true, strict=true, description="The password")
     *
     * @param int $id
     * @param ParamFetcher $paramfetcher
     * @return Response
     */
//    public function patchArticlesAction($id, ParamFetcher $paramfetcher)
//    {
//        $params = $paramfetcher->all();
//        $em = $this->getDoctrine()->getManager();
//        $article = $em->getRepository('AppBundle:Article')->find($id);
//
//        if (!$article) {
//            throw new HttpException(404, "Article cannot be found.");
//        }
//
//        if ($id != $this->getArticle()->getId() && !$this->get('security.authorization_checker')->isGranted(
//                'ROLE_ADMIN'
//            )
//        ) {
//            throw new HttpException(403, "You do not have the proper right to update this article.");
//        }
//
//        if (isset($params["email"])) {
//            $res = $em->getRepository("AppBundle:Article")->findOneBy(["email" => $params["email"]]);
//            if ($res && $res->getId() != $id) {
//                throw new HttpException(409, "This email already exists.");
//            }
//        }
//
//        if (isset($params["articlename"])) {
//            $res = $em->getRepository("AppBundle:Article")->findOneBy(["articlename" => $params["articlename"]]);
//            if ($res && $res->getId() != $id) {
//                throw new HttpException(409, "This articlename already exists.");
//            }
//        }
//
//        if (isset($params["password"])) {
//
//            //encodage password
//            $params["salt"] = bin2hex(random_bytes(255));
//            $encoder = $this->get("security.password_encoder");
//            $encoded = $encoder->encodePassword($article, $params["password"]);
//            $params["password"] = $encoded;
//
//        }
//
//        $articleAsArr = json_decode($this->get("custom_serializer")->serializeJson($article), true);
//        foreach ($articleAsArr as $key => $p) {
//            if (!isset($params[$key]) || $params[$key] == null || $params[$key] == '') {
//                $params[$key] = $p;
//            }
//        }
//
//        if (isset($params["id"])) {
//            unset($params["id"]);
//        }
//
//        $form = $this->createForm(ArticleType::class, $article);
//        $form->submit($params);
//
//        if ($form->isValid() == false) {
//            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
//        }
//
//        $em->persist($article);
//        $em->flush();
//
//        $article->eraseSensitive();
//
//        return $this->handleView(View::create()->setData($article)->setStatusCode(200));
//    }

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
