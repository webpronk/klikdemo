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

use App\Form\Type\ResetPasswordType;
use App\Repository\UserRepository;
use App\Repository\UserPasswordResetRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * Controller used to manage the application security.
 * See https://symfony.com/doc/current/cookbook/security/form_login_setup.html.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class SecurityController extends AbstractController
{
    /**
     *
     * Ugly way to reroute the nake / url, it works but not sure if it is best practice
     * @Route("/", name="homepage_redirect")
     */
    public function redirectToLogin(): RedirectResponse
    {
        return $this->redirectToRoute('security_login');
    }


    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $helper): Response
    {
        return $this->render('security/login-clean.html.twig', [
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/resetpassword", name="reset_password")
     */
    public function resetPassword(UserPasswordResetRepository $resetRepo, UserRepository $userRepo,Request $request): Response
    {
        //$user = $this->getUser();

        $string = $request->query->get('string');
        $resetPasswordEntity = $resetRepo->findOneBy(['reset_token' => $request->query->get('string')]);
        //$resetPassword = new \App\Entity\UserPasswordReset();
        //$resetPassword->setResetToken('oijiejd22jjd');
        $userId = $resetPasswordEntity->getUserId();
        $userEntity = $userRepo->findOneBy(['id' => $userId]);
        $userName = $userEntity->getUserName();
        //var_dump($userEntity->getUserName());
        //exit;

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$user->setPassword($encoder->encodePassword($user, $form->get('newPassword')->getData()));

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('security_logout');
        }

        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
            'username' => $userName,
        ]);
    }


    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     *
     * @Route("/logout", name="security_logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
