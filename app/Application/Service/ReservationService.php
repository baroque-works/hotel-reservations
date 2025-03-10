<?php

namespace App\Application\Service;

use App\Domain\Model\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;

class ReservationService
{
    public function __construct(private ReservationRepositoryInterface $repository)
    {
    }

    /**
     * Get all reservations
     *
     * @return Reservation[]
     */
    public function getPaginatedReservations(int $page = 1, int $limit = 20): array
    {
        $reservations = $this->repository->findByPage($page, $limit);
        return [
            'reservations' => $reservations,
            'total' => $this->repository->getTotalReservations(),
        ];
    }

    /**
     * Find reservations for searchTerm and get paginated results
     *
     * @param string $searchTerm
     * @param int $page
     * @param int $limit
     * @return array ['reservations' => Reservation[], 'total' => int]
     */
    public function searchReservations(string $searchTerm, int $page = 1, int $limit = 20): array
    {
        $filteredReservations = $this->repository->findBySearchTerm($searchTerm);
        $offset = ($page - 1) * $limit;
        $paginatedReservations = array_slice(array_values($filteredReservations), $offset, $limit);

        return [
            'reservations' => $paginatedReservations,
            'total' => count($filteredReservations),
        ];
    }
}
