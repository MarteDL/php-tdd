<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 */
class Room
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $onlyForPremiumMembers;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="room")
     */
    private $bookings;

    public function __construct(bool $onlyForPremiumMembers)
    {
        $this->bookings = new ArrayCollection();
        $this->onlyForPremiumMembers = $onlyForPremiumMembers;
    }

    function canBook(User $user): bool
    {
        return ($this->onlyForPremiumMembers && $user->getPremiumMember()) || !$this->onlyForPremiumMembers;
    }

    public function isAvailableForBooking(Booking $reservation): bool
    {
        foreach ($this->bookings as $booking){

            if ($booking->getStartDate() <= $reservation->getStartDate() &&
                $reservation->getStartDate() <
                $booking->getEndDate()){
                return false;
            }

            if ($booking->getStartDate() < $reservation->getEndDate() &&
                    $reservation->getEndDate() <=
                    $booking->getEndDate()){
                return false;
            }
        }

        return true;
    }

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

    public function getOnlyForPremiumMembers(): ?bool
    {
        return $this->onlyForPremiumMembers;
    }

    public function setOnlyForPremiumMembers(bool $onlyForPremiumMembers): self
    {
        $this->onlyForPremiumMembers = $onlyForPremiumMembers;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setRoom($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getRoom() === $this) {
                $booking->setRoom(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
