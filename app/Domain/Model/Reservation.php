<?php

namespace App\Domain\Model;

/**
 * Hotel reservation class
 */
class Reservation
{
    private array $validationErrors = [];

    public function __construct(
        private string $locator,
        private string $guest,
        private \DateTime $checkInDate,
        private \DateTime $checkOutDate,
        private string $hotel,
        private ?float $price,
        private string $possibleActions
    ) {
        $this->validate();
    }

    /**
     * Validates the reservation data and stores errors if any.
     */
    private function validate(): void
    {
        $this->validationErrors = [];

        // Validar campos obligatorios
        if (empty(trim($this->locator))) {
            $this->validationErrors[] = 'El localizador no puede estar vacío';
        }
        if (empty(trim($this->guest))) {
            $this->validationErrors[] = 'El nombre del huésped no puede estar vacío';
        }
        if (empty(trim($this->hotel))) {
            $this->validationErrors[] = 'El nombre del hotel no puede estar vacío';
        }
        if (empty(trim($this->possibleActions))) {
            $this->validationErrors[] = 'Las acciones posibles no pueden estar vacías';
        }
        if ($this->price === null) {
            $this->validationErrors[] = 'Falta el precio';
        }

        if ($this->checkOutDate < $this->checkInDate) {
            $this->validationErrors[] = 'La fecha de salida debe ser igual o posterior a la fecha de entrada';
        }

        if ($this->price !== null && $this->price < 0) {
            $this->validationErrors[] = 'El precio no puede ser negativo';
        }
        if ($this->price === null && str_contains(strtolower($this->possibleActions), 'charge')) {
            $this->validationErrors[] = 'El precio es obligatorio para reservas cobrables';
        }
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
        return clone $this->checkInDate;
    }

    /**
     * @return \DateTime
     */
    public function getCheckOutDate(): \DateTime
    {
        return clone $this->checkOutDate;
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
     *
     * @return array
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
     *
     * @param string $searchTerm The term to search for
     * @return bool
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
     *
     * @return string
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

    /**
     * Checks if the reservation is valid according to business rules
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $this->validate();
        return empty($this->validationErrors);
    }

    /**
     * Returns validation errors, if any
     *
     * @return string[]
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
