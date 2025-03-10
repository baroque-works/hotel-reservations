<?php

namespace Tests\App\Application\Service;

use App\Application\Service\ReservationService;
use App\Domain\Model\Reservation;
use App\Domain\Repository\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ReservationServiceTest extends TestCase
{
    private $service;
    private $mockRepository;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->service = new ReservationService($this->mockRepository);
    }

    public function testGetPaginatedReservationsReturnsCorrectData(): void
    {
        // Simulate data for page 1, liimt 1
        $reservationsPage1 = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
        ];
        $this->mockRepository->expects($this->once())
            ->method('findByPage')
            ->with(1, 1)
            ->willReturn($reservationsPage1);
        $this->mockRepository->expects($this->once())
            ->method('getTotalReservations')
            ->willReturn(2);

        $result = $this->service->getPaginatedReservations(1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('reservations', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(1, $result['reservations']);
        $this->assertEquals('34637', $result['reservations'][0]->getLocator());
        $this->assertEquals(2, $result['total']);
    }

    public function testGetPaginatedReservationsWithDefaultLimit(): void
    {
        // Simulate data for page 1, predetermined limit (20)
        $reservationsPage1 = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
            new Reservation('34638', 'Nombre2', new \DateTime('2018-10-05'), new \DateTime('2018-10-06'), 'Hotel5', 125.00, 'edit'),
        ];
        $this->mockRepository->expects($this->once())
            ->method('findByPage')
            ->with(1, 20)
            ->willReturn($reservationsPage1);
        $this->mockRepository->expects($this->once())
            ->method('getTotalReservations')
            ->willReturn(2);

        $result = $this->service->getPaginatedReservations(1);

        $this->assertCount(2, $result['reservations']);
        $this->assertEquals(2, $result['total']);
    }

    public function testSearchReservationsWithEmptyTermReturnsAllPaginated(): void
    {
        // Simulate data for an empty searching, page 1, limit 1
        $allReservations = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
            new Reservation('34638', 'Nombre2', new \DateTime('2018-10-05'), new \DateTime('2018-10-06'), 'Hotel5', 125.00, 'edit'),
        ];
        $this->mockRepository->expects($this->once())
            ->method('findBySearchTerm')
            ->with('')
            ->willReturn($allReservations);

        $result = $this->service->searchReservations('', 1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('reservations', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(1, $result['reservations']);
        $this->assertEquals('34637', $result['reservations'][0]->getLocator());
        $this->assertEquals(2, $result['total']);
    }

    public function testSearchReservationsWithMatchingTerm(): void
    {
        // Simulate data for searching "Hotel4", page 1, limit 1
        $filteredReservations = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
        ];
        $this->mockRepository->expects($this->once())
            ->method('findBySearchTerm')
            ->with('Hotel4')
            ->willReturn($filteredReservations);

        $result = $this->service->searchReservations('Hotel4', 1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('reservations', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(1, $result['reservations']);
        $this->assertEquals('Hotel4', $result['reservations'][0]->getHotel());
        $this->assertEquals(1, $result['total']);
    }

    public function testSearchReservationsWithNoMatches(): void
    {
        $this->mockRepository->expects($this->once())
            ->method('findBySearchTerm')
            ->with('NonExistent')
            ->willReturn([]);

        $result = $this->service->searchReservations('NonExistent');

        $this->assertCount(0, $result['reservations']);
        $this->assertEquals(0, $result['total']);
    }
}
