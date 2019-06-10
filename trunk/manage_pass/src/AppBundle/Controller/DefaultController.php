<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Account;
use AppBundle\Repository\AccountRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="accountList")
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $limit = $this->container->getParameter('page_per_account');

        $em = $this->get('doctrine.orm.entity_manager');
        $dpl = "SELECT a FROM AppBundle:Account a";
        $query = $em->createQuery($dpl);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('account/index.html.twig', ['pagination' => $pagination]);
    }
}
