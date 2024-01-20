<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActivityController extends AbstractController
{
    /**
     * @Route("/activities", name="get_activities", methods={"GET"})
     */
    public function getAll(EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $activities = $entityManager->getRepository(Activity::class)->findAll();

        // Validate the entity
        $errors = $validator->validate($activities);

        // Return a JSON response¡ with no data and 404 status code
        if (count($errors) > 0) {
            return new JsonResponse('Any problem in the Server', JsonResponse::HTTP_BAD_REQUEST);
        }

        // Convert the object into an associative array with the help of a foreach loop
        $activitiesAsArray = [];
        foreach ($activities as $activity) {
            $activitiesAsArray[] = [
                'id' => $activity->getId(),
                'activity_type' => $activity->getActivityType(),
                'monitors' => $activity->getMonitors(),
                'date_start' => $activity->getDateStart(),
                'date_end' => $activity->getDateEnd()
            ];
        }

        // Return a JSON response with the found activities and 200 status code
        return new JsonResponse($activitiesAsArray, JsonResponse::HTTP_OK);
    }
}
