<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ActivityType;
use Doctrine\ORM\EntityManagerInterface;

class ActivityTypeController extends AbstractController
{
    /**
     * @Route("/activity-types", name="get_activity_types", methods={"GET"})
     */
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $activityTypes = $entityManager->getRepository(ActivityType::class)->findAll();

        // Return a JSON response with no data and 404 status code
        if (!$activityTypes) {
            return new JsonResponse(['status' => 'Activity types not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Convert the object into an associative array with the help of a foreach loop
        $activityTypesAsArray = [];
        foreach ($activityTypes as $activityType) {
            $activityTypesAsArray[] = [
                'id' => $activityType->getId(),
                'name' => $activityType->getName(),
                'numberMonitors' => $activityType->getNumberMonitors(),
            ];
        }

        // Return a JSON response with the found activity types and 200 status code
        return new JsonResponse($activityTypesAsArray, JsonResponse::HTTP_OK);
    }
}
