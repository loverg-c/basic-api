<?php


namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use AppBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Form\UserType;
use FOS\RestBundle\Request\ParamFetcher;


class UserController extends FOSRestController
{


    /**
     * Return a user by id.
     *
     * @Get("/users/{id}")
     * @ApiDoc(
     *  resource = "User",
     *  description = "Return a user by id",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "User id" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      404 = "Returned when the user cannot be found"
     *  }
     * )
     * @param int $id
     * @return Response
     */
    public function getUserByIdAction($id)
    {
        $view = View::create();
        $user = $this->getDoctrine()->getRepository("AppBundle:User")->find($id);
        if (!$user) {
            throw new HttpException(404, "User cannot be found.");
        }

        $userAsArr = json_decode($this->get('custom_serializer')->serializeJson($user), true);
        unset($userAsArr["password"]);
        unset($userAsArr["salt"]);

        return $this->handleView($view->setData($userAsArr)->setStatusCode(200));
    }


    /**
     * Return list of user.
     *
     * @Get("/users")
     * @Security("is_granted('ROLE_ADMIN')")
     * @ApiDoc(
     *  resource = "User",
     *  description = "Return list of user",
     *  statusCodes = {
     *      200 = "Returned when successful",
     *  }
     * )
     * @return Response
     */
    public function getUsersAction()
    {
        $view = View::create();
        $users = $this->getDoctrine()->getRepository("AppBundle:User")->findAll();

        $userAsArr = json_decode($this->get('custom_serializer')->serializeJson($users), true);
        foreach ($userAsArr as $key => $user) {
            unset($userAsArr[$key]["password"]);
            unset($userAsArr[$key]["salt"]);
        }

        return $this->handleView($view->setData($userAsArr)->setStatusCode(200));
    }

    /**
     * Return a user by email.
     *
     * @Get("/users/email/{userEmail}")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @ApiDoc(
     *  resource = "User",
     *  description = "Return a user by email",
     *  requirements = {
     *      { "name" = "email", "dataType" = "string", "requirement" = "", "description" = "User's email" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      404 = "Returned when the user cannot be found"
     *  }
     * )
     *
     * @param string $email
     * @return Response
     */
    public function getUserByEmailAction($email)
    {
        $view = View::create();
        $user = $this->getDoctrine()->getRepository("AppBundle:User")->findOneBy(["email" => $email]);
        if (!$user) {
            throw new HttpException(404, "This email does not exist.");
        }

        $userAsArr = json_decode($this->get('custom_serializer')->serializeJson($user), true);
        unset($userAsArr["password"]);

        return $this->handleView($view->setData($userAsArr)->setStatusCode(200));
    }

    /**
     * Create a user.
     *
     * @Post("/users")
     * @ApiDoc(
     *  resource = "User",
     *  description = "Creates a new user",
     *  parameters = {
     *
     *      { "name"="username", "dataType"="string", "required"=true, "format"="", "description"="Username" },
     *      { "name"="email", "dataType"="string", "required"=true, "format"="", "description"="Email" },
     *      { "name"="password", "dataType"="string", "required"=true, "format"="", "description"="Plain Password" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when the email is already used"
     *  }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="username", nullable=false, strict=true, description="The username")
     * @RequestParam(name="email", nullable=false, strict=true, description="The email")
     * @RequestParam(name="password", nullable=false, strict=true, description="The password")
     *
     * @return Response
     */
    public function postUserAction(ParamFetcher $paramFetcher)
    {
        $view = View::create();
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $params = $paramFetcher->all();

        //check doublon
        $res = $em->getRepository("AppBundle:User")->findOneBy(["email" => $params["email"]]);
        if ($res) {
            throw new HttpException(409, "This email already exists.");
        }
        $res = $em->getRepository("AppBundle:User")->findOneBy(["username" => $params["username"]]);
        if ($res) {
            throw new HttpException(409, "This username already exists.");
        }

        $form = $this->createForm(UserType::class, $user);

        //encodage password
        $params["salt"] = bin2hex(random_bytes(255));
        $encoder = $this->get("security.password_encoder");
        $encoded = $encoder->encodePassword($user, $params["password"]);
        $params["password"] = $encoded;

        //verification validité
        $form->submit($params);
        if ($form->isValid() == false) {
            throw new HttpException(400, 'Invalid data send');
        }

        //push bdd
        $em->persist($user);
        $em->flush();

        //unset des données sensible
        $userAsArr = json_decode($this->get('custom_serializer')->serializeJson($user), true);
        unset($userAsArr["password"]);
        unset($userAsArr["salt"]);

        return $this->handleView($view->setData($userAsArr)->setStatusCode(200));
    }

