<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activity;

class ActivityController extends AbstractController
{
    /**
     * @Route("/activity", name="activity_create", methods={"POST"})
     */
    public function create(): JsonResponse
    {
        // Create activity
        $activity = new Activity();
        // $activity->setActivityType();
        // $activity->


        return new JsonResponse(['status' => 'ok']);
    }
}
