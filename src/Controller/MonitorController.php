<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Monitor;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as ValidatorValidatorInterface;

class MonitorController extends AbstractController
{
    /**
     * @Route("/monitors", name="get_monitors", methods={"GET"})
     */
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $monitors = $entityManager->getRepository(Monitor::class)->findAll();

        // Return a JSON response with no data and 404 status code
        if (!$monitors) {
            return new JsonResponse(['status' => 'Monitors not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Convert the object into an associative array with the help of a foreach loop
        $monitorsAsArray = [];
        foreach ($monitors as $monitor) {
            $monitorsAsArray[] = [
                'id' => $monitor->getId(),
                'name' => $monitor->getName(),
                'email' => $monitor->getEmail(),
                'phone' => $monitor->getPhone(),
                'photo' => $monitor->getPhoto(),
            ];
        }

        // Return a JSON response with the found monitors and 200 status code
        return new JsonResponse($monitorsAsArray, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/monitors", name="create_monitor", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, ValidatorValidatorInterface $validator): JsonResponse
    {
        /* 
        JSON example value for the request body:
        {
            "id": 10,
            "name": "Miguel Goyena",
            "email": "miguel_goyena@cuatrovientos.org",
            "phone": "654767676",
            "photo": "http://foto.com/miguel.goyena"
        } */

        // Get the JSON string from the request body
        $logger->debug('Request: ' . $request->getContent());
        $json = json_decode($request->getContent()); // Returns an object with the data

        // Consult the database to check if the monitor already exists
        if (property_exists($json, 'name')) {
            $monitorInput = $entityManager->getRepository(Monitor::class)->findBy(['name' => $json->name]);
        } else {
            return new JsonResponse(['code' => 21, 'description' => 'The name is mandatory'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Return a JSON response with data and 409 status code if the monitor already exists
        if ($monitorInput) {
            return new JsonResponse(['code' => 22, 'description' => 'The monitor already exists'], JsonResponse::HTTP_CONFLICT);
        }

        // Create a new monitor object and set its data from the received JSON
        $monitor = new Monitor();
        $monitor->setName($json->name);
        $monitor->setEmail($json->email);
        $monitor->setPhone($json->phone);
        $monitor->setPhoto($json->photo);

        // Validate the new monitor object
        $errors = $validator->validate($monitor);

        if (count($errors) > 0) {
            $logger->error('Validation error: ' . $errors);
            return new JsonResponse(['code' => 23, 'description' => (string) $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Save the new monitor into the database
        $entityManager->persist($monitor);
        $entityManager->flush();

        // Convert the object into an associative array
        $monitorAsArray = [
            'id' => $monitor->getId(),
            'name' => $monitor->getName(),
            'email' => $monitor->getEmail(),
            'phone' => $monitor->getPhone(),
            'photo' => $monitor->getPhoto(),
        ];

        // Return a JSON response with the created monitor and 200 status code
        return new JsonResponse($monitorAsArray, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/monitors/{monitorId}", name="update_monitor", methods={"PUT"})
     */
    public function update(int $monitorId, Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, ValidatorValidatorInterface $validator): JsonResponse
    {
        /* 
        JSON example value for the request body:
        {
            "id": 10,
            "name": "Miguel Goyena",
            "email": "miguel_goyena@cuatrovientos.org",
            "phone": "654767676",
            "photo": "http://foto.com/miguel.goyena"
        } */
        // Get the JSON string from the request body
        $logger->debug('Request: ' . $request->getContent());
        $json = json_decode($request->getContent()); // Returns an object with the data

        // Consult the database to check if the monitor already exists
        $monitor = $entityManager->getRepository(Monitor::class)->findBy(['id' => $monitorId]);

        if (!$monitor) {
            return new JsonResponse(['code' => 20, 'description' => 'Monitor not found'], JsonResponse::HTTP_CONFLICT);
        }

        if (!property_exists($json, 'name')) {
            return new JsonResponse(['code' => 21, 'description' => 'The name is mandatory'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Update the monitor object with the received JSON data
        if (is_object($json)) {
            $monitor[0]->setName($json->name);
            $monitor[0]->setEmail($json->email);
            $monitor[0]->setPhone($json->phone);
            $monitor[0]->setPhoto($json->photo);
        } else {
            // Handle the case when $json is not an object
            return new JsonResponse(['code' => 24, 'description' => 'Invalid JSON data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate the new monitor object
        $errors = $validator->validate($monitor);

        if (count($errors) > 0) {
            $logger->error('Validation error: ' . $errors);
            return new JsonResponse(['code' => 23, 'description' => (string) $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Save the new monitor into the database
        $entityManager->flush();

        // Convert the object into an associative array
        $monitorAsArray = [
            'id' => $monitorId,
            'name' => $monitor[0]->getName(),
            'email' => $monitor[0]->getEmail(),
            'phone' => $monitor[0]->getPhone(),
            'photo' => $monitor[0]->getPhoto(),
        ];

        return new JsonResponse($monitorAsArray, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/monitors/{monitorId}", name="delete_monitor", methods={"DELETE"})
     */
    public function delete(int $monitorId, EntityManagerInterface $entityManager): JsonResponse
    {
        $monitor = $entityManager->getRepository(Monitor::class)->findBy(['id' => $monitorId]); // Returns an array of objects

        // Return a JSON response with data and 404 status code if the monitor does not exist
        if (!$monitor) {
            return new JsonResponse(['code' => 20, 'description' => 'Monitor not found'], JsonResponse::HTTP_CONFLICT);
        }

        // Remove the monitor from the database
        $entityManager->remove($monitor[0]);

        // Save the new monitor into the database
        $entityManager->flush();

        // Return a JSON response with no data and 200 status code
        return new JsonResponse(null, JsonResponse::HTTP_OK);
    }
}
