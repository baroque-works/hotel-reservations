<?php

namespace App\Interface\Web\Controller;

use App\Application\Service\ReservationService;
use App\Domain\Model\Reservation;
use Generator;

class ReservationController
{
    private $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function listAction(array $request): void
    {
        $searchTerm = $request['search'] ?? '';
        $page = max(1, (int) ($request['page'] ?? 1));
        $itemsPerPage = 15;

        $result = !empty($searchTerm)
            ? $this->reservationService->searchReservations($searchTerm, $page, $itemsPerPage)
            : $this->reservationService->getPaginatedReservations($page, $itemsPerPage);

        $reservations = $result['reservations'];
        $totalReservations = $result['total'];
        $totalPages = max(1, ceil($totalReservations / $itemsPerPage));

        if ($page > $totalPages) {
            $result = !empty($searchTerm)
                ? $this->reservationService->searchReservations($searchTerm, $totalPages, $itemsPerPage)
                : $this->reservationService->getPaginatedReservations($totalPages, $itemsPerPage);
            $reservations = $result['reservations'];
            $page = $totalPages;
        }

        extract([
            'reservations' => $reservations,
            'totalReservations' => $totalReservations,
            'totalPages' => $totalPages,
            'page' => $page,
            'searchTerm' => $searchTerm,
            'request' => $request,
        ]);

        include __DIR__ . '/../Template/reservation_list.php';
    }
    
    /**
     * Generates a JSON response containing all reservations as a stream.
     *
     * @param array $request The request parameters, including 'search' term.
     * @return Generator Yields JSON fragments for streaming.
     */
    public function downloadJsonAction(array $request): Generator
    {
        try {
            $searchTerm = $request['search'] ?? '';
            error_log('Downloading JSON with search term: ' . $searchTerm);
            $result = $searchTerm
                ? $this->reservationService->searchReservations($searchTerm, 1, PHP_INT_MAX)
                : $this->reservationService->getPaginatedReservations(1, PHP_INT_MAX);

            error_log('Total reservations: ' . count($result['reservations'] ?? []));

            yield '[';

            $reservations = $result['reservations'] ?? [];
            $first = true;

            foreach ($reservations as $reservation) {
                if (!$first) {
                    yield ',';
                }
                $first = false;

                $reservationData = [
                    'locator' => $reservation->getLocator(),
                    'guest' => $reservation->getGuest(),
                    'checkInDate' => $reservation->getCheckInDate()->format('Y-m-d'),
                    'checkOutDate' => $reservation->getCheckOutDate()->format('Y-m-d'),
                    'hotel' => $reservation->getHotel(),
                    'price' => $reservation->getPrice(),
                    'possibleActions' => $reservation->getPossibleActions(),
                ];

                yield json_encode($reservationData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            }

            yield ']';
        } catch (\Throwable $e) {
            error_log('Error in downloadJsonAction: ' . $e->getMessage());
            yield json_encode(['error' => 'Error al generar el JSON: ' . $e->getMessage()]);
        }
    }
}
