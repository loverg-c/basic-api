<?php


namespace BlogBundle\Controller;

use BlogBundle\Form\CategoryType;
use BlogBundle\Repository\CategoryRepository;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use BlogBundle\Entity\Category as Category;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use FOS\RestBundle\Request\ParamFetcher;


class CategoryController extends FOSRestController
{

    /**
     * Return an category by id.
     *
     * @Get("/categories/{id}")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Return a category by id",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Category id" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      404 = "Returned when the category cannot be found"
     *  }
     * )
     * @param int $id
     * @return Response
     */
    public function getCategoryByIdAction($id)
    {
        /** @var Category $category */
        $category = $this->getDoctrine()->getRepository("BlogBundle:Category")->find($id);
        if (!$category) {
            throw new HttpException(404, "Category cannot be found.");
        }

        return $this->handleView(View::create()->setData($category)->setStatusCode(200));
    }


    /**
     * Return list of category.
     *
     * @Get("/categories")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Return list of category",
     *  statusCodes = {
     *      200 = "Returned when successful",
     *  }
     * )
     * @return Response
     */
    public function getCategoriesAction()
    {
        /** @var Category[] $categories */
        $categories = $this->getDoctrine()->getRepository("BlogBundle:Category")->findAll();

        return $this->handleView(View::create()->setData($categories)->setStatusCode(200));
    }


    /**
     * Return a category by title.
     *
     * @Get("/categories/title/{title}")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Return a category by title",
     *  requirements = {
     *      { "name" = "title", "dataType" = "string", "requirement" = "", "description" = "Category's title" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      404 = "Returned when the category cannot be found"
     *  }
     * )
     *
     * @param string $title
     * @return Response
     */
    public function getCategoryByTitleAction($title)
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->getDoctrine()->getRepository("BlogBundle:Category");


        $category = $categoryRepository->fetchOneByTitleLow($title);


        if (!$category) {
            throw new HttpException(404, "Category cannot be found.");
        }



        return $this->handleView(View::create()->setData($category)->setStatusCode(200));
    }
    
    /**
     * Create an category.
     *
     * @Post("/categories")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Creates a new category",
     *  parameters = {
     *      { "name"="title", "dataType"="string", "required"=true, "format"="", "description"="Title" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when title already used"
     *  }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="title", nullable=false, strict=true, description="Title")
     *
     * @return Response
     */
    public function postCategoryAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $paramFetcher->all();


        if (($em->getRepository("AppBundle:Category")->findOneBy(["title" => $params["title"]])) != null) {
            throw new HttpException(409, "This title already exists.");
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        //verification validité
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
        }

        //push bdd
        $em->persist($category);
        $em->flush();

        return $this->handleView(View::create()->setData($category)->setStatusCode(200));
    }

    /**
     * Replace old category by new category at ID.
     *
     * @Put("/categories/{id}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Replace an existing category",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Category id" }
     *  },
     *  parameters = {
     *      { "name"="title", "dataType"="string", "required"=true, "format"="", "description"="Title" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when title already used"
     *  }
     * )
     *
     * @RequestParam(name="title", nullable=false, strict=true, description="Title")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function putCategoriesAction($id, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $paramFetcher->all();
        /** @var Category $category */
        $category = $em->getRepository('BlogBundle:Category')->find($id);

        if (!$category) {
            throw new HttpException(404, "Category cannot be found.");
        }


        $res = $em->getRepository("AppBundle:Category")->findOneBy(["title" => $params["title"]]);
        if ($res && $res->getId() != $id) {
            throw new HttpException(409, "This title already exists.");
        }


        $form = $this->createForm(CategoryType::class, $category);
        //verification validité
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
        }

        //push bdd
        $em->persist($category);
        $em->flush();

        return $this->handleView(View::create()->setData($category)->setStatusCode(200));
    }

    /**
     * Update some data for a category.
     *
     * @Patch("/categories/{id}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Update an existing category",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Category id" }
     *  },
     *  parameters = {
     *      { "name"="title", "dataType"="string", "required"=false, "format"="", "description"="Title" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when title already used"
     *  }
     * )
     *
     * @RequestParam(name="title", nullable=true, strict=true, description="Title")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function patchCategoriesAction($id, ParamFetcher $paramFetcher)
    {
        $params = $paramFetcher->all();
        $em = $this->getDoctrine()->getManager();
        /** @var Category $category */
        $category = $em->getRepository('BlogBundle:Category')->find($id);

        if (!$category) {
            throw new HttpException(404, "Category cannot be found.");
        }

        if (isset($params["title"])) {
            $res = $em->getRepository("AppBundle:Category")->findOneBy(["title" => $params["title"]]);
            if ($res && $res->getId() != $id) {
                throw new HttpException(409, "This title already exists.");
            }
        }

        $categoryAsArr = json_decode($this->get("custom_serializer")->serializeJson($category), true);
        foreach ($categoryAsArr as $key => $p) {
            if (!isset($params[$key]) || $params[$key] == null || $params[$key] == '') {
                $params[$key] = $p;
            }
        }

        if (isset($params["id"])) {
            unset($params["id"]);
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView(View::create()->setData($form->getErrors())->setStatusCode(400));
        }

        $em->persist($category);
        $em->flush();


        return $this->handleView(View::create()->setData($category)->setStatusCode(200));
    }

    /**
     * Delete a category.
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ApiDoc(
     *  resource = "Category",
     *  description = "Delete a category",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "Category id" }
     *  },
     *  statusCodes = {
     *      204 = "Returned when successful",
     *      403 = "Returned when the request is forbidden",
     *      404 = "Returned when the category cannot be found"
     *  }
     * )
     *
     * @param int $id
     * @return Response;
     */
    public function deleteCategoryAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //check existance
        /** @var Category $category */
        $category = $em->getRepository("BlogBundle:Category")->find($id);
        if (!$category) {
            throw new HttpException(404, "Category cannot be found.");
        }

        //remove
        $em->remove($category);
        $em->flush();

        return $this->handleView(
            View::create()->setData('Delete has been done, you will never see it again')->setStatusCode(200)
        );
    }

}
