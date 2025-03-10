<?php

namespace App\Domain\Repository;

use App\Domain\Model\Reservation;

interface ReservationRepositoryInterface
{
    /**
     * Find all reservations.
     *
     * @return Reservation[]
     */
    public function findAll(): array;

    /**
     * Find reservations by search term.
     *
     * @param string $searchTerm
     * @return Reservation[]
     */
    public function findBySearchTerm(string $searchTerm): array;

    /**
     * Get reservations for a specific page.
     *
     * @param int $page
     * @param int $limit
     * @return Reservation[]
     */
    public function findByPage(int $page, int $limit = 10): array;

    /**
     * Get total number of reservations.
     *
     * @return int
     */
    public function getTotalReservations(): int;
}
