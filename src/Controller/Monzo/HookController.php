<?php


namespace App\Controller\Monzo;

use Amelia\Monzo\Exceptions\AccessDeniedException;
use App\Monzo\Authenticator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\VarDumper\Caster\Caster;

class HookController extends AbstractController implements LoggerAwareInterface
{
    /** @var Authenticator */
    private $monzoAuth;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(Authenticator $monzoAuth)
    {
        $this->monzoAuth = $monzoAuth;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/monzo/{accountId}/registerhook", name="monzo_account_registerhook")
     */
    public function registerHookAction(string $accountId)
    {
        if (isset($_ENV['MONZO_WEBHOOK_URL'])) {
            $url = $_ENV['MONZO_WEBHOOK_URL'].$this->generateUrl('hook_monzo', ['accountId' => $accountId]);
        } else {
            $url = $this->generateUrl('hook_monzo', ['accountId' => $accountId], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        $this->logger->info('Registering "'.$url.'" for account "'.$accountId.'"');
        $response = $this->monzoAuth->getAuthenticatedMonzo()->registerWebhook($url);
        $this->logger->debug((string)$response->toJson());
        $this->addFlash('info', 'Webhook registered');
        return $this->redirectToRoute('monzo_accounts');
    }

    /**
     * @Route("/monzo/{accountId}/removehooks", name="monzo_account_removehooks")
     */
    public function removeHooksAction(string $accountId)
    {
        $monzo = $this->monzoAuth->getAuthenticatedMonzo();
        foreach ($monzo->webhooks() as $webhook) {
            $monzo->deleteWebhook($webhook->id);
        }
        $this->addFlash('info', 'Webhooks removed');
        return $this->redirectToRoute('monzo_accounts');
    }

    /**
     * @Route("/hooks/monzo/{accountId}", name="hook_monzo")
     */
    public function receiveHookAction(string $accountId, Request $request)
    {
        /*
         * {
  "type": "transaction.created",
  "data": {
    "id": "tx_00009rbFEpbXtPXLEXfO7g",
    "created": "2020-02-01T16:48:56.137Z",
    "description": "Nick Peirson & Sarah Peirson",
    "amount": -100,
    "fees": {},
    "currency": "GBP",
    "merchant": null,
    "notes": "",
    "metadata": {
      "p2p_initiator": "internal",
      "p2p_transfer_id": "p2p_00009rbFEol52TZMbp7ULR"
    },
    "labels": null,
    "account_balance": 0,
    "attachments": null,
    "international": null,
    "category": "general",
    "categories": null,
    "is_load": false,
    "settled": "2020-02-01T16:48:56.137Z",
    "local_amount": -100,
    "local_currency": "GBP",
    "updated": "2020-02-01T16:48:56.231Z",
    "account_id": "acc_00009SjC52ObZsnmpAv2jR",
    "user_id": "user_00009Coa38mFVfSJ74MNoP",
    "counterparty": {
      "account_id": "acc_00009jgMWaTCCbwKoBeF2P",
      "name": "Nick Peirson & Sarah Peirson",
      "preferred_name": "Nick Peirson & Sarah Peirson",
      "user_id": "anonuser_74d62f854b53ea2cb42cb0"
    },
    "scheme": "p2p_payment",
    "dedupe_id": "p2p-payment:acc_00009SjC52ObZsnmpAv2jR:acc_00009SjC52ObZsnmpAv2jRuser_00009Coa38mFVfSJ74MNoP:e4248ee8-2022-4244-81c5-8e8a80a7b07e",
    "originator": true,
    "include_in_spending": true,
    "can_be_excluded_from_breakdown": true,
    "can_be_made_subscription": false,
    "can_split_the_bill": false,
    "can_add_to_tab": false,
    "amount_is_pending": false
  }
}
         */
        $this->logger->debug('Webhook triggered for account "'.$accountId.'"');
        $this->logger->debug('Webhook content: '.$request->getContent());
        return new Response();
    }
}