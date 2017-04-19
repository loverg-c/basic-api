<?php

namespace AppBundle\Security;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder as JWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class JwtAuthenticator
 * @package AppBundle\Security
 */
class JwtAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var JWTEncoder
     */
    private $jwtEncoder;

    /**
     * JwtAuthenticator constructor.
     * @param EntityManager $em
     * @param JWTEncoder $jwtEncoder
     */
    public function __construct(EntityManager $em, JWTEncoder $jwtEncoder)
    {
        $this->em = $em;
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return void
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new HttpException(401, "You must provide a valid token.");
    }

    /**
     * @param Request $request
     * @return array|bool|null|string
     */
    public function getCredentials(Request $request)
    {

        if (!$request->headers->has("Authorization")) {
            return null;
        }
        $extractor = new AuthorizationHeaderTokenExtractor("Bearer", "Authorization");
        $token = $extractor->extract($request);
        if (!$token) {
            return null;
        }

        return $token;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return null|object
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        try {
            $data = $this->jwtEncoder->decode($credentials);
        } catch (JWTDecodeFailureException $e) {
            return null;
        }
        $user = $this->em->getRepository("AppBundle:User")->findOneBy(["username" => $data["username"]]);
        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new HttpException(404, "User cannot be found.");
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

}
