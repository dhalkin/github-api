<?php

namespace Assignment\GithubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


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

            // check github
            $client  = new Client(['base_uri' => self::BASE_URL]);

            try{

               //$res = $client->get('/users/dhalkin');
               $res = $client->get('authorizations', ['auth' => [$data['username'], $data['password']]]);
               $body = $res->getBody();

            }catch (RequestException $e){

                if($e->hasResponse()){
                    $code = $e->getResponse()->getStatusCode();
                    $e->getResponse()->getReasonPhrase();
                    sleep(1);
                }
            }



            //login process
            $token = new UsernamePasswordToken( $data['username'], $oauthToken, 'main', ['ROLE_USER']);
            $this->get('security.token_storage')->setToken($token);
            return $this->redirectToRoute('assignment_github_default_user');

        }


        return array('form'=>$form->createView());
    }


    /**
     * @Route("/login")
     */
    public function loginAction()
    {

            $client = new Client(
                [
                    // Base URI is used with relative requests
                    'base_uri' => 'http://ya.ru',
                    // You can set any number of default request options.
                    'timeout' => 2.0,
                ]
            );

            $response = $client->get('http://ya.ru');

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
     */
    public function userAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return array('user_name' => $user);
    }

}
