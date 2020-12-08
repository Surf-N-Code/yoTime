<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use App\Mail\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPersister implements DataPersisterInterface
{

    private $entityManager;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    private Mailer $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder,
        Mailer $mailer
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->mailer = $mailer;
    }

    public function supports($data): bool
    {
        return $data instanceof User;
    }

    public function persist($data)
    {
        if (!$data->getId()) {
            $this->mailer->send('ndilthey@gmail.com', $data->getEmail(), 'Welcome', 'Welcome to YoTime');
        }

        if ($data->getPassword()) {
            $data->setPassword(
                $this->userPasswordEncoder->encodePassword($data, $data->getPassword())
            );
            $data->eraseCredentials();
        }
        $data->setIsActive(true);
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }
    public function remove($data)
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

}
