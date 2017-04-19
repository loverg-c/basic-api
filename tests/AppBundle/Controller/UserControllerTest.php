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

}
