<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Messages;
use App\Entity\Meta;
use App\Entity\User;
use App\Entity\Picture;
use App\Events;
use App\Form\CommentType;
use App\Repository\ProvincieRepository;
use App\Repository\UserRepository;
use App\Repository\MetaRepository;
use App\Service\PictureHelper;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ProfileReadyHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/singles")
 *
 * @IsGranted("ROLE_USER")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class SinglesListController extends RedirectController
{

    /**
     * Message Controller constructor.
     * @IsGranted("ROLE_USER")
     */
    public function __construct(EntityManagerInterface $entityManager, MetaRepository $metas)
    {
        $this->em = $entityManager;
        $this->metas = $metas;
    }

    /**
     * @Route("/list", defaults={"page": "1", "_format"="html"}, methods={"GET"}, name="singles_index")
     * @Route("/list/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods={"GET"}, name="singles_index_paginated")
     * @Cache(smaxage="10")
     *
     * NOTE: For standard formats, Symfony will also automatically choose the best
     * Content-Type header for the response.
     * See https://symfony.com/doc/current/quick_tour/the_controller.html#using-formats
     */
    public function index(Request $request, int $page, ProvincieRepository $ProvinceRepository, ProfileReadyHelper $profileReadyHelper): Response
    {

        $redirect = parent::switchRoute($profileReadyHelper);
        if(!empty($redirect && array_key_exists('route', $redirect))) {
            return $this->redirectToRoute( $redirect['route'] );
        }


        $provinceList = $ProvinceRepository->findAll();
        $user = $this->getUser();
        $opzoek = '';
        if($user) {
            $opzoek = $this->getUser()->getMeta()->getOpzoek();

        }

        $provId = $request->query->get('q', '');
        $singlesList = $this->metas->getSingles($opzoek, $provId);

        return $this->render('singles/singles_list.html.twig', ['singles' => $singlesList, 'provinces' => $provinceList]);
    }


    /**
     * @Route("/search", methods={"GET"}, name="singles_search")
     */
    public function search(Request $request, MetaRepository $metas): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render('blog/search.html.twig');
        }

        $provId = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);
        $foundsingles = $metas->findBySearchQuery($provId, 'V', $limit);

        $results = [];
        foreach ($foundsingles as $single) {

            $results[] = [
                'single_user_id' => $single->getUser()->getId(),
                'single_path' => '/singles/show/',
                'mainpicture' => $single->getUser()->getMainPicture(),
                'singlebirthdate' => $single->getUser()->getGeboortedatum(),
            ];
        }

        return $this->json($results);
    }
}
