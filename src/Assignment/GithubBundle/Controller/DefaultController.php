<?php

namespace Assignment\GithubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{

    const BASE_URL = 'https://api.github.com';

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction(Request $request)
    {

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('assignment_github_default_user');
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('assignment_github_default_index'))
            ->setMethod('POST')
            ->add('username', 'text')
            ->add('password', 'text')
            ->add('Login', 'submit', array('label' => 'Login'))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            // data is an array with "user", "pass"
            $data = $form->getData();
            $gitHubClient = $this->container->get('github');

            $list = $this->container->get('github')->verifyAuth($data);
            if (!is_object($list)) {
                $this->addFlash(
                    'notice',
                    $list
                );
                    return $this->redirectToRoute('assignment_github_default_index');
            }

            return $this->redirectToRoute('assignment_github_default_user');

        }
        return array('form'=>$form->createView());
    }


    /**
     * @Route("/logout")
     */
    public function logoutAction()
    {
        $this->get('security.token_storage')->setToken(null);
        $this->get('request')->getSession()->invalidate();
        return $this->redirectToRoute('assignment_github_default_index');
    }


    /**
     * @Route("/user")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function userAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $list = $this->container->get('github')->getListRepoBaseAuth();
        return array('user_name' => $user, 'list_repo'=> $list);
    }
}
