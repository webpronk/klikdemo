<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Service\PictureHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
//use App\Entity\Pictures;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 *
 * Defines the properties of the User entity to represent the application users.
 * See https://symfony.com/doc/current/book/doctrine.html#creating-an-entity-class
 *
 * Tip: if you have an existing database, you can generate these entity class automatically.
 * See https://symfony.com/doc/current/cookbook/doctrine/reverse_engineering.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */

/*
* @UniqueEntity(fields={"email11"}, groups={"registration"})
* @UniqueEntity(fields={"username222"}, groups={"registration"})
*/
class User implements UserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=false)
     */
    private $male_female;


    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=50)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $password;



    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Land")
     * @ORM\JoinColumn(nullable=false)
     */
    private $land;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pictures", mappedBy="user", orphanRemoval=true)
     */
    private $pictures;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="sender")
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="receiver")
     */
    private $recipients;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $geboortedatum;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Meta", mappedBy="user", cascade={"persist", "remove"})
     */
    private $meta;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Favorites", mappedBy="sender", orphanRemoval=true)
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Blocked", mappedBy="sender", orphanRemoval=true)
     */
    private $blockeds;

    const NUM_ITEMS = 10;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->recipient_id = new ArrayCollection();
        $this->messagerecipients = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->blockeds = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $geslacht
     */
    public function setMalefemale(string $male_female): void
    {
        $this->male_female = $male_female;
        //$this->male_female = 'V';
    }

    public function getMaleFemale(): ?string
    {
        return $this->male_female;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getLand(): ?land
    {
        return $this->land;
    }

    public function setLand(?land $land): self
    {
        $this->land = $land;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /*public function setPlainPassword(string $password): void
    {
        $this->password = $password;
    }*/

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return Collection|Pictures[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function getMainPicture()
    {

        foreach ($this->pictures as $picture)
        {
            if ($picture->getMainFoto() == 1) {

                return $picture;
            } else {
                return false;
            }
        }
    }

    public function addPicture(Pictures $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setUser($this);
        }

        return $this;
    }

    public function removePicture(Pictures $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // set the owning side to null (unless already changed)
            if ($picture->getUser() === $this) {
                $picture->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Messages $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setRecipient($this);
        }

        return $this;
    }

    public function removeRecipient(Messages $recipient): self
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
            // set the owning side to null (unless already changed)
            if ($recipient->getRecipient() === $this) {
                $recipient->setRecipient(null);
            }
        }

        return $this;
    }

    public function getGeboortedatum(): ?\DateTimeInterface
    {
        return $this->geboortedatum;
    }

    public function setGeboortedatum(?\DateTimeInterface $geboortedatum): self
    {
        $this->geboortedatum = $geboortedatum;

        return $this;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function setMeta(Meta $meta): self
    {
        $this->meta = $meta;

        // set the owning side of the relation if necessary
        if ($this !== $meta->getUser()) {
            $meta->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Favorites[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorites $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites[] = $favorite;
            $favorite->setSender($this);
        }

        return $this;
    }

    public function removeFavorite(Favorites $favorite): self
    {
        if ($this->favorites->contains($favorite)) {
            $this->favorites->removeElement($favorite);
            // set the owning side to null (unless already changed)
            if ($favorite->getSender() === $this) {
                $favorite->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Blocked[]
     */
    public function getBlockeds(): Collection
    {
        return $this->blockeds;
    }

    public function addBlocked(Blocked $blocked): self
    {
        if (!$this->blockeds->contains($blocked)) {
            $this->blockeds[] = $blocked;
            $blocked->setSender($this);
        }

        return $this;
    }

    public function removeBlocked(Blocked $blocked): self
    {
        if ($this->blockeds->contains($blocked)) {
            $this->blockeds->removeElement($blocked);
            // set the owning side to null (unless already changed)
            if ($blocked->getSender() === $this) {
                $blocked->setSender(null);
            }
        }

        return $this;
    }




}
