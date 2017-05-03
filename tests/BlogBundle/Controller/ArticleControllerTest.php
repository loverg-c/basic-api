<?php

namespace Tests\BlogBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadUserData;
use AppBundle\DataFixtures\Services\FixtureAwareTestCase;
use BlogBundle\DataFixtures\ORM\LoadArticleData;
use BlogBundle\DataFixtures\ORM\LoadCategoryData;
use BlogBundle\DataFixtures\ORM\LoadTagData;
use Tests\AppBundle\Services\TestCase;


/**
 * Class ArticleControllerTest
 * @package Tests\AppBundle\Controller
 */
class ArticleControllerTest extends TestCase
{

    /**
     * API endpoint: article.
     */
    private $endpoint = "/articles";


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
     * First article
     */
    private $firstArticle = [];

    /**
     * @var array
     */
    private $expected = [
        "title" => "Sortie du nouvel album d\\'Ultra Vomit",
        "content" => "&lt;div class=&quot;entry&quot;&gt;\n\t\t\t\t&lt;p&gt;\n\t\t\t\t    &lt;strong&gt;ULTRA VOMIT&lt;/strong&gt; dévoile les premiers détails de son nouvel album, intitulé Panzer Surprise, prévu le 28 avril chez Verycords.\n\t\t\t\t    &lt;span id=&quot;more-251927&quot;&gt;&lt;/span&gt;\n\t\t\t\t&lt;/p&gt;\n                &lt;p&gt;Teaser réalisé par Nicolas Leroy :&lt;/p&gt;\n                &lt;p&gt;&lt;/p&gt;\n                &lt;center&gt;\n                    &lt;iframe src=&quot;https://www.youtube.com/embed/geH49uzJooU&quot; allowfullscreen=&quot;&quot; height=&quot;315&quot; frameborder=&quot;0&quot; width=&quot;560&quot;&gt;&lt;/iframe&gt;\n                &lt;/center&gt;\n                &lt;p&gt;&lt;/p&gt;\n                &lt;p&gt;Artwork :&lt;/p&gt;\n                &lt;p&gt;\n                    &lt;img src=&quot;http://www.radiometal.com/wp-content/uploads/2017/03/17498539_10155131581851972_2622969336406760238_n.jpg&quot; alt=&quot;&quot;\n                        class=&quot;aligncenter size-full wp-image-251929&quot;\n                        sizes=&quot;(max-width: 960px) 100vw, 960px&quot; height=&quot;500&quot;&gt;\n                &lt;/p&gt;\n            ",
        "author" => [
            "username" => "ileo-admin",
            "role" => "ROLE_SUPER_ADMIN",
            "email" => "admin@ileotech.com",
        ],
        "category" => [
            "title" => "musique",
        ],
        "tags" => [
            [
                "title" => "metal",
            ],
            [
                "title" => "rock",
            ],
            [
                "title" => "parodie",
            ],
            [
                "title" => "humour",
            ],
        ],
        "likes" => [
            [
                "username" => "ileo-user",
                "role" => "ROLE_USER",
                "email" => "user@ileotech.com",
            ],
        ],
    ];

