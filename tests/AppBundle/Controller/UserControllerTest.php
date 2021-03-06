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
     * @group postuser
     */
    public function testPostUser_400_spaceInUsername()
    {
        $data = $this->user_data;
        $data["username"] = "il eo-ad min";
        $this->httpRequest400(
            ["method" => "POST", "path" => '/api'.$this->endpoint, "token" => $this->admin["token"]],
            $data,
            ["code" => 400, "message" => "Bad Request", "exception_message" => "The username contains space(s)"]
        );
    }


    /**
     * @group postuser
     */
    public function testPostUser_400_spaceInPassword()
    {
        $data = $this->user_data;
        $data["password"] = "il eo-ad min";
        $this->httpRequest400(
            ["method" => "POST", "path" => '/api'.$this->endpoint, "token" => $this->admin["token"]],
            $data,
            ["code" => 400, "message" => "Bad Request", "exception_message" => "The password contains space(s)"]
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




    /**
     * Get list of users: GET /users
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 403: forbidden
     * - 404: token provided but invalid
     */

    /**
     * @group getUserList
     */
    public function testGetUserList_401_tokenNotProvided()
    {
        $this->httpRequest401(["method" => "GET", "path" => '/api'.$this->endpoint]);
    }

    /**
     * @group getUserList
     */
    public function testGetUserList_403_forbidden()
    {
        $this->httpRequest403(
            [
                "method" => "GET",
                "path" => '/api'.$this->endpoint,
                "token" => $this->user["token"],
            ],
            null
        );
    }

    /**
     * @group getUserList
     */
    public function testGetUserList_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "GET",
                "path" => '/api'.$this->endpoint,
                "token" => substr($this->admin["token"], 0, -1),
            ],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }


    /**
     * @group getUserList
     */
    public function testGetUserList_200_successful()
    {

        $this->httpRequest200(
            [
                "method" => "GET",
                "path" => '/api'.$this->endpoint,
                "token" => $this->admin["token"],
            ],
            null,
            [
                [
                    "email" => "admin@ileotech.com",
                    "username" => "ileo-admin",
                    "role" => "ROLE_SUPER_ADMIN",
                ],
                [
                    "email" => "user@ileotech.com",
                    "username" => "ileo-user",
                    "role" => "ROLE_USER",
                ],
            ]
        );
    }

    /**
     * Delete a user : DELETE /users/:user_id
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 403: forbidden request when the token belongs to a user with no proper right to perform the request
     * - 404: token provided but invalid
     * - 404: user cannot be found
     */

    /**
     * @group deleteUser
     */
    public function testDeleteUser_401_tokenNotProvided()
    {
        $this->httpRequest401(array("method" => "DELETE", "path" => '/api'.$this->endpoint."/".$this->user["id"]));
    }

    /**
     * @group deleteUser
     */
    public function testDeleteUser_403_forbidden()
    {
        $this->httpRequest403(
            [
                "method" => "DELETE",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => $this->user["token"],
            ],
            null,
            ["code" => 403, "message" => "Forbidden"]
        );
    }

    /**
     * @group deleteUser
     */
    public function testDeleteUser_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "DELETE",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => substr($this->admin["token"], 0, -1),
            ],
            null,
            [
                "code" => 404,
                "message" => "Not Found",
                "exception_message" => "User cannot be found.",

            ]
        );
    }

    /**
     * @group deleteUser
     */
    public function testDeleteUser_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "DELETE", "path" => '/api'.$this->endpoint."/0", "token" => $this->admin["token"]],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group deleteUser
     */
    public function testDeleteUser_200_successful()
    {
        $this->httpRequest200(
            array(
                "method" => "DELETE",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => $this->admin["token"],
            ),
            null,
            [
                "exception_message" => "Delete has been done, you will never see it again",
            ]
        );
    }


    /**
     * Put a user : PUT /users/:user_id
     * Tests:
     * - 200: successful
     * - 400: some null value
     * - 401: token not provided
     * - 403: forbidden request when the token belongs to a user with no proper right to perform the request
     * - 404: token provided but invalid
     * - 404: user cannot be found
     * - 409: email already taken
     * - 409: username already taken
     */

    /**
     * @group putUser
     */
    public function testPutUser_400_nullInForm()
    {
        $data = $this->user_data;
        $data['username'] = '';
        $this->httpRequest400(
            [
                "method" => "PUT",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => $this->admin["token"],
            ],
            $data,
            ["code" => 400, "message" => "Bad Request"]
        );
    }

    /**
     * @group putUser
     */
    public function testPutUser_401_tokenNotProvided()
    {
        $this->httpRequest401(array("method" => "PUT", "path" => '/api'.$this->endpoint."/".$this->user["id"]));
    }

    /**
     * @group putUser
     */
    public function testPutUser_403_forbidden()
    {
        $this->httpRequest403(
            [
                "method" => "PUT",
                "path" => '/api'.$this->endpoint."/".$this->admin["id"],
                "token" => $this->user["token"],
            ],
            $this->user_data,
            [
                "code" => 403,
                "message" => "Forbidden",
                'exception_message' => 'You do not have the proper right to update this user.',
            ]
        );
    }

    /**
     * @group putUser
     */
    public function testPutUser_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "PUT",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => substr($this->admin["token"], 0, -1),
            ],
            $this->user_data,
            [
                "code" => 404,
                "message" => "Not Found",
                "exception_message" => "User cannot be found.",

            ]
        );
    }

    /**
     * @group putUser
     */
    public function testPutUser_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "PUT", "path" => '/api'.$this->endpoint."/0", "token" => $this->admin["token"]],
            $this->user_data,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group putUser
     */
    public function testPutUser_409_emailAlreadyExists()
    {
        $data = $this->user_data;
        $data["email"] = "user@ileotech.com";
        $this->httpRequest409(
            [
                "method" => "PUT",
                "path" => '/api'.$this->endpoint."/".$this->admin['id'],
                "token" => $this->admin["token"],
            ],
            $data,
            ["code" => 409, "message" => "Conflict", "exception_message" => "This email already exists."]
        );
    }

    /**
     * @group putUser
     */
    public function testPutUser_409_usernameAlreadyExists()
    {
        $data = $this->user_data;
        $data["username"] = "ileo-user";
        $this->httpRequest409(
            [
                "method" => "PUT",
                "path" => '/api'.$this->endpoint."/".$this->admin['id'],
                "token" => $this->admin["token"],
            ],
            $data,
            ["code" => 409, "message" => "Conflict", "exception_message" => "This username already exists."]
        );
    }

    /**
     * @group putUser
     */
    public function testPutUser_200_successful()
    {

        $data = $this->user_data;

        $data["username"] = "user-test[PUT]";

        $this->httpRequest200(
            [
                "method" => "PUT",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => $this->admin["token"],
            ],
            $data,
            [
                "email" => "user-test@ileotech.com",
                "username" => "user-test[PUT]",
                "role" => "ROLE_USER",
            ]
        );
    }


    /**
     * Patch a user : PATCH /users/:user_id
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 403: forbidden request when the token belongs to a user with no proper right to perform the request
     * - 404: token provided but invalid
     * - 404: user cannot be found
     * - 409: email already taken
     * - 409: username already taken
     */

    /**
     * @group patchUser
     */
    public function testPatchUser_401_tokenNotProvided()
    {
        $this->httpRequest401(array("method" => "PATCH", "path" => '/api'.$this->endpoint."/".$this->user["id"]));
    }

    /**
     * @group patchUser
     */
    public function testPatchUser_403_forbidden()
    {
        $this->httpRequest403(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->admin["id"],
                "token" => $this->user["token"],
            ],
            $this->user_data,
            [
                "code" => 403,
                "message" => "Forbidden",
                'exception_message' => 'You do not have the proper right to update this user.',
            ]
        );
    }

    /**
     * @group patchUser
     */
    public function testPatchUser_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => substr($this->admin["token"], 0, -1),
            ],
            $this->user_data,
            [
                "code" => 404,
                "message" => "Not Found",
                "exception_message" => "User cannot be found.",

            ]
        );
    }

    /**
     * @group patchUser
     */
    public function testPatchUser_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "PATCH", "path" => '/api'.$this->endpoint."/0", "token" => $this->admin["token"]],
            $this->user_data,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group patchUser
     */
    public function testPatchUser_409_emailAlreadyExists()
    {
        $data = $this->user_data;
        $data["email"] = "user@ileotech.com";
        $this->httpRequest409(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->admin['id'],
                "token" => $this->admin["token"],
            ],
            ["email" => "user@ileotech.com"],
            ["code" => 409, "message" => "Conflict", "exception_message" => "This email already exists."]
        );
    }

    /**
     * @group patchUser
     */
    public function testPatchUser_409_usernameAlreadyExists()
    {
        $this->httpRequest409(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->admin['id'],
                "token" => $this->admin["token"],
            ],
            ["username" => "ileo-user"],
            ["code" => 409, "message" => "Conflict", "exception_message" => "This username already exists."]
        );
    }

    /**
     * @group patchUser
     */
    public function testPatchUser_200_successful()
    {

        $this->httpRequest200(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->user["id"],
                "token" => $this->admin["token"],
            ],
            ["username" => "user-test[PATCH]"],
            [
                "email" => "user@ileotech.com",
                "username" => "user-test[PATCH]",
                "role" => "ROLE_USER",
            ]
        );
    }

    /**
     * PatchRole a user : PatchRole /users/:user_id
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 403: forbidden request when the token belongs to a user with no proper right to perform the request
     * - 404: token provided but invalid
     * - 404: user cannot be found
     * - 409: email already taken
     * - 409: username already taken
     */


    /**
     * @group patchRoleUser
     */
    public function testPatchRoleUser_400_wrongData()
    {
        $this->httpRequest400(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->user["id"]."/change_role",
                "token" => $this->admin["token"],
            ],
            ["role" => "ROLE_TOTO"],
            ["code" => 400, "message" => "Bad Request"]
        );
    }

    /**
     * @group patchRoleUser
     */
    public function testPatchRoleUser_401_tokenNotProvided()
    {
        $this->httpRequest401(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->user["id"]."/change_role",
            ]
        );
    }

    /**
     * @group patchRoleUser
     */
    public function testPatchRoleUser_403_forbidden()
    {
        $this->httpRequest403(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->admin["id"]."/change_role",
                "token" => $this->user["token"],
            ],
            ["role" => "ROLE_ADMIN"],
            [
                "code" => 403,
                "message" => "Forbidden",
            ]
        );
    }

    /**
     * @group patchRoleUser
     */
    public function testPatchRoleUser_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->user["id"]."/change_role",
                "token" => substr($this->admin["token"], 0, -1),
            ],
            ["role" => "ROLE_ADMIN"],
            [
                "code" => 404,
                "message" => "Not Found",
                "exception_message" => "User cannot be found.",

            ]
        );
    }

    /**
     * @group patchRoleUser
     */
    public function testPatchRoleUser_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "PATCH", "path" => '/api'.$this->endpoint."/0/change_role", "token" => $this->admin["token"]],
            ["role" => "ROLE_ADMIN"],
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }


    /**
     * @group patchRoleUser
     */
    public function testPatchRoleUser_200_succesful()
    {
        $this->httpRequest200(
            [
                "method" => "PATCH",
                "path" => '/api'.$this->endpoint."/".$this->user["id"]."/change_role",
                "token" => $this->admin["token"],
            ],
            ["role" => "ROLE_ADMIN"],
            [
                "email" => "user@ileotech.com",
                "username" => "ileo-user",
                "role" => "ROLE_ADMIN",
            ]
        );
    }

    // TODO test for avatar

}
