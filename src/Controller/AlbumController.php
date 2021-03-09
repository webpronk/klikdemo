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
use App\Entity\Meta;
use App\Form\MetaType;
use App\Entity\Pictures;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Service\PictureHelper;

/**
 * Controller used for photo album. Most logic is used in twig tmeplate.
 *
 * @Route("/meta")
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class AlbumController extends AbstractController
{

    /**
     * Just load the template, ajax will load all pictures
     *
     * @Route("/edit_meta_album", methods={"GET", "POST"}, name="user_edit_album")
     */
    public function edit_meta_album(): Response
    {
        return $this->render('user/edit_album.html.twig', [

        ]);
    }

}
