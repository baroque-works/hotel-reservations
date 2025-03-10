<?php

namespace App\Interface\Web\Controller;

use App\Application\Service\ReservationService;
use App\Domain\Model\Reservation;

class Response
{
    private $headers;
    private $content;

    public function __construct(array $headers, string $content)
    {
        $this->headers = $headers;
        $this->content = $content;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function send(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }
        echo $this->content;
        exit;
    }
}

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

    public function downloadJsonAction(array $request): Response
    {
        $searchTerm = $request['search'] ?? '';
        $result = !empty($searchTerm)
            ? $this->reservationService->searchReservations($searchTerm, 1, PHP_INT_MAX)
            : $this->reservationService->getPaginatedReservations(1, PHP_INT_MAX);

        $reservations = $result['reservations'];

        $jsonData = array_map(function (Reservation $reservation) {
            return [
                'locator' => $reservation->getLocator(),
                'guest' => $reservation->getGuest(),
                'checkInDate' => $reservation->getCheckInDate()->format('Y-m-d'),
                'checkOutDate' => $reservation->getCheckOutDate()->format('Y-m-d'),
                'hotel' => $reservation->getHotel(),
                'price' => $reservation->getPrice() ?? null,
                'possibleActions' => $reservation->getPossibleActions(),
            ];
        }, $reservations);

        $content = json_encode($jsonData, JSON_PRETTY_PRINT);
        $headers = [
            'Content-Type: application/json',
            'Content-Disposition: attachment; filename="reservations.json"',
        ];

        return new Response($headers, $content);
    }
}
