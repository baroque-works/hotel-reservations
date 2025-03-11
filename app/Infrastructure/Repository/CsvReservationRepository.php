<?php

namespace App\Infrastructure\Repository;

use App\Domain\Model\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Infrastructure\Http\ApiClient;

class CsvReservationRepository implements ReservationRepositoryInterface
{
    /** @var Reservation[] */
    private array $cachedReservations;

    public function __construct(
        private ApiClient $apiClient
    ) {
        $this->cachedReservations = $this->loadReservations();
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        return $this->cachedReservations;
    }

    /**
     * @inheritDoc
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return $this->cachedReservations;
        }

        return array_filter($this->cachedReservations, fn (Reservation $reservation) => stripos($reservation->getHotel(), $searchTerm) !== false || stripos($reservation->getGuest(), $searchTerm) !== false);
    }

    /**
     * Get reservations for a specific page
     *
     * @param int $page
     * @param int $limit
     * @return Reservation[]
     */
    public function findByPage(int $page, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        return array_slice($this->cachedReservations, $offset, $limit);
    }

    /**
     * Get total reservations
     *
     * @return int
     */
    public function getTotalReservations(): int
    {
        return count($this->cachedReservations);
    }

    /**
     * Load reservations from CSV
     */
    private function loadReservations(): array
    {
        $csvData = $this->apiClient->fetchCsvData();
        return $this->parseCsvData($csvData);
    }

    /**
     * Parse CSV data and turns it into a Reservation object
     *
     * @param string $csvData
     * @return Reservation[]
     */
    private function parseCsvData(string $csvData): array
    {
        $rows = explode("\n", $csvData);
        $reservations = [];

        foreach ($rows as $row) {
            if (empty(trim($row))) {
                continue;
            }

            $fields = str_getcsv($row, ';', '"', '');

            if (count($fields) >= 7) {
                try {
                    $checkInDate = \DateTime::createFromFormat('Y-m-d', $fields[2]);
                    $checkOutDate = \DateTime::createFromFormat('Y-m-d', $fields[3]);

                    if ($checkInDate === false || $checkOutDate === false) {
                        continue;
                    }

                    $price = !empty($fields[5]) ? (float)str_replace(',', '.', $fields[5]) : null;

                    $reservations[] = new Reservation(
                        $fields[0],
                        $fields[1],
                        $checkInDate,
                        $checkOutDate,
                        $fields[4],
                        $price,
                        $fields[6]
                    );
                } catch (\Exception $e) {
                    error_log('Error parsing row: ' . $row . ' - ' . $e->getMessage());
                    continue;
                }
            }
        }

        return $reservations;
    }
}