    /**
     * Setup:
     * - load required fixtures
     * - fetch admin and article information: tokens and ids
     */
    public function setup()
    {
        parent::setup();

        $fixtures = [
            new LoadUserData(),
            new LoadTagData(),
            new LoadCategoryData(),
            new LoadArticleData(),
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

        // get the article information
        $response = $this->httpRequest200(
            ["method" => "POST", "path" => "/api/auth"],
            ["username" => "ileo-user", "password" => "user"]
        );
        $this->user["token"] = $response["token"];
        $this->user["id"] = $response["idUser"];

        $listArticles = $this->httpRequest200(
            [
                "method" => "GET",
                "path" => '/api/blog'.$this->endpoint,
                "token" => $this->admin["token"],
            ]
        );

        $this->firstArticle = $listArticles[0];

    }


    /**
     * Get list of articles: GET /articles
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 404: token provided but invalid
     */

    /**
     * @group getArticleList
     */
    public function testGetArticleList_401_tokenNotProvided()
    {
        $this->httpRequest401(["method" => "GET", "path" => '/api/blog'.$this->endpoint]);
    }

    /**
     * @group getArticleList
     */
    public function testGetArticleList_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "GET",
                "path" => '/api/blog'.$this->endpoint,
                "token" => substr($this->admin["token"], 0, -1),
            ],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }


    /**
     * @group getArticleList
     */
    public function testGetArticleList_200_successful()
    {

        $this->httpRequest200(
            [
                "method" => "GET",
                "path" => '/api/blog'.$this->endpoint,
                "token" => $this->admin["token"],
            ],
            null,
            [
                $this->expected,
            ]
        );
    }


    //todo test for get by id

    /**
     * Get a article by id: GET /articles/:article_id
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 404: token provided but invalid
     * - 404: article cannot be found
     */

    /**
     * @group getArticleById
     */
    public function testGetArticleById_401_tokenNotProvided()
    {
        $this->httpRequest401(["method" => "GET", "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle['id']]);
    }

    /**
     * @group getArticleById
     */
    public function testGetArticleById_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "GET",
                "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle['id'],
                "token" => substr($this->admin["token"], 0, -1),
            ],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "User cannot be found."]
        );
    }

    /**
     * @group getArticleById
     */
    public function testGetArticleById_404_onArticle()
    {
        $this->httpRequest404(
            ["method" => "GET", "path" => '/api/blog'.$this->endpoint."/0", "token" => $this->admin["token"]],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "Article cannot be found."]
        );
    }

    /**
     * @group getArticleById
     */
    public function testGetArticleById_200_successful()
    {
        $this->httpRequest200(
            array(
                "method" => "GET",
                "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle['id'],
                "token" => $this->admin["token"],
            ),
            null,
            $this->expected
        );
    }



    //todo test for post
    //todo test for put
    //todo test for patch



    /**
     * Delete a user : DELETE /articles/:article_id
     * Tests:
     * - 200: successful
     * - 401: token not provided
     * - 403: forbidden request when the token belongs to a user with no proper right to perform the request
     * - 404: token provided but invalid
     * - 404: article cannot be found
     */

    /**
     * @group deleteArticle
     */
    public function testDeleteArticle_401_tokenNotProvided()
    {
        $this->httpRequest401(array("method" => "DELETE", "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle["id"]));
    }

    /**
     * @group deleteArticle
     */
    public function testDeleteArticle_403_forbidden()
    {
        $this->httpRequest403(
            [
                "method" => "DELETE",
                "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle["id"],
                "token" => $this->user["token"],
            ],
            null,
            ["code" => 403, "message" => "Forbidden"]
        );
    }

    /**
     * @group deleteArticle
     */
    public function testDeleteArticle_404_invalidToken()
    {
        $this->httpRequest404(
            [
                "method" => "DELETE",
                "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle["id"],
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
     * @group deleteArticle
     */
    public function testDeleteArticle_404_onUser()
    {
        $this->httpRequest404(
            ["method" => "DELETE", "path" => '/api/blog'.$this->endpoint."/0", "token" => $this->admin["token"]],
            null,
            ["code" => 404, "message" => "Not Found", "exception_message" => "Article cannot be found."]
        );
    }

    /**
     * @group deleteArticle
     */
    public function testDeleteArticle_200_successful()
    {
        $this->httpRequest200(
            array(
                "method" => "DELETE",
                "path" => '/api/blog'.$this->endpoint."/".$this->firstArticle["id"],
                "token" => $this->admin["token"],
            ),
            null,
            [
                "exception_message" => "Delete has been done, you will never see it again",
            ]
        );
    }



}
