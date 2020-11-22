<?php

namespace App\Controller;

use App\Entity\User;
use App\HrTools\Personio\Gateway;
use App\Repository\DailySummaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function index(Gateway $personioGateway, DailySummaryRepository $repo)
    {

//        try {
//            $response = $personioGateway->getEmployees();
//        } catch (\Exception $e) {
//            dd($e);
//        }

//        try {
//            $response = $personioGateway->getAttendanceForEmployee(
//                [2269559],
//                (new \DateTime('19.09.2020')),
//                (new \DateTime('19.09.2020'))
//            );
//        } catch (\Exception $e) {
//            dd($e);
//        }

//        $dailySummaryEntity = $repo->findOneBy(['date' => new \DateTime('2020-09-18')]);
//        try {
//            $response = $personioGateway->postAttendanceForEmployee(2269559, $dailySummaryEntity);
//        } catch(\Exception $e) {
//            dd($e);
//        }
//
//        dd($response);
    }

    /**
     * @Route("/user", name="user")
     */
    public function generateUser(EntityManagerInterface $em)
    {
        $user = new User();
        $user->setFirstName('Norman');
        $user->setSlackUserId('wrsdf');
        $user->setEmail('ndfs@sadf.cde');
        $user->setTimezone('europ');
        $user->setTzOffset(200);
        $em->persist($user);
        $em->flush();
        return new \Symfony\Component\HttpFoundation\Response(200);
    }
}
