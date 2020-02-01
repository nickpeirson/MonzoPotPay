<?php
namespace App\Monzo;

use Amelia\Monzo\Client;
use Amelia\Monzo\Monzo;
use App\Entity\MonzoCredentials;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Authenticator
{
    /** @var string */
    private $id;
    /** @var string */
    private $secret;
    /** @var Session */
    private $session;
    /** @var Client */
    private $monzoClient;
    /** @var ManagerRegistry */
    private $doctrine;
    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(
        string $id,
        string $secret,
        SessionInterface $session,
        Client $monzoClient,
        ManagerRegistry $doctrine,
        UrlGeneratorInterface $router
    ) {
        $this->id = $id;
        $this->secret = $secret;
        $this->session = $session;
        $this->monzoClient = $monzoClient;
        $this->doctrine = $doctrine;
        $this->router = $router;
    }

    public function loadCredentials(UserInterface $user): bool
    {
        if ($this->session->has('monzo_credentials')) {
            return true;
        }
        $username = $user->getUsername();
        $repository = $this->doctrine->getRepository(MonzoCredentials::class);

        if ($credentials = $repository->findOneBy(['userId' => $username])) {
            $this->session->set('monzo_credentials', $credentials);
            return true;
        }
        return false;
    }

    public function getAuthURL()
    {
        $stateToken = bin2hex(\random_bytes(16));
        $this->session->set('state_token', $stateToken);
        return 'https://auth.monzo.com/?client_id='.$this->id
            .'&redirect_uri='.$this->getRedirectURI()
            .'&response_type=code'
            .'&state='.$stateToken;
    }

    private function getRedirectURI(): string
    {
        return $this->router->generate('monzo_connect', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param UserInterface $user
     * @param $stateToken
     * @param $code
     * @throws StateException
     */
    public function connectMonzo(UserInterface $user, $stateToken, $code): void
    {
        $this->validateState($stateToken);
        $response = $this->exchangeAuthCodeForToken($code);
        $this->saveCredentials($user, $response);
    }

    private function validateState(string $stateToken): void
    {
        if (!($storedStateToken = $this->session->get('state_token'))) {
            throw new StateException('We can\'t find a matching Monzo authentication request. Please try connecting again.');
        }
        if ($storedStateToken != $stateToken) {
            throw new StateException('The Monzo token didn\'t match your stored token. Please try connecting again.');
        }
    }

    /**
     * @param string $code
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function exchangeAuthCodeForToken(string $code)
    {
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->id,
            'client_secret' => $this->secret,
            'redirect_uri' => $this->getRedirectURI(),
            'code' => $code
        ];
        return $this->monzoClient->call('POST', 'oauth2/token', [], $data);
    }

    /**
     * @param UserInterface $user
     * @param array $monzoAuthResponse
     */
    private function saveCredentials(UserInterface $user, array $monzoAuthResponse): void
    {
        $user = $user->getUsername();
        $entityManager = $this->doctrine->getManager();
        $repository = $this->doctrine->getRepository(MonzoCredentials::class);

        if ($credentials = $repository->findOneBy(['userId' => $user])){
            $entityManager->remove($credentials);
        }

        $credentials = new MonzoCredentials();
        $credentials->setAccessToken($monzoAuthResponse['access_token'])
            ->setRefreshToken($monzoAuthResponse['refresh_token'])
            ->setClientId($monzoAuthResponse['client_id'] ?? '')
            ->setScope($monzoAuthResponse['scope'] ?? '')
            ->setTokenType($monzoAuthResponse['token_type'] ?? '')
            ->setMonzoUserId($monzoAuthResponse['user_id'] ?? '')
            ->setUserId($user);

        $this->session->set('monzo_credentials', $credentials);
        $entityManager->persist($credentials);
        $entityManager->flush();
    }

    public function getAuthenticatedMonzo()
    {
        /** @var MonzoCredentials $creds */
        $creds = $this->session->get('monzo_credentials');
        return (new Monzo($this->monzoClient))
            ->as($creds->getAccessToken(), $creds->getRefreshToken());
    }
}