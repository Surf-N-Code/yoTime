<?php


namespace App\Services;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DatabaseHelper {

    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger){
        $this->em = $em;
        $this->logger = $logger;
    }

    public function flushAndPersist($object): void
    {
        try {
            $this->em->persist($object);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->error('Error adding object to db with message: ' . $e->getMessage());
            throw new \RuntimeException('An error occurred while ending your timer. Please contact support', 400);
        }
    }
}
