<?php

namespace App\Entity;

use App\Repository\RegistrationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Introduzca su nombre.')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Introduzca sus apellidos.')]
    private ?string $surname = null;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank(message: 'Introduzca su NIF o el número de su pasaporte.')]
    private ?string $idCard = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Introduzca su dirección de correo electrónico.')]
    #[Assert\Email(message: 'Hay un error en la dirección de correo electrónico introducida.')]
    private ?string $email = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank(message: 'Seleccione su nacionalidad en la lista desplegable.')]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $age = null;

    #[ORM\Column]
    private ?bool $member = null;

    #[ORM\Column]
    private ?bool $relative = null;

    #[ORM\Column(nullable: true)]
    private ?int $donation = null;

    #[ORM\Column]
    private ?bool $public = null;

    #[ORM\Column]
    #[Assert\IsTrue(message: 'Es necesario autorizar expresamente el tratamiento para formalizar el registro.')]
    private ?bool $consent = null;

    #[ORM\Column]
    private ?bool $paid = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getIdCard(): ?string
    {
        return $this->idCard;
    }

    public function setIdCard(string $idCard): self
    {
        $this->idCard = $idCard;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function isMember(): ?bool
    {
        return $this->member;
    }

    public function setMember(bool $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function isRelative(): ?bool
    {
        return $this->relative;
    }

    public function setRelative(bool $relative): self
    {
        $this->relative = $relative;

        return $this;
    }

    public function getDonation(): ?int
    {
        return $this->donation;
    }

    public function setDonation(?int $donation): self
    {
        $this->donation = $donation;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function hasConsent(): ?bool
    {
        return $this->consent;
    }

    public function setConsent(bool $consent): self
    {
        $this->consent = $consent;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
