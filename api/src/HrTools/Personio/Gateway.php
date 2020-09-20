<?php


namespace App\HrTools\Personio;


use App\Entity\DailySummary;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class Gateway
{

    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function getEmployees()
    {
        $response = $this->client->request('GET', 'company/employees');
        return json_decode($response->getContent(false), true);
    }

    public function getAttendanceForEmployee(array $employeeIds, \DateTime $startDate, \DateTime $endDate)
    {
        $response = $this->client->request('GET', 'company/attendances', [
            'query' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'employees' => implode(',', $employeeIds)
            ],
        ]);
        return json_decode($response->getContent(false), true);
    }

    public function postAttendanceForEmployee(int $employeeId, DailySummary $ds)
    {
        $response = $this->client->request('POST', 'company/attendances', [
            'json' => [
                'attendances' => [
                    [
                        'employee' => $employeeId,
                        'date' => $ds->getDate()->format('Y-m-d'),
                        'start_time' => $ds->getStartTime()->format('H:i'),
                        'end_time' => $ds->getEndTime()->format('H:i'),
                        'break' => (int) round($ds->getTimeBreakInS()/60,0),
                        'comment' => $ds->getDailySummary()
                    ]
                ]
            ],
        ]);
        return json_decode($response->getContent(false), true);
    }
}
