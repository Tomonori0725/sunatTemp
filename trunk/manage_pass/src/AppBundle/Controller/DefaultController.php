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
        $sort = $request->query->get('sort');
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $limit = $this->container->getParameter('page_per_account');

        $offset = 0;





        /*$em = $this->get('doctrine.orm.entity_manager');
        $dpl = "SELECT a FROM AppBundle:Account a";
        $query = $em->createQuery($dpl);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );*/




        if($sort == 'account_asc'){
            $accounts = $repository->findBy(array(), array('name' => 'ASC'), $limit, $offset);
        }elseif($sort == 'account_desc'){
            $accounts = $repository->findBy(array(), array('name' => 'DESC'), $limit, $offset);
        }elseif($sort == 'modified_asc'){
            $accounts = $repository->findBy(array(), array('modified_date' => 'ASC'), $limit, $offset);
        }elseif($sort == 'modified_desc'){
            $accounts = $repository->findBy(array(), array('modified_date' => 'DESC'), $limit, $offset);
        }else{
            $accounts = $repository->findBy(array(), array(), $limit, $offset);
        }
        
        //更新日時を変換
        $modifiedDate = array();
        foreach($accounts as $account){
            $modifiedDate[$account->getId()] = $account->getModifiedDate()->format('Y-m-d H:i');
        }

        return $this->render('account/index.html.twig',
        ['accounts' => $accounts, 'modified' => $modifiedDate, 'sort' => $sort]);
    }
}
