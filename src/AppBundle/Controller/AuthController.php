<?php


namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 * @package AppBundle\Controller
 */
class AuthController extends FOSRestController
{
    /**
     * Authenticate a user.
     *
     * @Post("/auth")
     * @ApiDoc(
     *  resource = "Authentication",
     *  description = "Authenticate a user",
     *  parameters = {
     *      { "name" = "username", "dataType" = "string","required"=true, "format" = "", "description"="username" },
     *      { "name" = "password","dataType" = "string","required"=true, "format" = "", "description"="password" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when the password is invalid",
     *      404 = "Returned when user cannot be found"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAuthenticateAction(Request $request)
    {
        $email = $request->request->get("username");
        $password = $request->request->get("password");
        $user = $this->getDoctrine()->getRepository("AppBundle:User")->findOneBy(["username" => $email]);

        if (!$user) throw new HttpException(404, "User cannot be found.");
        if (!$this->get("security.password_encoder")->isPasswordValid($user, $password)) {
            throw new HttpException(400, "Invalid password.");
        }
        // Use LexikJWTAuthenticationBundle to create JWT token that hold only information about user name
        $token = $this->get("lexik_jwt_authentication.encoder")->encode(["username" => $user->getUsername()]);

        return new JsonResponse(['token' => $token, 'idUser' => $user->getId()]);
    }

    /**
     * Send a mail providing a token to recover a forgotten password.
     *
     * @Post("/auth/recover-password")
     * @ApiDoc(
     *  resource = "Authentication",
     *  description = "First step to recover a password",
     *  parameters = {
     *      { "name" = "username", "dataType" = "string", "required"=true, "format" = "", "description"="username" }
     *  },
     *  statusCodes = {
     *      204 = "Returned when successful",
     *      404 = "Returned when user cannot be found"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postRecoverPasswordAction(Request $request)
    {
        $view = View::create();
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository("AppBundle:User")->findOneBy(["username" => $request->request->get("username")]);
        if (!$user) throw new HttpException(404, "This username does not exist.");

        $message = \Swift_Message::newInstance()
            ->setSubject("Recovernig password from Efficiency")
            ->setFrom("econox@gmail.com")
            ->setTo($user->getEmail())
            ->setBody(
                "<h3>Hi " . $user->getFirstName() . " " . $user->getLastName() . "</h3>
                To recover your password, enter this token to the recoving password form :<br><br>" .
                $user->getPassword() . "<br><br>
                Thanks!",
                "text/html"
            );
        $this->get("mailer")->send($message);

        return $this->handleView($view->setData(null)->setStatusCode(204));
    }

    /**
     * Update the new password if the token is valid.
     *
     * @Post("/auth/update-password")
     * @ApiDoc(
     *  resource = "Authentication",
     *  description = "Update the user's password",
     *  parameters = {
     *      { "name" = "username", "dataType" = "string", "required"=true, "format" = "", "description"="Login" },
     *      { "name" = "password", "dataType" = "string", "required"=true, "format" = "", "description"="Password" },
     *      { "name" = "token", "dataType" = "string", "required" = "true", "format" = "", "description" = "Token" }
     *  },
     *  statusCodes = {
     *      200 = "Returned when successful",
     *      404 = "Returned when user cannot be found"
     *  }
     * )
     *
     * @@param Request $request
     *
     * @return Response
     */
    public function postRecoverPasswordTokenAction(Request $request)
    {
        $view = View::create();
        $em = $this->getDoctrine()->getManager();
        $token = $request->request->get("token");
        $email = $request->request->get("username");
        $password = $request->request->get("password");

        $user = $em->getRepository("AppBundle:User")->findOneBy(["username" => $email]);
        if (!$user) throw new HttpException(404, "This username does not exist.");
        if ($user->getPassword() !== $token) throw new HttpException(400, "Invalid token.");

        $encoder = $this->get("security.password_encoder");
        $new_password = $encoder->encodePassword($user, $password);
        $user->setPassword($new_password);
        $em->persist($user);
        $em->flush();

        return $this->handleView($view->setData(null)->setStatusCode(204));
    }
}
