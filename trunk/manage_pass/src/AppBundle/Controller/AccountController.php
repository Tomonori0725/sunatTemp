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


class AccountController extends Controller
{
    /**
     * @Route("/", name="accountList")
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        //セッションを削除する
        $session = $request->getSession();
        if ( $session->has('account_add') ) {
            $session->remove('account_add');
        }
        if ( $session->has('account_edit') ) {
            $session->remove('account_edit');
        }

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
