<?php


namespace App\Services;


use App\Entity\Slack\SlackUser;
use App\Entity\SlackTeam;
use App\Entity\Task;
use App\Entity\User;
use App\Slack\SlackClient;use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class UserProvider
{
    private $em;
    private $client;
    private $logger;

    /** @var \Symfony\Component\Serializer\SerializerInterface */
    private $serializer;

    public function __construct(EntityManagerInterface $em, SlackClient $client, LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->client = $client;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function populateUserEntityFromSlackInfo(SlackUser $slackUser): User
    {
        $dbUser = new User();
        $dbUser->setDisplayName($slackUser->getRealName());
        $dbUser->setEmail($slackUser->getProfile() ? $slackUser->getProfile()->getEmail() : null);
        $dbUser->setFullName($slackUser->getRealName());
        $dbUser->setSlackUserId($slackUser->getId());
        $dbUser->setTz($slackUser->getTz());
        $dbUser->setTzOffset($slackUser->getTzOffset());
        $dbUser->addSlackTeam((new SlackTeam())->setTeamId($slackUser->getTeamId()));

        return $dbUser;
    }

    public function getSlackUserInfo($slackUserId): SlackUserW
    {
        try {
            $response = $this->client->getSlackUserProfile($slackUserId);
            $data = json_decode($response->getContent(), true);
            return $this->serializer->denormalize($data['user'], SlackUser::class, null);
        } catch (\Exception $e) {
            $msg = sprintf('That slack ID seems to be incorrect. Could not find slack user with slack ID: %s', $slackUserId);
            throw new NotFoundHttpException($msg);
        }
    }

    public function getDbUserBySlackId($slackUserId): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['slackUserId' => $slackUserId]);
        if (!$user) {
            $msg = sprintf('Could not find user with slack ID: %s in our database. The user in question should use the `/register` command', $slackUserId);
            $this->logger->error($msg);
            throw new NotFoundHttpException($msg);
        }

        return $user;
    }
}
