<?php

namespace Customize\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class SamplePageController extends AbstractController
{
    /**
     * @Method("GET")
     * @Route("/sample")
     * @Template("Sample/index.twig")
     */
    public function testMethod()
    {
        return ['name' => 'EC-CUBE'];
    }

}
