<?php

namespace Msi\AdminBundle\Event;

use Symfony\Component\HttpFoundation\Response;

class GetResponseEntityEvent extends EntityEvent
{
    private $response;

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
