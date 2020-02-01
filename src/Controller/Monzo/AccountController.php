<?php


namespace App\Controller\Monzo;

use Amelia\Monzo\Exceptions\AccessDeniedException;
use App\Monzo\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /** @var Authenticator */
    private $monzoAuth;

    public function __construct(Authenticator $monzoAuth)
    {
        $this->monzoAuth = $monzoAuth;
    }

    /**
     * @Route("/monzo/{accountId}/pots", name="monzo_account_pots")
     */
    public function potsAction(string $accountId)
    {
        $pots = $this->fetchPots($accountId);
        return $this->render('monzo/account/pots.html.twig', ['pots' => $pots]);
    }

    private function fetchPots(string $accountId)
    {
        $monzo = $this->monzoAuth->getAuthenticatedMonzo();

        try {
            $pots = $monzo->pots();
            $this->fetchPotsFromTransactions($accountId, $pots);
        } catch (AccessDeniedException $e) {
            $this->addFlash('notice', 'Please grant access in your Monzo app');
        }
        return $pots;
    }

    /**
     * @param string $accountId
     * @param Pot[] $pots
     */
    private function fetchPotsFromTransactions(string $accountId, $pots): void
    {
        $date = date(DATE_RFC3339, strtotime("89 days ago"));
        $monzo = $this->monzoAuth->getAuthenticatedMonzo();
        $potIds = [];
        foreach ($monzo->since($date)->transactions($accountId) as $transaction) {
            if (!empty($transaction->metadata['pot_id'])) {
                $potIds[$transaction->metadata['pot_id']] = $transaction->metadata['pot_id'];
            }
        }
        foreach ($potIds as $potId) {
            try {
                $pot = $monzo->pot($potId);
                $pots->add($pot);
            } catch (AccessDeniedException $e) {
                $this->addFlash('notice', 'We found a transaction for pot "' . $potId . '" but we couldn\'t access the pot.');
            }
        }
    }
}