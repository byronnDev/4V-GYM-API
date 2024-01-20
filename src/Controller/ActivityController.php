<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activity;

class ActivityController extends AbstractController
{
    /**
     * @Route("/activities", name="get_activities", methods={"GET"})
     */
    public function getAll(): JsonResponse
    {

        return new JsonResponse(['status' => 'ok']);
    }
}
