<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Plaats;

/**
 * Controller used to find places belonging to a province
 * Mostly used interactively by jquery in selectboxes
 *
 * @Route("/plaats")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class PlaatsController extends AbstractController
{

    /**
     * Returns a JSON string with the cities belonging to a province
     *
     * @param Request $request
     * @Route("/list", methods={"GET", "POST"}, name="plaats_list")
     * @return JsonResponse
     */
    public function listPlaatsenByProvincie(Request $request)
    {
        // Get Entity manager and repository
        $em = $this->getDoctrine()->getManager();
        $plaatsRepository = $em->getRepository(Plaats::class );

        // Search the places that belongs to the province with the given id as GET parameter "provincie"
        $plaatsen = $plaatsRepository->createQueryBuilder("q")
            ->where("q.provincie = :provincie")
            ->setParameter("provincie", $request->query->get("provincie"))
            ->getQuery()
            ->getResult();

        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($plaatsen as $plaats){
            $responseArray[] = array(
                "id" => $plaats->getId(),
                "name" => $plaats->getNaam()
            );
        }

        // Return array with structure of the plaats of the providen city id
        return new JsonResponse($responseArray);

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }
}
