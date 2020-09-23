<?php


namespace App\Tests\Mocks;


use App\Entity\DailySummary;
use App\HrTools\Personio\Gateway;
use App\HrTools\Personio\HttpClient;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PersonioGateway extends Gateway
{

    public function __construct(HttpClient $client)
    {
        parent::__construct($client);
    }

    public function getEmployees()
    {
        throw new \Exception('Implement get employees in personio mock');
    }

    public function getAttendanceForEmployee(array $employeeIds, \DateTime $startDate, \DateTime $endDate)
    {
        return [
            'success' => true,
            'message' => 'message'
        ];
    }

    public function postAttendanceForEmployee(int $employeeId, DailySummary $ds)
    {
        return [
            'success' => true,
            'message' => 'message'
        ];
    }
}
