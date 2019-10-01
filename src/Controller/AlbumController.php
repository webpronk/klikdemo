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

use App\Form\AlbumFormType;
use App\Entity\Profiel;
use App\Entity\Pictures;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Controller used to manage current user.
 *
 * @Route("/profile")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class AlbumController extends AbstractController
{

    /**
     * @Route("/edit_profile_album", methods={"GET", "POST"}, name="user_edit_album")
     */
    public function edit_profile_album(Request $request): Response
    {

        $profile = $this->getProfile();
        $pictures = new Pictures();
        $form = $this->createForm(AlbumFormType::class, $pictures);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pictures);
            $entityManager->flush();

            $someNewFilename = uniqid('klik2matchpic', true).".jpg";

            $directory = "/public/pictures/";

            $file = $form['naam']->getData();
            $file->move($directory, $someNewFilename);

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit_album');
        }

        return $this->render('user/edit_album.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    public function upload(Request $request): Response
    {
        $profile = $this->getProfile();
        $form = $this->createForm(ProfielType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $someNewFilename = uniqid('klik2matchpic', true).".jpg";

            $directory = "/public/pictures/";

            $file = $form['attachment']->getData();
            $file->move($directory, $someNewFilename);

        // ...
        }

        // ...
    }

    /**
     *
     * @return object
     */
    protected function getProfile()
    {
        $user = $this->getUser();
        $profielId = $user->getId();

        $profile = $this->getDoctrine()
            ->getRepository(Profiel::class)
            ->findOneBy(['user' => $profielId]);

        return $profile;
    }


}
