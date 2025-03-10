<?php

namespace App\Domain\Model;

/**
 *  Hotel reservation Class
 */
class Reservation
{
    public function __construct(
        private string $locator,
        private string $guest,
        private \DateTime $checkInDate,
        private \DateTime $checkOutDate,
        private string $hotel,
        private ?float $price,
        private string $possibleActions
    ) {
    }

    /**
     * @return string
     */
    public function getLocator(): string
    {
        return $this->locator;
    }

    /**
     * @return string
     */
    public function getGuest(): string
    {
        return $this->guest;
    }

    /**
     * @return \DateTime
     */
    public function getCheckInDate(): \DateTime
    {
        return $this->checkInDate;
    }

    /**
     * @return \DateTime
     */
    public function getCheckOutDate(): \DateTime
    {
        return $this->checkOutDate;
    }

    /**
     * @return string
     */
    public function getHotel(): string
    {
        return $this->hotel;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPossibleActions(): string
    {
        return $this->possibleActions;
    }
    
    /**
     * Turns reservation into an array for serialization
     */
    public function toArray(): array
    {
        return [
            'locator' => $this->locator,
            'guest' => $this->guest,
            'check_in_date' => $this->checkInDate->format('Y-m-d'),
            'check_out_date' => $this->checkOutDate->format('Y-m-d'),
            'hotel' => $this->hotel,
            'price' => $this->price,
            'possible_actions' => $this->possibleActions
        ];
    }
    
    /**
     * Verifies if reservation contains the search chain in some field
     */
    public function matchesSearchTerm(string $searchTerm): bool
    {
        if (empty($searchTerm)) {
            return true;
        }
        
        $searchTerm = strtolower($searchTerm);
        
        return str_contains(strtolower($this->locator), $searchTerm) ||
               str_contains(strtolower($this->guest), $searchTerm) ||
               str_contains(strtolower($this->checkInDate->format('Y-m-d')), $searchTerm) ||
               str_contains(strtolower($this->checkOutDate->format('Y-m-d')), $searchTerm) ||
               str_contains(strtolower($this->hotel), $searchTerm) ||
               ($this->price !== null && str_contains((string)$this->price, $searchTerm)) ||
               str_contains(strtolower($this->possibleActions), $searchTerm);
    }

    /**
     * Converts the reservation into a CSV line
     */
    public function toCsv(): string
    {
        return implode(',', [
            $this->locator,
            $this->guest,
            $this->checkInDate->format('Y-m-d'),
            $this->checkOutDate->format('Y-m-d'),
            $this->hotel,
            $this->price !== null ? (string)$this->price : '',
            $this->possibleActions
        ]);
    }
}
