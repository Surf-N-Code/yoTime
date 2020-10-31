<?php


namespace App\Tests;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class IntegrationTestCase extends ApiTestCase
{
    public const SLACK_SECRET = '41fc7fe005b809f97d50394d82442186';

    public function getValidSlackHeaders($data, $contentType)
    {
        $slackTimestamp = 1600676620;
        $string = sprintf('v0:%s:%s', $slackTimestamp, json_encode($data));
        $mySig = 'v0='.hash_hmac('sha256', $string, self::SLACK_SECRET);

        return [
            'x-slack-request-timestamp' => $slackTimestamp,
            'x-slack-signature' => $mySig,
            'content-type' => $contentType,
            'accept' => 'application/json'
        ];
    }

    protected function createAuthenticatedClient()
    {
        $user = [
            'email' => 'norman@yazio.com',
            'password' => 'trustno1'
        ];

        $client = static::createClient();
        $response = $client->request(
            'POST',
            '/token',
            [
                'json' => $user,
                'base_uri' => 'https://localhost:8443'
            ]
        );

        $data = json_decode($response->getContent(), true);
        $client = static::createClient([],['auth_bearer' => $data['token']]);

        return $client;
    }

    public function truncateTableForClass($class)
    {
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $classMetaData = $em->getClassMetadata($class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
        $connection->commit();
    }
}
