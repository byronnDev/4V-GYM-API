<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Monitor;

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
}
