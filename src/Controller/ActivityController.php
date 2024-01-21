<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activity;
use App\Entity\ActivityType;
use App\Entity\Monitor;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
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

        // Return a JSON response with no data and 404 status code
        if (count($errors) > 0) {
            return new JsonResponse('Any problem in the Server', JsonResponse::HTTP_BAD_REQUEST);
        }

        // Convert the object into an associative array with the help of a foreach loop
        $activitiesAsArray = [];
        foreach ($activities as $activity) {
            $activityType = $activity->getActivityType();
            // Convert the object into an associative array
            $activityType = [
                'id' => $activityType->getId(),
                'name' => $activityType->getName(),
                'number_monitors' => $activityType->getNumberMonitors()
            ];

            // Convert the object into an associative array
            $monitors = [];
            foreach ($activity->getMonitors() as $monitor) {
                $monitors[] = [
                    'id' => $monitor->getId(),
                    'name' => $monitor->getName(),
                    'email' => $monitor->getEmail(),
                    'phone' => $monitor->getPhone(),
                    'photo' => $monitor->getPhoto()
                ];
            }

            // Convert the date format example: 2024-01-20T23:36:33.297Z
            $dateStart = $activity->getDateStart()->format('Y-m-d\TH:i:s.v\Z');
            $dateEnd = $activity->getDateEnd()->format('Y-m-d\TH:i:s.v\Z');

            $activitiesAsArray[] = [
                'id' => $activity->getId(),
                'activity_type' => $activityType,
                'monitors' => $monitors,
                'date_start' => $dateStart,
                'date_end' => $dateEnd
            ];
        }

        // Return a JSON response with the found activities and 200 status code
        return new JsonResponse($activitiesAsArray, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/activities", name="deleteAll_activities", methods={"DELETE"})
     */
    /* public function deleteAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $activities = $entityManager->getRepository(Activity::class)->findAll();

        // Return a JSON response with no data and 404 status code
        if (!$activities) {
            return new JsonResponse(['status' => 'Activities not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Delete all activities
        foreach ($activities as $activity) {
            $entityManager->remove($activity);
        }
        $entityManager->flush();

        // Return a JSON response with the found activities and 200 status code
        return new JsonResponse(['status' => 'All activities deleted'], JsonResponse::HTTP_OK);
    } */

    /**
     * @Route("/activities", name="create_activities", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $logger): JsonResponse
    {
        /* 
        JSON example value for the request body:
        {
            "id": 10,
            "activity_type_id": 2,
            "monitors_id": [
                1,2
            ],
            "date_start": "2024-01-20T23:36:33.297Z",
            "date_end": "2024-01-20T23:36:33.297Z"
        } */

        $logger->debug('Request: ' . $request->getContent());
        $json = json_decode($request->getContent());

        // Validate the entity
        $errors = $validator->validate($json);
        if (count($errors) > 0) {
            $logger->error('Validation error: ' . $errors);
            return new JsonResponse(['code' => 19, 'description' => 'Any Error like validations'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate the required fields
        $requiredFields = ['activity_type_id', 'monitors_id', 'date_start', 'date_end'];
        foreach ($requiredFields as $field) {
            if (!property_exists($json, $field)) {
                $logger->error("Missing required field: $field");
                return new JsonResponse(['code' => 20, 'description' => "The $field is mandatory"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        // Validate the date format example: 2024-01-20T23:36:33.297Z
        $dateStart = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($json->date_start, 0, 19));
        $dateEnd = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($json->date_end, 0, 19));

        if (!$dateStart || !$dateEnd || !$this->isValidStartDate($dateStart) || !$this->isValidStartDate($dateEnd)) {
            $logger->error('Invalid date format');
            return new JsonResponse(['code' => 23, 'description' => 'The date format is not valid'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Check if the activity already exists
        $activityInput = $entityManager->getRepository(Activity::class)->findBy(['activity_type' => $json->activity_type_id, 'date_start' => $dateStart]);
        if ($activityInput) {
            $logger->error('Activity already exists');
            return new JsonResponse(['code' => 22, 'description' => 'The activity already exists'], JsonResponse::HTTP_CONFLICT);
        }

        // Create activity objects
        $activityType = $entityManager->getRepository(ActivityType::class)->find($json->activity_type_id);

        if (!$activityType) {
            $logger->error('Activity type does not exist');
            return new JsonResponse(['code' => 23, 'description' => 'The activity type does not exist'], JsonResponse::HTTP_CONFLICT);
        }

        $activity = new Activity();
        $activity->setActivityType($activityType);
        foreach ($json->monitors_id as $monitorId) {
            $monitor = $entityManager->getRepository(Monitor::class)->find($monitorId);
            if (!$monitor) {
                $logger->error('Monitor does not exist: ' . $monitorId);
                return new JsonResponse(['code' => 23, 'description' => 'The monitor with id ' . $monitorId . ' does not exist'], JsonResponse::HTTP_CONFLICT);
            }
            $activity->addMonitor($monitor);
        }
        $activity->setDateStart($dateStart);
        $activity->setDateEnd($dateEnd);

        // Validate the new activity object
        $errors = $validator->validate($activity);
        if (count($errors) > 0) {
            $logger->error('Validation error: ' . $errors);
            return new JsonResponse(['code' => JsonResponse::HTTP_BAD_REQUEST, 'description' => 'Any Error like validations'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Save the new activity into the database
        $entityManager->persist($activity);
        $entityManager->flush();

        // Convert the object into an associative array
        $activityType = [
            'id' => $activityType->getId(),
            'name' => $activityType->getName(),
            'number_monitors' => $activityType->getNumberMonitors()
        ];

        // Convert the object into an associative array
        $monitors = [];
        foreach ($activity->getMonitors() as $monitor) {
            $monitors[] = [
                'id' => $monitor->getId(),
                'name' => $monitor->getName(),
                'email' => $monitor->getEmail(),
                'phone' => $monitor->getPhone(),
                'photo' => $monitor->getPhoto()
            ];
        }

        // Convert the date format example: 2024-01-20T23:36:33.297Z
        $dateStart = $dateStart->format('Y-m-d\TH:i:s.v\Z');
        $dateEnd = $dateEnd->format('Y-m-d\TH:i:s.v\Z');

        // Convert the object into an associative array
        $activityAsArray = [
            'id' => $activity->getId(),
            'activity_type' => $activityType,
            'monitors' => $monitors,
            'date_start' => $dateStart,
            'date_end' => $dateEnd
        ];

        // Return a JSON response with the created activity and 200 status code
        return new JsonResponse($activityAsArray, JsonResponse::HTTP_OK);
    }

    // Validate the date format example: 2024-01-20T23:36:33.297Z
    public function isValidStartDate(\DateTime $date): bool
    {
        // Validate the date format example: 2024-01-20T23:36:33.297Z
        $dateString = $date->format('Y-m-d\TH:i:s');
        $dateFormatted = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($dateString, 0, 19));

        if (!$dateFormatted) {
            return false;
        }
        return true;
    }
}
