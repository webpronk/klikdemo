<?php

namespace App\Entity;

use App\Repository\PicturesOldRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PicturesOldRepository::class)
 */
class PicturesOld
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $profielId;

    /**
     * @ORM\Column(type="integer")
     */
    private $nummer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $naam;

    /**
     * @ORM\Column(type="integer")
     */
    private $profielfoto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfiel(): ?int
    {
        return $this->profielId;
    }

    public function setProfiel(int $profiel): self
    {
        $this->profielId = $profiel;

        return $this;
    }

    public function getNummer(): ?int
    {
        return $this->nummer;
    }

    public function setNummer(int $nummer): self
    {
        $this->nummer = $nummer;

        return $this;
    }

    public function getNaam(): ?string
    {
        return $this->naam;
    }

    public function setNaam(string $naam): self
    {
        $this->naam = $naam;

        return $this;
    }

    public function getProfielfoto(): ?int
    {
        return $this->profielfoto;
    }

    public function setProfielfoto(int $profielfoto): self
    {
        $this->profielfoto = $profielfoto;

        return $this;
    }
}
