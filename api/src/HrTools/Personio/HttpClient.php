<?php


namespace App\HrTools\Personio;


use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClient
{

    private HttpClientInterface $personioClient;

    private string $personioClientId;

    private string $personioClientSecret;

    public function __construct(HttpClientInterface $personioClient, $personioClientId, $personioClientSecret)
    {
        $this->personioClient = $personioClient;
        $this->personioClientId = $personioClientId;
        $this->personioClientSecret = $personioClientSecret;
    }

    public function request(string $method, string $url, array $options = [])
    {
        if (!isset($options['auth_bearer'])) {
            $token = $this->getToken();
            $options['auth_bearer'] = $token;
            $options['headers']['HTTP_ACCEPT'] = 'application/json';
            $options['headers']['HTTP_CONTENT_TYPE'] = 'application/json';
        }
        return $this->personioClient->request($method, $url, $options);
    }

    private function getToken()
    {
        $response = $this->personioClient->request('POST', 'auth', [
            'json' => [
                'client_id' => $this->personioClientId,
                'client_secret' => $this->personioClientSecret,
            ]
        ]);

        $response = json_decode(
            $response->getContent(false),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if (isset($response['success'], $response['data']['token']) && $response['success'] === true) {
            return $response['data']['token'];
        }
        throw new AuthenticationException(sprintf('Personio API Token could not be retrieved with object: %s', json_encode($response)), 400);
    }
}
