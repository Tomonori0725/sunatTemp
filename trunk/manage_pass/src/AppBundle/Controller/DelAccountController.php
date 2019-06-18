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
use AppBundle\Service\ManageFunction;

class DelAccountController extends Controller
{
    /**
     * アカウントの削除
     * 
     * @Route("/delete/{id}", name="account_delete")
     * @Method({"DELETE"})
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($id);

        if (!$account) {
            throw $this->createNotFoundException('No account found for id = ' .$id);
        }
        if ($this->isCsrfTokenValid('account', $request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $manageFunc = new ManageFunction($this->container, $em);
            $em->remove($account);
            $em->flush();
            //.htaccessに書き込む
            $manageFunc->writePassword();
        }

        return $this->redirectToRoute('accountList');
    }

}
