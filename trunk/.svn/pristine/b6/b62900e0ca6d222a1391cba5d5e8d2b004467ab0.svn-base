<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Account;
use AppBundle\Repository\AccountRepository;

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
        if($sort == 'account_asc'){
            $accounts = $repository->findBy(array(), array('name' => 'ASC'), 3);
        }elseif($sort == 'account_desc'){
            $accounts = $repository->findBy(array(), array('name' => 'DESC'), 3);
        }elseif($sort == 'modified_asc'){
            $accounts = $repository->findBy(array(), array('modified_date' => 'ASC'), 3);
        }elseif($sort == 'modified_desc'){
            $accounts = $repository->findBy(array(), array('modified_date' => 'DESC'), 3);
        }else{
            $accounts = $repository->findBy(array(), array(), 3);
        }
        
        //更新日時を変換
        $modifiedDate = array();
        foreach($accounts as $account){
            $modifiedDate[$account->getId()] = $account->getModifiedDate()->format('Y-m-d H:i');
        }

        return $this->render('account/index.html.twig', ['accounts' => $accounts, 'modified' => $modifiedDate, 'sort' => $sort]);
    }
}
