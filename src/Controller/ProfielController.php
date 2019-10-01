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
use App\Form\ProfielType;
use App\Entity\Profiel;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller used to manage current user.
 *
 * @Route("/profile")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class ProfielController extends AbstractController
{

    /**
     * @Route("/edit_profile_meta", methods={"GET", "POST"}, name="user_edit_meta")
     */
    public function edit_profile_meta(Request $request): Response
    {

        $profile = $this->getProfile();
        $form = $this->createForm(ProfielType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit_meta');
        }

        return $this->render('user/edit_meta.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
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

    /**
     * @Route("/change-password", methods={"GET", "POST"}, name="user_change_password")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $form->get('newPassword')->getData()));

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('security_logout');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
