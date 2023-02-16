<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MetaRepository")
 * @ORM\Table(name="meta")
 */
class Meta
{

    /**
     * Use constants to define configuration options that rarely change instead
     * of specifying them under parameters section in config/services.yaml file.
     *
     * See https://symfony.com/doc/current/best_practices/configuration.html#constants-vs-configuration-options
     */
    public const NUM_ITEMS = 10;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Provincie", inversedBy="metas")
     * @ORM\JoinColumn(nullable=true)
     */
    private $provincie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Plaats", inversedBy="metas")
     * @ORM\JoinColumn(nullable=true)
     */
    private $plaats;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerStatus", inversedBy="metas")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=55, nullable=false)
     *
     */
    private $opzoek;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $kinderwens;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $kinderen;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $roken;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $drugs;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $drinken;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $vegetarisch;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lengte;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerAfkomst", inversedBy="metas")
     */
    private $afkomst;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerBouw", inversedBy="metas")
     */
    private $Bouw;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerSport", inversedBy="metas")
     */
    private $sport;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerHaarkleur", inversedBy="metas")
     */
    private $haarkleur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerOpleiding", inversedBy="metas")
     */
    private $opleiding;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BeheerReligie", inversedBy="metas")
     */
    private $religie;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="meta", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;



    public function __construct()
    {

        $this->pictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

   
    public function getProvincie(): ?provincie
    {
        return $this->provincie;
    }

    public function setProvincie(?provincie $provincie): self
    {
        $this->provincie = $provincie;

        return $this;
    }

    public function getPlaats(): ?plaats
    {
        return $this->plaats;
    }

    public function setPlaats(?plaats $plaats): self
    {
        $this->plaats = $plaats;

        return $this;
    }

    public function getStatus(): ?BeheerStatus
    {
        return $this->status;
    }

    public function setStatus(?BeheerStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOpzoek(): ?string
    {
        return $this->opzoek;
    }

    public function setOpzoek(?string $opzoek): self
    {
        $this->opzoek = $opzoek;

        return $this;
    }

    public function getKinderwens(): ?string
    {
        return $this->kinderwens;
    }

    public function setKinderwens(?string $kinderwens): self
    {
        $this->kinderwens = $kinderwens;

        return $this;
    }

    public function getKinderen(): ?string
    {
        return $this->kinderen;
    }

    public function setKinderen(?string $kinderen): self
    {
        $this->kinderen = $kinderen;

        return $this;
    }

    public function getRoken(): ?string
    {
        return $this->roken;
    }

    public function setRoken(?string $roken): self
    {
        $this->roken = $roken;

        return $this;
    }

    public function getDrugs(): ?string
    {
        return $this->drugs;
    }

    public function setDrugs(?string $drugs): self
    {
        $this->drugs = $drugs;

        return $this;
    }

    public function getDrinken(): ?string
    {
        return $this->drinken;
    }

    public function setDrinken(?string $drinken): self
    {
        $this->drinken = $drinken;

        return $this;
    }

    public function getVegetarisch(): ?string
    {
        return $this->vegetarisch;
    }

    public function setVegetarisch(?string $vegetarisch): self
    {
        $this->vegetarisch = $vegetarisch;

        return $this;
    }

    public function getLengte(): ?string
    {
        return $this->lengte;
    }

    public function setLengte(?string $lengte): self
    {
        $this->lengte = $lengte;

        return $this;
    }

    public function getAfkomst(): ?BeheerAfkomst
    {
        return $this->afkomst;
    }

    public function setAfkomst(?BeheerAfkomst $afkomst): self
    {
        $this->afkomst = $afkomst;

        return $this;
    }

    public function getBouw(): ?BeheerBouw
    {
        return $this->Bouw;
    }

    public function setBouw(?BeheerBouw $Bouw): self
    {
        $this->Bouw = $Bouw;

        return $this;
    }

    public function getSport(): ?BeheerSport
    {
        return $this->sport;
    }

    public function setSport(?BeheerSport $sport): self
    {
        $this->sport = $sport;

        return $this;
    }

    public function getHaarkleur(): ?BeheerHaarkleur
    {
        return $this->haarkleur;
    }

    public function setHaarkleur(?BeheerHaarkleur $haarkleur): self
    {
        $this->haarkleur = $haarkleur;

        return $this;
    }

    public function getOpleiding(): ?BeheerOpleiding
    {
        return $this->opleiding;
    }

    public function setOpleiding(?BeheerOpleiding $opleiding): self
    {
        $this->opleiding = $opleiding;

        return $this;
    }

    public function getReligie(): ?BeheerReligie
    {
        return $this->religie;
    }

    public function setReligie(?BeheerReligie $religie): self
    {
        $this->religie = $religie;

        return $this;
    }






}
