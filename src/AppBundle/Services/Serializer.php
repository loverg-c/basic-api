<?php

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use JMS\Serializer\Exception\Exception;

/**
 * Class Serializer
 * @package AppBundle\Services
 */
class Serializer
{
    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * Serializer constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->serializer = $container->get('jms_serializer');
    }

    /**
     * @param object $obj the entity to serialize to json
     * @return string
     */
    public function serializeJson($obj)
    {
        return $this->serializer->serialize($obj, 'json');
    }

    /**
     * Deserialize the json string into an entity
     * @param string $data the json string to deserialize
     * @param string $entityName the name of the entity in AppBundle\Entity where to deserialize
     * @return null|object
     */
    public function deserializeJson($data, $entityName)
    {
        try {
            $object = $this->serializer->deserialize($data, "AppBundle\\Entity\\" . $entityName, 'json');
        } catch (Exception $e) {
            echo $e;
            return null;
        }

        return $object;
    }

}
