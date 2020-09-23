<?php


namespace App\Security\Slack\EventListener;

use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlackSecurityVoter;
use App\Slack\SlackClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SecurityListener
{

    private string $slackSignSecret;

    public function __construct($slackSignSecret)
    {
        $this->slackSignSecret = $slackSignSecret;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($this->isSecurityEnabledSlackRequest($request)) {
            $slackTimestamp = $request->headers->get('X-Slack-Request-Timestamp');
            $string = sprintf('v0:%s:%s', $slackTimestamp, $request->getContent());
            $mySig = 'v0='.hash_hmac('sha256', $string, $this->slackSignSecret);
            $slackSig = $request->headers->get('X-Slack-Signature');
//            dd($mySig, $slackSig, $string);

            if (!hash_equals((string)$slackSig, $mySig)) {
                $response = new Response();
                $response->setStatusCode(401);
                $event->setResponse($response);
            }
        }
    }

    private function isSecurityEnabledSlackRequest(Request $request): bool
    {
        return in_array($request->getRequestUri(), SlackSecurityVoter::SECURITY_ENABLED_ENDPOINTS, false);
    }
}
