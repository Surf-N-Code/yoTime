<?php


namespace App\Handler\MessageController\Slack;

use App\Entity\Slack\SlackInteractionEvent;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Services\UserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class InteractionEventController implements MessageHandlerInterface
{
    private DailySummaryHandler $dailySummaryHandler;

    private UserProvider $userProvider;

    private LoggerInterface $logger;

    public function __construct(
        DailySummaryHandler $dailySummaryHandler,
        UserProvider $userProvider,
        LoggerInterface $logger
    )
    {
        $this->dailySummaryHandler = $dailySummaryHandler;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    public function __invoke(SlackInteractionEvent $interactionEvent)
    {
        $payload = $interactionEvent->getPayload();
        try {
            try {
                $user = $this->userProvider->getDbUserBySlackId($payload['user']['id']);
            } catch (\Exception $e) {
                throw new MessageHandlerException('Seems like you have not registered an account with YoTime yet. Please contact the admin of your slack workspace to add you to the service.', Response::HTTP_PRECONDITION_FAILED,);
            }

            if (!empty($payload['view']['state']['values']['daily_summary_block'] && $payload['type'] === 'view_submission')) {
                $modalStatus = $this->dailySummaryHandler->handleModalSubmission($payload, $user);
                $confirmView = $this->dailySummaryHandler->getDailySummaryConfirmView($modalStatus);
                return new JsonResponse($confirmView, 200);
            }

            if ($payload['type'] === 'block_actions') {
                return new Response('successful block action', 200);
            }

            return new Response(sprintf('Unsupported event detected in payload: %s', json_encode($payload)), 400);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Something went wrong setting the daily summary with message: %s', $e->getMessage()));
            return new Response($e->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
