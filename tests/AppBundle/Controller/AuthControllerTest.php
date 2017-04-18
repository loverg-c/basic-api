<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\Services\FixtureAwareTestCase;
use AppBundle\DataFixtures\ORM\LoadUserData;
use Tests\AppBundle\Services\TestCase;


/**
 * Class AuthControllerTest
 * @package Tests\AppBundle\Controller
 */
class AuthControllerTest extends TestCase
{

    /**
     * API endpoint: auth.
     */
    private $endpoint = "/auth";

    /**
     * Admin data
     */
    private $work_data = [
        "username" => "ileo-admin",
        "password" => "d1zqvdzz",
    ];

    /**
     * Wrong password data
     */
    private $wrong_data = [
        "username" => "ileo-admin",
        "password" => "bidule",
    ];

    /**
     * Not found user data
     */
    private $notfound_data = [
        "username" => "ileo-machin",
        "password" => "bidule",
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

    }

    /**
     * Test Authentication successfull
     */
    public function testPostAuthenticate200()
    {
       $this->httpRequest200(
            ["method" => "POST", "path" => '/api'.$this->endpoint],
            $this->work_data
        );
    }

    /**
     * Test Authentication wrong password
     */
    public function testPostAuthenticate400()
    {
        $this->httpRequest400(
            ["method" => "POST", "path" => '/api'.$this->endpoint],
            $this->wrong_data,
            ["code" => 400, "message" => "Bad Request", "exception_message" => "Invalid password."]
        );
    }

    /**
     * Test Authentication user not found
     */
    public function testPostAuthenticate404()
    {
        $this->httpRequest404(
            ["method" => "POST", "path" => '/api'.$this->endpoint],
            $this->notfound_data,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

}
