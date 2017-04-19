<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\Services\FixtureAwareTestCase;
use AppBundle\DataFixtures\ORM\LoadUserData;
use Tests\AppBundle\Services\TestCase;


/**
 * Class UserControllerTest
 * @package Tests\AppBundle\Controller
 */
class UserControllerTest extends TestCase
{

    /**
     * API endpoint: user.
     */
    private $endpoint = "/users";


    /**
     * Admin data
     */
    private $admin = [
        "id" => "",
        "token" => "",
    ];

    /**
     * Simple user data
     */
    private $user = [
        "id" => "",
        "token" => "",
    ];


    /**
     * New user
     */
    private $user_data = [
        "email" => "user-test@ileotech.com",
        "username" => "user-test",
        "password" => "test",
    ];

    /**
     * Setup:
     * - load required fixtures
     * - fetch admin and user information: tokens and ids
     */
    public function setup()
    {
        parent::setup();

        $fixtures = [
            new LoadUserData(),
        ];

        // Load the fixtures we needs
        $fixtureAware = new FixtureAwareTestCase();
        $fixtureAware->setup();
        foreach ($fixtures as $fixture) {
            $fixtureAware->addFixture($fixture);
        }
        $fixtureAware->executeFixtures();

        // get the admin information
        $response = $this->httpRequest200(
            ["method" => "POST", "path" => "/api/auth"],
            ["username" => "ileo-admin", "password" => "admin"]
        );

        $this->admin["token"] = $response["token"];
        $this->admin["id"] = $response["idUser"];

        // get the user information
        $response = $this->httpRequest200(
            ["method" => "POST", "path" => "/api/auth"],
            ["username" => "ileo-user", "password" => "user"]
        );
        $this->user["token"] = $response["token"];
        $this->user["id"] = $response["idUser"];

    }


    /**
     * Post a user: POST /users
     * Tests:
     * - 200: successfull
     * - 409: email already exists
     */


    /**
     * @group postuser
     */
    public function testPostUser_409_emailAlreadyExists()
    {
        $data = $this->user_data;
        $data["email"] = "admin@ileotech.com";
        $this->httpRequest409(
            ["method" => "POST", "path" => '/api'.$this->endpoint, "token" => $this->admin["token"]],
            $data,
            ["code" => 409, "message" => "Conflict", "exception_message" => "This email already exists."]
        );
    }

    /**
     * @group postuser
     */
    public function testPostUser_409_usernameAlreadyExists()
    {
        $data = $this->user_data;
        $data["username"] = "ileo-admin";
        $this->httpRequest409(
            ["method" => "POST", "path" => '/api'.$this->endpoint, "token" => $this->admin["token"]],
            $data,
            ["code" => 409, "message" => "Conflict", "exception_message" => "This username already exists."]
        );
    }

    /**
     * @group postuser
     */
    public function testPostUser_200_successful()
    {
        $this->httpRequest200(
            ["method" => "POST", "path" => '/api'.$this->endpoint, "token" => $this->admin["token"]],
            $this->user_data,
            [
                "email" => "user-test@ileotech.com",
                "username" => "user-test",
                "role" => "ROLE_USER",
            ]
        );
    }


    /**
     * Get a user by id: GET /users/:user_id
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 404: token provided but invalid
     * - 404: user cannot be found
     */

    /**
     * @group getUserById
     */
    public function testGetUserById_401_tokenNotProvided()
    {
        $this->httpRequest401(["method" => "GET", "path" => '/api'.$this->endpoint."/".$this->user["id"]]);
    }

    /**
     * @group getUserById
     */
    public function testGetUserById_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "GET",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => substr($this->admin["token"], 0, -1),
            ],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group getUserById
     */
    public function testGetUserById_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "GET", "path" => '/api'.$this->endpoint."/0", "token" => $this->admin["token"]],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group getUserById
     */
    public function testGetUserById_200_successful()
    {
        $this->httpRequest200(
            array(
                "method" => "GET",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => $this->admin["token"],
            ),
            null,
            [
                "email" => "user@ileotech.com",
                "username" => "ileo-user",
                "role" => "ROLE_USER",
            ]
        );
    }

    /**
     * Get a user by email: GET /users/:email
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 404: token provided but invalid
     * - 404: user cannot be found
     */

    /**
     * @group getUserByEmail
     */
    public function testGetUserByEmail_401_tokenNotProvided()
    {
        $this->httpRequest401(["method" => "GET", "path" => '/api'.$this->endpoint."/email/admin@ileotech.com"]);
    }

    /**
     * @group getUserByEmail
     */
    public function testGetUserByEmail_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "GET",
                "path" => '/api'.$this->endpoint."/email/admin@ileotech.com",
                "token" => substr($this->admin["token"], 0, -1),
            ],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group getUserByEmail
     */
    public function testGetUserByEmail_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "GET", "path" => '/api'.$this->endpoint."/email/not@fou.nd", "token" => $this->admin["token"]],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group getUserByEmail
     */
    public function testGetUserByEmail_200_successful()
    {


        $this->httpRequest200(
            [
                "method" => "GET",
                "path" => '/api'.$this->endpoint."/email/admin@ileotech.com",
                "token" => $this->admin["token"],
            ],
            null,
            [
                "email" => "admin@ileotech.com",
                "username" => "ileo-admin",
                "role" => "ROLE_SUPER_ADMIN",
            ]
        );
    }


    //todo PUT
    //todo PATCH
    //todo DELETE
    //todo GET LIST

}
