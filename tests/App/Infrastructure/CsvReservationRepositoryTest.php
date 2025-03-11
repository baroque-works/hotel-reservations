<?php

namespace Tests\App\Infrastructure;

use App\Domain\Model\Reservation;
use App\Infrastructure\Http\ApiClient;
use App\Infrastructure\Repository\CsvReservationRepository;
use PHPUnit\Framework\TestCase;

class CsvReservationRepositoryTest extends TestCase
{
    private $repository;
    private $mockApiClient;

    protected function setUp(): void
    {
        $this->mockApiClient = $this->createMock(ApiClient::class);
        $this->mockApiClient->expects($this->once())
            ->method('fetchCsvData')
            ->willReturn("Localizador;Huésped;Fecha Entrada;Fecha Salida;Hotel;Precio;Acciones\n"
                . "34637;Nombre1;2018-10-04;2018-10-05;Hotel4;112.49;view\n"
                . '34638;Nombre2;2018-10-05;2018-10-06;Hotel5;125.00;edit');

        $this->repository = new CsvReservationRepository($this->mockApiClient);
    }

    public function testFindAllReturnsReservationsFromValidCsv(): void
    {
        $reservations = $this->repository->findAll();

        $this->assertCount(2, $reservations);

        // Verifies 1st Reservation
        $reservation1 = $reservations[0];
        $this->assertInstanceOf(Reservation::class, $reservation1);
        $this->assertEquals('34637', $reservation1->getLocator());
        $this->assertEquals('Nombre1', $reservation1->getGuest());
        $this->assertEquals('2018-10-04', $reservation1->getCheckInDate()->format('Y-m-d'));
        $this->assertEquals('2018-10-05', $reservation1->getCheckOutDate()->format('Y-m-d'));
        $this->assertEquals('Hotel4', $reservation1->getHotel());
        $this->assertEquals(112.49, $reservation1->getPrice());
        $this->assertEquals('view', $reservation1->getPossibleActions());

        // Verifies 2nd Reservation
        $reservation2 = $reservations[1];
        $this->assertInstanceOf(Reservation::class, $reservation2);
        $this->assertEquals('34638', $reservation2->getLocator());
        $this->assertEquals('Nombre2', $reservation2->getGuest());
        $this->assertEquals('2018-10-05', $reservation2->getCheckInDate()->format('Y-m-d'));
        $this->assertEquals('2018-10-06', $reservation2->getCheckOutDate()->format('Y-m-d'));
        $this->assertEquals('Hotel5', $reservation2->getHotel());
        $this->assertEquals(125.00, $reservation2->getPrice());
        $this->assertEquals('edit', $reservation2->getPossibleActions());
    }

    public function testFindAllHandlesEmptyCsv(): void
    {
        // Mock setup for an empty CSV
        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->expects($this->once())
            ->method('fetchCsvData')
            ->willReturn("Localizador;Huésped;Fecha Entrada;Fecha Salida;Hotel;Precio;Acciones\n");

        $repository = new CsvReservationRepository($mockApiClient);

        $reservations = $repository->findAll();

        $this->assertCount(0, $reservations);
    }

    public function testFindAllHandlesApiClientFailure(): void
    {
        // Mock setup for simulate a failure
        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->expects($this->once())
            ->method('fetchCsvData')
            ->willThrowException(new \RuntimeException('API failure'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API failure');
        new CsvReservationRepository($mockApiClient);
    }

    public function testFindBySearchTermWithEmptyTermReturnsAllReservations(): void
    {
        $reservations = $this->repository->findBySearchTerm('');
        $this->assertCount(2, $reservations);
    }

    public function testFindBySearchTermWithMatchingHotel(): void
    {
        $reservations = $this->repository->findBySearchTerm('Hotel4');
        $this->assertCount(1, $reservations);
        $this->assertEquals('Hotel4', $reservations[0]->getHotel());
    }

    public function testFindBySearchTermWithMatchingGuest(): void
    {
        $reservations = $this->repository->findBySearchTerm('Nombre1');
        $this->assertCount(1, $reservations);
        $this->assertEquals('Nombre1', $reservations[0]->getGuest());
    }

    public function testFindBySearchTermWithNoMatches(): void
    {
        $reservations = $this->repository->findBySearchTerm('NonExistent');
        $this->assertCount(0, $reservations);
    }

    public function testFindByPageReturnsCorrectSlice(): void
    {
        // Page 1, límit 1
        $reservations = $this->repository->findByPage(1, 1);
        $this->assertCount(1, $reservations);
        $this->assertEquals('34637', $reservations[0]->getLocator());

        // Page 2, límit 1
        $reservations = $this->repository->findByPage(2, 1);
        $this->assertCount(1, $reservations);
        $this->assertEquals('34638', $reservations[0]->getLocator());

        // Page 3, límit 1 (no more reservations)
        $reservations = $this->repository->findByPage(3, 1);
        $this->assertCount(0, $reservations);
    }

    public function testGetTotalReservations(): void
    {
        $total = $this->repository->getTotalReservations();
        $this->assertEquals(2, $total);
    }
}
