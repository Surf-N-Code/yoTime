<?php


namespace App\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\DailySummary;
use App\Mail\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DailySummaryListener implements EventSubscriberInterface
{

    private $mailer;

    private EntityManagerInterface $em;

    public function __construct(Mailer $mailer, EntityManagerInterface $em)
    {
        $this->mailer = $mailer;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                'sendMail', EventPriorities::POST_WRITE,
            ],
        ];
    }

    public function sendMail(ViewEvent $event): void
    {
        $ds = $event->getControllerResult();

        if (!$ds instanceof DailySummary && Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $originalData = $this->em->getUnitOfWork()->getOriginalEntityData($ds);
        $shouldSendMail = $originalData['isEmailSent'] ?? false;

        if ($shouldSendMail) {
            $user = $ds->getUser();
            $mailSubject = 'Daily Summary - ' . $user->getFirstName() . ' ' . $user->getLastName();
            $this->mailer->send($_ENV['MAIL_SENDER'], $user->getEmail(), $mailSubject, $ds->getDailySummary());
        }
    }
}
