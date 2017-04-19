<?php namespace Tests\AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ContainerInterface|null
     */
    protected $container;

    public function setup()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->serializer = $this->container->get("custom_serializer");
    }



    /**
     * @param array $params
     * @param array $data
     * @return array
     */
    private function request($params, $data)
    {
        $this->client = static::createClient();
        if (isset($params["token"])) {
            $this->client->setServerParameter("HTTP_Authorization", sprintf("Bearer %s", $params["token"]));
        }
        $http = [];
        if ($params["method"] === "GET" || $params["method"] === "DELETE") {
           $this->client->request($params["method"], $params["path"]);
        } else {
            $this->client->request($params["method"], $params["path"], $data);
        }
        $http["response"] = $this->client->getResponse();
        $http["content"] = $http["response"]->getContent();
        $http["decoded"] = json_decode($http["content"], true);

        return $http;
    }

    /**
     *
     * 200 OK
     *
     * @param array $params
     * @param array|null $data
     * @param array|null $expected
     * @return mixed
     */
    public function httpRequest200($params, $data = null, $expected = null)
    {
        $http = $this->request($params, $data);

//         if ($http["response"]->getStatusCode() != 200)
            // var_dump($http["decoded"]);

        $this->assertEquals(200, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertArraySubset($expected, $http["decoded"]);
        }

        return $http["decoded"];
    }

    /**
     *
     * 204 No Content
     *
     * @param array $params
     * @param array|null $data
     * @param array|null $expected
     * @return mixed
     */
    public function httpRequest204($params, $data = null, $expected = null)
    {
        $http = $this->request(
            $params,
            $data
        );

        // if ($http["response"]->getStatusCode() != 204) var_dump($http["decoded"]);

        $this->assertEquals(204, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertArraySubset($expected, $http["decoded"]);
        }

        return $http["decoded"];
    }

    /**
     *
     * 400 Bad Request
     *
     * @param array $params
     * @param array|null $data
     * @param array $expected
     * @return mixed
     */
    public function httpRequest400(
        $params,
        $data = null,
        $expected = ["code" => 400, "message" => "Bad Request", "exception_message" => "Bad Request"]
    ) {
        $http = $this->request(
            $params,
            $data
        );

        // if ($http["response"]->getStatusCode() != 400) var_dump($http["decoded"]);

        $this->assertEquals(400, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertEquals($expected["code"], $http["decoded"]["error"]["code"]);
            $this->assertEquals($expected["message"], $http["decoded"]["error"]["message"]);
            $this->assertEquals($expected["exception_message"], $http["decoded"]["error"]["exception"][0]["message"]);
        }

        return $http["decoded"];
    }

    /**
     *
     * 401 Unauthorized
     *
     * @param array $params
     * @param array|null $data
     * @param array $expected
     * @return mixed
     */
    public function httpRequest401(
        $params,
        $data = null,
        $expected = [
            "code" => 401,
            "message" => "Unauthorized",
            "exception_message" => "You must provide a valid token.",
        ]
    ) {
        $http = $this->request(
            $params,
            $data
        );

        //         if ($http["response"]->getStatusCode() != 401) var_dump($http["decoded"]);

        $this->assertEquals(401, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertEquals($expected["code"], $http["decoded"]["error"]["code"]);
            $this->assertEquals($expected["message"], $http["decoded"]["error"]["message"]);
            $this->assertEquals($expected["exception_message"], $http["decoded"]["error"]["exception"][0]["message"]);
        }

        return $http["decoded"];
    }

    /**
     *
     * 403 Forbidden
     *
     * @param array $params
     * @param array|null $data
     * @param array $expected
     * @return mixed
     */
    public function httpRequest403(
        $params,
        $data = null,
        $expected = ["code" => 403, "message" => "Forbidden", "exception_message" => "Resource forbidden."]
    ) {
        $http = $this->request(
            $params,
            $data
        );
        // if ($http["response"]->getStatusCode() != 403) var_dump($http["decoded"]);
        $this->assertEquals(403, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertEquals($expected["code"], $http["decoded"]["error"]["code"]);
            $this->assertEquals($expected["message"], $http["decoded"]["error"]["message"]);
            $this->assertEquals($expected["exception_message"], $http["decoded"]["error"]["exception"][0]["message"]);
        }

        return $http["decoded"];
    }

    /**
     *
     * 404 Not Found
     *
     * @param array $params
     * @param array|null $data
     * @param array $expected
     * @return mixed
     */
    public function httpRequest404(
        $params,
        $data = null,
        $expected = ["code" => 404, "message" => "Not Found", "exception_message" => "Resource cannot be found."]
    ) {
        $http = $this->request($params, $data);
        $this->assertEquals(404, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertEquals($expected["code"], $http["decoded"]["error"]["code"]);
            $this->assertEquals($expected["message"], $http["decoded"]["error"]["message"]);
            $this->assertEquals($expected["exception_message"], $http["decoded"]["error"]["exception"][0]["message"]);
        }

        return $http["decoded"];
    }

    /**
     * @param array $params
     * @param array|null $data
     * @param array $expected
     * @return mixed
     */
    public function httpRequest409(
        $params,
        $data = null,
        $expected = ["code" => 409, "message" => "Conflict", "exception_message" => ""]
    ) {
        $http = $this->request($params, $data);

        // if ($http["response"]->getStatusCode() != 409) var_dump($http["decoded"]);

        $this->assertEquals(409, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertEquals($expected["code"], $http["decoded"]["error"]["code"]);
            $this->assertEquals($expected["message"], $http["decoded"]["error"]["message"]);
            $this->assertEquals($expected["exception_message"], $http["decoded"]["error"]["exception"][0]["message"]);
        }

        return $http["decoded"];
    }

    /**
     *
     * 422 Unprocessable entity
     *
     * @param array $params
     * @param array|null $data
     * @param array $expected
     * @return mixed
     */
    public function httpRequest422(
        $params,
        $data = null,
        $expected = ["code" => 422, "message" => "Unprocessabled Entity", "exception_message" => ""]
    ) {
        $http = $this->request(
            $params,
            $data
        );

        // if ($http["response"]->getStatusCode() != 422) var_dump($http["decoded"]);

        $this->assertEquals(422, $http["response"]->getStatusCode());
        if ($expected) {
            $this->assertEquals($expected["code"], $http["decoded"]["error"]["code"]);
            $this->assertEquals($expected["message"], $http["decoded"]["error"]["message"]);
            $this->assertEquals($expected["exception_message"], $http["decoded"]["error"]["exception"][0]["message"]);
        }

        return $http["decoded"];
    }

}

