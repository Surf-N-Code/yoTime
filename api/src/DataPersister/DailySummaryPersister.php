<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\DailySummary;
use App\Exceptions\UniqueConstraintViolationException;
use App\Mail\Mailer;
use Doctrine\ORM\EntityManagerInterface;

class DailySummaryPersister implements ContextAwareDataPersisterInterface
{

    private $decorated;
    private $mailer;

    private EntityManagerInterface $em;

    public function __construct(
        ContextAwareDataPersisterInterface $decorated,
        Mailer $mailer,
        EntityManagerInterface $em
    )
    {
        $this->decorated = $decorated;
        $this->mailer = $mailer;
        $this->em = $em;
    }

    public function supports($data, array $context = []): bool
    {
        return $this->decorated->supports($data, $context);
    }

    public function persist($data, array $context = [])
    {
        try {
            $result = $this->decorated->persist($data, $context);
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\UniqueConstraintViolationException) {
                throw new UniqueConstraintViolationException(sprintf('A daily summary for this date: %s already exists.', $data->getDate()->format('Y-m-d')));
            } else {
                throw new \Exception($e->getMessage());
            }
        }

        if (
            $data instanceof DailySummary && (
                ($context['collection_operation_name'] ?? null) === 'post' ||
                ($context['collection_operation_name'] ?? null) === 'patch'
            )
        ) {
            $this->sendWelcomeEmail($data);
        }

        return $result;
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }

    private function sendWelcomeEmail(DailySummary $ds)
    {
        $originalData = $this->em->getUnitOfWork()->getOriginalEntityData($ds);
        $isMailSent = $originalData['isEmailSent'] ?? false;

        if (!$isMailSent) {
            $user = $ds->getUser();
            $mailSubject = 'Daily Summary - ' . $user->getFirstName() . ' ' . $user->getLastName();
            $this->mailer->send($_ENV['MAIL_SENDER'], $user->getEmail(), $mailSubject, $ds->getDailySummary());
        }
    }
}
