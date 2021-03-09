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

use App\Form\Type\ChangePasswordType;
use App\Form\UserType;
use App\Form\MetaType;
use App\Entity\Meta;
use App\Entity\Pictures;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Service\PictureHelper;

/**
 * Controller used to manage current user.
 *
 * @Route("/meta")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class MetaController extends AbstractController
{

    protected $em;

    /**
     * MetaController constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->em = $entityManager;
    }


    /**
     * @Route("/edit_profile", methods={"GET", "POST"}, name="user_edit_profile")
     */
    public function edit_meta_meta(Request $request): Response
    {

        $meta = $this->getMetaForUser();

        //$user = $this->getUser();
        $form = $this->createForm(MetaType::class, $meta);
        //$form = $this->createForm(MetaType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($meta);
            //$entityManager->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit_profile');
        }

        return $this->render('user/edit_meta.html.twig', [
            'meta' => $meta,
            //'meta' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user_show_profile", methods={"GET", "POST"}, name="user_show_profile")
     */
    public function user_show_profile(Request $request): Response
    {

        $meta = $this->getMetaForUser();
        $user = $this->getUser();

        $form = $this->createForm(MetaType::class, $meta);

        $PictureHelper = new PictureHelper($this->em, $user->getId());
        //$PictureHelper->setEntityManager($this->em);
        //$PictureHelper->setUploadPathFromUser($user->getId());

        $pictures = $PictureHelper->getPicturesWithoutMain();
        $mainPicture = $PictureHelper->getMainPicture();

        $form->handleRequest($request);

        return $this->render('user/show_profile.html.twig', [
            'mainPicture' => $mainPicture,
            'pictures' => $pictures,
            'meta' => $meta,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * @return object
     */
    protected function getMetaForUser()
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $meta = $this->getDoctrine()
            ->getRepository(Meta::class)
            ->findOneBy(['user' => $userId]);

        return $meta;
    }


}
