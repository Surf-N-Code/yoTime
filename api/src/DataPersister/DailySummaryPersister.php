<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\DailySummary;
use App\Entity\Task;
use App\Entity\User;
use App\Exceptions\UniqueConstraintViolationException;
use App\Mail\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DailySummaryPersister implements DataPersisterInterface
{

    private $entityManager;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    private Mailer $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        Mailer $mailer
    )
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function supports($data): bool
    {
        return $data instanceof DailySummary;
    }

    public function persist($data)
    {
//        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
//        $hasSentMailAlready = $originalData['isEmailSent'] ?? false;
//
//        if (!$hasSentMailAlready) {
//            $this->mailer->send('ndilthey@gmail.com', 'ndilthey@gmail.com', 'Daily Summary Mail', 'DS Mail');
//        }

        try {
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            if ($e instanceof UniqueConstraintViolationException) {
                throw new UniqueConstraintViolationException(sprintf('A daily summary for this date: %s already exists.', $data->getDate()));
            }
        }
    }
    public function remove($data)
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

}
