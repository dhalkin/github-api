<?php

namespace Assignment\GithubBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Github
{
    const BASE_URL = 'https://api.github.com';
    const USER_HASH = '7c61a8a8293af39e2fb7e7a721daf6393562b6f1';

    /**
     * @var Client
     */
    private $client;
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->client = new Client(['base_uri' => self::BASE_URL]);

    }

    public function verifyAuth($data)
    {
        try {
             $res = $this->client->get('user', ['auth' => [$data['username'], $data['password']]]);
             $body = json_decode($res->getBody()->getContents());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                //$code = $e->getResponse()->getStatusCode();
                return $e->getResponse()->getReasonPhrase();
            }
        }

        //login process
        $token = new UsernamePasswordToken($data['username'], $data['password'], 'main', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
        return $body;
    }


    public function getListRepoBaseAuth()
    {

        $user =  $this->tokenStorage->getToken()->getUser();
        $credentials =  $this->tokenStorage->getToken()->getCredentials();

        try {
            $res = $this->client->get('user/repos', ['auth' => [$user, $credentials]]);
            $body = json_decode($res->getBody()->getContents());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                //$code = $e->getResponse()->getStatusCode();
                return $e->getResponse()->getReasonPhrase();
            }
        }
        return $body;
    }
}
