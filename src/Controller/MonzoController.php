<?php


namespace App\Controller;

use Amelia\Monzo\Exceptions\AccessDeniedException;
use Amelia\Monzo\Exceptions\InvalidTokenException;
use App\Monzo\Authenticator;
use App\Monzo\StateException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class MonzoController extends AbstractController
{
    /** @var Authenticator */
    private $monzoAuth;

    public function __construct(
        Authenticator $monzoAuth
    ) {
        $this->monzoAuth = $monzoAuth;
    }

    /**
     *
     * @Route("/monzo/connect", name="monzo_connect")
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function monzoConnectAction(Request $request)
    {
        $stateToken = $request->query->get('state');
        $code = $request->query->get('code');
        try {
            $this->monzoAuth->connectMonzo($this->getUser(), $stateToken, $code);

            return $this->render('monzo/connect.html.twig', ['content' => 'Monzo connected']);
        } catch (StateException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('index');
        } catch (InvalidTokenException $e) {
            $this->addFlash('error', 'The authorisation token has already been used.');
            return $this->redirectToRoute('index');
        }
    }

    /**
     *
     * @Route("/monzo/accounts", name="monzo_accounts")
     *
     * @return Response
     */
    public function listAccounts(SessionInterface $session)
    {
        $monzo = $this->monzoAuth->getAuthenticatedMonzo();
        $accounts = [];
        $owners = [];
        try {
            $accounts = $monzo->accounts();
            $owners = [];
            foreach ($accounts as $account) {
                foreach ($account->owners as $owner) {
                    $owners[$owner['user_id']] = $owner['preferred_name'];
                }
            }
        } catch (AccessDeniedException $e) {
            $this->addFlash('notice', 'Please grant access in your Monzo app');
        }
        return $this->render('monzo/accounts.html.twig', ['accounts' => $accounts, 'owners' => $owners]);
    }
}