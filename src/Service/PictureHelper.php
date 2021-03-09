<?php

namespace App\Service;
use App\Form\UserType;
use App\Entity\Pictures;
use App\Repository\PicturesRepository;
use Doctrine\ORM\EntityManagerInterface;


/**
 * It helps reading and showing pictures from the database
 *
 *
 * @author Bart Pronk <b.pronk3@gmail.com>
 */
class PictureHelper
{
    private $userId;
    private $em;
    public $upload_path;


    public function __construct($em, $userId)
    {
        $this->em = $em;
        $this->userId = $userId;
        $this->upload_path = Pictures::SERVER_PATH_TO_IMAGE_FOLDER.$this->userId.'/';
    }


    /**
     * @return array
     */
    public function getPicturesAll()
    {
        $pictures = $this->em->getRepository(Pictures::class)->getPicturesAllFromDB($this->userId);

        $uploads_array = $this->setPictureMetaData($pictures);

        return $uploads_array;
    }

    /**
     * @return array
     */
    public function getPicturesWithoutMain()
    {
        //$picturesRepo = new PicturesRepository();
        $pictures = $this->em->getRepository(Pictures::class)->getPicturesWithoutMainFromDB($this->userId);
        $uploads_array = $this->setPictureMetaData($pictures);

        return $uploads_array;
    }

    /**
     * @return bool|mixed
     */
    public function getMainPicture()
    {
        // If no pictures present at all, just return false
        //$allPictures = $this->getPicturesAllFromDB();
        $allPictures = $this->em->getRepository(Pictures::class)->getPicturesAllFromDB($this->userId);
        if(empty($allPictures)) {
            return false;
        }

        $mainPicture = $this->em->getRepository(Pictures::class)->getMainPictureFromDB($this->userId);

        // If for some reason there is no main picture, we will create one from the first
        if(empty($mainPicture)) {
            $topPicture = $allPictures[0];
            $topPicture->setMainFoto(1);
            $this->em->persist($topPicture);
            $this->em->flush();
            $mainPicture = $this->em->getRepository(Pictures::class)->getMainPictureFromDB($this->userId);
        }

        $uploads_array = $this->setPictureMetaData($mainPicture);
        $return = sizeof($uploads_array) > 0 ? $uploads_array[0] : false;

        return $return;
    }


    /**
     * @param $pictures
     *
     * @return array
     */
    private function setPictureMetaData($pictures)
    {
        $uploads_array = [];
        foreach($pictures as $picture)
        {
            $file = new \stdClass();

            $file->id = $picture->getId() ;
            $file->name = $picture->getNaam();
            $file->size = 100;
            $file->mainfoto = $picture->getMainFoto();

            $file->status = 1;

            $file->url = $this->upload_path.$picture->getNaam();
            $file->thumbnailUrl = $this->upload_path.'thumbnail/'.$picture->getNaam();
            $file->mediumnailUrl = $this->upload_path.'mediumnail/'.$picture->getNaam();
            $file->bignailUrl = $this->upload_path.'bignail/'.$picture->getNaam();
            $file->deleteUrl = "/nl/album/delete?file=".$picture->getNaam();
            $file->mainimageUrl = "/nl/album/mainimage?file=".$picture->getNaam();
            $file->deleteType = "DELETE";
            array_push($uploads_array, $file);
        }

        return $uploads_array;
    }

}