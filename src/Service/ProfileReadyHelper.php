<?php

namespace App\Service;
//use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use App\Entity\Meta;
use App\Repository\MetaRepository;
use Symfony\Component\Security\Core\Security;


/**
 * Service to see if user has finished profile.
 *
 * @IsGranted("ROLE_USER")
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class ProfileReadyHelper
{
    private $security;
    private $em;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     *
     */
    public function readyCheck($em)
    {
        $user = $this->security->getUser();
        $metaRepo = $em->getRepository(Meta::class);
        $meta = $metaRepo->findOneBy(['user' => $user]);
        // Just check a few fields, the html validator does the job anyway
        if(
            empty($meta->getPlaats()) ||
            empty($meta->getOpzoek()) ||
            empty($meta->getKinderen()) ||
            empty($meta->getKinderwens()) ||
            empty($meta->getRoken()) ||
            empty($meta->getDrinken()) ||
            empty($meta->getDrugs()) ||
            empty($meta->getLengte())
        )
        {
            return 'profileNotReady';
        }

        $pictures = $user->getPictures();
        if(count($pictures) == 0) {
            return 'albumNotReady';
        }

        // All okay
        return 'ready';
    }
}