    /**
     * Update a user.
     * @Put("/users/{id}")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @ApiDoc(
     *  resource = "User",
     *  description = "Update an existing user",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "User id" }
     *  },
     *  parameters = {
     *      { "name"="username", "dataType"="string", "required"=false, "format"="", "description"="Username" },
     *      { "name"="email", "dataType"="string", "required"=false, "format"="", "description"="Email" },
     *      { "name"="password", "dataType"="string", "required"=false, "format"="", "description"="Plain Password" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when a/some parameter(s) is/are invalid",
     *      403 = "Returned when the request is forbidden",
     *      409 = "Returned when the email is already used"
     *  }
     * )
     *
     * @RequestParam(name="username", nullable=true, strict=true, description="The username")
     * @RequestParam(name="email", nullable=true, strict=true, description="The email")
     * @RequestParam(name="password", nullable=true, strict=true, description="The password")
     *
     * @param int $id
     * @param ParamFetcher $paramfetcher
     * @return Response
     */
    public function putUsersAction($id, ParamFetcher $paramfetcher)
    {
        $params = $paramfetcher->all();

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);
        if (!$user) {
            throw new HttpException(404, "User cannot be found.");
        }
        if ($id != $this->getUser()->getId() && !$this->getUser()->isGranted('ROLE_ADMIN')) {
            throw new HttpException(403, "You do not have the proper right to update this user.");
        }

        $view = View::create();
        if (isset($params["email"])) {
            $res = $em->getRepository("AppBundle:User")->findOneBy(["email" => $params["email"]]);
            if ($res && $res->getId() != $id) {
                throw new HttpException(409, "This email already exists.");
            }
        }
        if (isset($params["username"])) {
            $res = $em->getRepository("AppBundle:User")->findOneBy(["username" => $params["username"]]);
            if ($res && $res->getId() != $id) {
                throw new HttpException(409, "This username already exists.");
            }
        }

        if (isset($params["password"])) {
            $encoder = $this->get("security.password_encoder");
            $encoded = $encoder->encodePassword($user, $params["password"]);
            $params["password"] = $encoded;
        }

        //todo Patch instead of put
        $userAsArr = json_decode($this->get("custom_serializer")->serializeJson($user), true);
        foreach ($userAsArr as $key => $p) {
            if (!isset($params[$key]) || $params[$key] == null || $params[$key] == '') {
                $params[$key] = $p;
            }
        }

        if (isset($params["id"])) {
            unset($params["id"]);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->submit($params);

        if ($form->isValid() == false) {
            return $this->handleView($view->setData($form->getErrors())->setStatusCode(400));
        }

        $em->persist($user);
        $em->flush();

        $userAsArr = json_decode($this->get('custom_serializer')->serializeJson($user), true);
        unset($userAsArr["password"]);
        unset($userAsArr["salt"]);

        return $this->handleView($view->setData($userAsArr)->setStatusCode(200));
    }

    /**
     * Delete a user.
     * @Security("is_granted('ROLE_ADMIN')")
     * @ApiDoc(
     *  resource = "User",
     *  description = "Delete a user",
     *  requirements = {
     *      { "name" = "id", "dataType" = "int", "requirement" = "\d+", "description" = "User id" }
     *  },
     *  statusCodes = {
     *      204 = "Returned when successful",
     *      403 = "Returned when the request is forbidden",
     *      404 = "Returned when the user cannot be found"
     *  }
     * )
     *
     * @param int $id
     * @return Response;
     */
    public function deleteUserAction($id)
    {
        $view = View::create();
        $em = $this->getDoctrine()->getManager();

        //check existance
        $user = $em->getRepository("AppBundle:User")->find($id);
        if (!$user) {
            throw new HttpException(404, "User cannot be found.");
        }

        //remove
        $em->remove($user);
        $em->flush();

        return $this->handleView($view->setData(null)->setStatusCode(204));
    }
}
