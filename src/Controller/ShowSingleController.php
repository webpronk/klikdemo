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
use App\Repository\MetaRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
//use Symfony\Bridge\Twig;

use App\Service\PictureHelper;

/**
 * Controller used to manage current user.
 *
 * @Route("/single")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class ShowSingleController extends AbstractController
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
     * @Route("", methods={"GET", "POST"}, name="single_show")
     */
    public function single_show(UserRepository $userRepo, Request $request): Response
    {
        $userEntity = $userRepo->findOneBy(['id' => $request->query->get('user')]);
        $metaEntity = $userEntity->getMeta();

        $form = $this->createForm(MetaType::class, $metaEntity);

        $PictureHelper = new PictureHelper($this->em, $userEntity->getId());
        $pictures = $PictureHelper->getPicturesWithoutMain();
        $mainPicture = $PictureHelper->getMainPicture();

        $favorites = $this->getUser()->getFavorites();
        $favo_button_disabled = false;
        foreach ($favorites as $favorite)
        {
            if($userEntity->getId() == $favorite->getReceiver()->getId()) {
                $favo_button_disabled = true;
            }
        }

        $blockeds = $this->getUser()->getBlockeds();
        $block_button_disabled = false;
        foreach ($blockeds as $block)
        {
            if($userEntity->getId() == $block->getReceiver()->getId()) {
                $block_button_disabled = true;
            }
        }

        $form->handleRequest($request);

        // In config/packages/dev/swiftmailer.yaml the 'disable_delivery' option is set to 'true'.
        // That's why in the development environment you won't actually receive any email.
        // However, you can inspect the contents of those unsent emails using the debug toolbar.
        // See https://symfony.com/doc/current/email/dev_environment.html#viewing-from-the-web-debug-toolbar


        return $this->render('singles/show_single.html.twig', [
            'mainPicture' => $mainPicture,
            'pictures' => $pictures,
            'favo_button_disabled' => $favo_button_disabled,
            'block_button_disabled' => $block_button_disabled,
            'meta' => $metaEntity,
            'user' => $userEntity,
            'form' => $form->createView(),
        ]);
    }


}
