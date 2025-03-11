<?php

namespace Tests\App\Interface\Web\Controller;

use App\Application\Service\ReservationService;
use App\Domain\Model\Reservation;
use App\Interface\Web\Controller\ReservationController;
use PHPUnit\Framework\TestCase;

class ReservationControllerTest extends TestCase
{
    private $controller;
    private $mockService;

    protected function setUp(): void
    {
        $this->mockService = $this->createMock(ReservationService::class);
        $this->controller = new ReservationController($this->mockService);
    }

    public function testListActionWithNoSearch(): void
    {
        $request = ['page' => '1'];
        $reservations = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
        ];
        $result = [
            'reservations' => $reservations,
            'total' => 2,
        ];

        $this->mockService->expects($this->once())
            ->method('getPaginatedReservations')
            ->with(1, 15)
            ->willReturn($result);

        ob_start();
        $this->controller->listAction($request);
        $output = ob_get_clean();

        $this->assertStringContainsString('Gestión de Reservas', $output);
        $this->assertStringContainsString('34637', $output);
    }

    public function testListActionWithSearch(): void
    {
        $request = ['search' => 'Hotel4', 'page' => '2'];
        $reservationsPage1 = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
        ];
        $resultPage1 = [
            'reservations' => $reservationsPage1,
            'total' => 1,
        ];
        $resultPage2 = [
            'reservations' => [],
            'total' => 1,
        ];

        $this->mockService->expects($this->exactly(2))
            ->method('searchReservations')
            ->withConsecutive(
                ['Hotel4', 2, 15],
                ['Hotel4', 1, 15]
            )
            ->willReturnOnConsecutiveCalls(
                $resultPage2,
                $resultPage1
            );

        ob_start();
        $this->controller->listAction($request);
        $output = ob_get_clean();

        $this->assertStringContainsString('Gestión de Reservas', $output);
        $this->assertStringContainsString('34637', $output);
    }

    public function testListActionWithPageExceedingTotalPages(): void
    {
        $request = ['search' => 'Hotel4', 'page' => '999'];
        $reservations = [];
        $result = [
            'reservations' => $reservations,
            'total' => 1,
        ];

        $this->mockService->expects($this->exactly(2))
            ->method('searchReservations')
            ->withConsecutive(
                ['Hotel4', 999, 15],
                ['Hotel4', 1, 15]
            )
            ->willReturnOnConsecutiveCalls(
                ['reservations' => [], 'total' => 1],
                $result
            );

        ob_start();
        $this->controller->listAction($request);
        $output = ob_get_clean();

        $this->assertStringContainsString('Gestión de Reservas', $output);
        $this->assertStringContainsString('No se encontraron reservas', $output);
    }

    public function testDownloadJsonActionWithNoSearch(): void
    {
        $reservations = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
        ];
        $result = [
            'reservations' => $reservations,
            'total' => 1,
        ];

        $this->mockService->expects($this->once())
            ->method('getPaginatedReservations')
            ->with(1, PHP_INT_MAX)
            ->willReturn($result);

        $response = $this->controller->downloadJsonAction([]);

        $expectedHeaders = [
            'Content-Type: application/json',
            'Content-Disposition: attachment; filename="reservations.json"',
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());

        $expectedJson = json_encode([
            [
                'locator' => '34637',
                'guest' => 'Nombre1',
                'checkInDate' => '2018-10-04',
                'checkOutDate' => '2018-10-05',
                'hotel' => 'Hotel4',
                'price' => 112.49,
                'possibleActions' => 'view',
            ]
        ], JSON_PRETTY_PRINT);
        $this->assertEquals($expectedJson, $response->getContent());
    }

    public function testDownloadJsonActionWithSearch(): void
    {
        $reservations = [
            new Reservation('34637', 'Nombre1', new \DateTime('2018-10-04'), new \DateTime('2018-10-05'), 'Hotel4', 112.49, 'view'),
        ];
        $result = [
            'reservations' => $reservations,
            'total' => 1,
        ];

        $this->mockService->expects($this->once())
            ->method('searchReservations')
            ->with('Hotel4', 1, PHP_INT_MAX)
            ->willReturn($result);

        $response = $this->controller->downloadJsonAction(['search' => 'Hotel4']);

        $expectedHeaders = [
            'Content-Type: application/json',
            'Content-Disposition: attachment; filename="reservations.json"',
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());

        $expectedJson = json_encode([
            [
                'locator' => '34637',
                'guest' => 'Nombre1',
                'checkInDate' => '2018-10-04',
                'checkOutDate' => '2018-10-05',
                'hotel' => 'Hotel4',
                'price' => 112.49,
                'possibleActions' => 'view',
            ]
        ], JSON_PRETTY_PRINT);
        $this->assertEquals($expectedJson, $response->getContent());
    }

    public function testReservationIsInvalidWithIncorrectDates(): void
    {
        $reservation = new Reservation(
            '34637',
            'Nombre1',
            new \DateTime('2018-10-05'),
            new \DateTime('2018-10-04'),
            'Hotel4',
            112.49,
            'view'
        );

        $this->assertFalse($reservation->isValid());
        $this->assertContains('La fecha de salida debe ser igual o posterior a la fecha de entrada', $reservation->getValidationErrors());
    }

    public function testReservationIsValidWithSameDayDates(): void
    {
        $reservation = new Reservation(
            '34637',
            'Nombre1',
            new \DateTime('2018-10-04'),
            new \DateTime('2018-10-04'),
            'Hotel4',
            112.49,
            'view'
        );

        $this->assertTrue($reservation->isValid());
        $this->assertEmpty($reservation->getValidationErrors());
    }

    public function testReservationIsInvalidWithEmptyLocator(): void
    {
        $reservation = new Reservation(
            '',
            'Nombre1',
            new \DateTime('2018-10-04'),
            new \DateTime('2018-10-05'),
            'Hotel4',
            112.49,
            'view'
        );

        $this->assertFalse($reservation->isValid());
        $this->assertContains('El localizador no puede estar vacío', $reservation->getValidationErrors());
    }

    public function testReservationIsInvalidWithMissingPrice(): void
    {
        $reservation = new Reservation(
            '34637',
            'Nombre1',
            new \DateTime('2018-10-04'),
            new \DateTime('2018-10-05'),
            'Hotel4',
            null,
            'view'
        );

        $this->assertFalse($reservation->isValid());
        $this->assertContains('Falta el precio', $reservation->getValidationErrors());
    }

    public function testReservationIsInvalidWithMissingPriceForChargeable(): void
    {
        $reservation = new Reservation(
            '34637',
            'Nombre1',
            new \DateTime('2018-10-04'),
            new \DateTime('2018-10-05'),
            'Hotel4',
            null,
            'charge'
        );

        $this->assertFalse($reservation->isValid());
        $this->assertContains('El precio es obligatorio para reservas cobrables', $reservation->getValidationErrors());
    }

    public function testReservationIsValid(): void
    {
        $reservation = new Reservation(
            '34637',
            'Nombre1',
            new \DateTime('2018-10-04'),
            new \DateTime('2018-10-05'),
            'Hotel4',
            112.49,
            'view'
        );

        $this->assertTrue($reservation->isValid());
        $this->assertEmpty($reservation->getValidationErrors());
    }
}
