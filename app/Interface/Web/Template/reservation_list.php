<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .invalid-reservation {
            background-color: #fff3f3;
        }
    </style>
</head>
<body>
    <?php
        $searchTerm = $searchTerm ?? '';
        $page = $page ?? 1;
        $reservations = $reservations ?? [];
        $totalReservations = $totalReservations ?? 0;
        $totalPages = $totalPages ?? 1;
        $request = $request ?? [];
    ?>

    <div class="header-container">
        <div class="logo-container">
            <img src="/img/logo.png" alt="Logo" class="logo">
        </div>
        <h1>Gestión de Reservas</h1>
    </div>

    <div class="container">
        <div class="search-and-download-container">
            <div class="search-container">
                <form method="get" action="/" class="d-flex">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search"
                           value="<?= htmlspecialchars($searchTerm) ?>"
                           class="form-control"
                           placeholder="Buscar reserva...">
                    <?php if (!empty($searchTerm)) : ?>
                        <button type="button" class="clear-search"
                                onclick="window.location.href='/'">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>
            <div class="download-json-container">
                <a href="/download-json<?= !empty($searchTerm) ? '?search=' . urlencode($searchTerm) : '' ?>"
                   class="btn">Descargar JSON</a>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Localizador</th>
                            <th>Huésped</th>
                            <th>Fecha Entrada</th>
                            <th>Fecha Salida</th>
                            <th>Hotel</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations) && $page == 1) : ?>
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron reservas</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($reservations as $reservation) : ?>
                                <?php
                                $isValid = $reservation->isValid();
                                $errors = $reservation->getValidationErrors();
                                $errorMessage = $isValid ? '' : implode(', ', $errors);
                                ?>
                                <tr class="<?= $isValid ? '' : 'invalid-reservation' ?>">
                                    <td><?= htmlspecialchars($reservation->getLocator()) ?></td>
                                    <td><?= htmlspecialchars($reservation->getGuest()) ?></td>
                                    <td><?= $reservation->getCheckInDate()->format('d/m/Y') ?></td>
                                    <td><?= $reservation->getCheckOutDate()->format('d/m/Y') ?></td>
                                    <td><?= htmlspecialchars($reservation->getHotel()) ?></td>
                                    <td><?= $reservation->getPrice() !== null ? number_format($reservation->getPrice(), 2, ',', '.') . ' €' : '-' ?></td>
                                    <td>
                                        <?php if ($isValid) : ?>
                                            <a href="#" class="action-icon"
                                               data-bs-toggle="modal"
                                               data-bs-target="#chargeModal<?= $reservation->getLocator() ?>"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="Cobrar">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                            <a href="#" class="action-icon"
                                               data-bs-toggle="modal"
                                               data-bs-target="#refundModal<?= $reservation->getLocator() ?>"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="Devolver">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>

                                            <div class="modal fade" id="chargeModal<?= $reservation->getLocator() ?>"
                                                 tabindex="-1"
                                                 aria-labelledby="chargeModalLabel<?= $reservation->getLocator() ?>"
                                                 aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="chargeModalLabel<?= $reservation->getLocator() ?>">Confirmar Cobro</h5>
                                                            <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            ¿Seguro que quiere cobrar la reservación <?= htmlspecialchars($reservation->getLocator()) ?>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="button" class="btn btn-primary"
                                                                    data-bs-dismiss="modal">Confirmar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="refundModal<?= $reservation->getLocator() ?>"
                                                 tabindex="-1"
                                                 aria-labelledby="refundModalLabel<?= $reservation->getLocator() ?>"
                                                 aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="refundModalLabel<?= $reservation->getLocator() ?>">Confirmar Devolución</h5>
                                                            <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            ¿Seguro que quiere devolver la reservación <?= htmlspecialchars($reservation->getLocator()) ?>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="button" class="btn btn-primary"
                                                                    data-bs-dismiss="modal">Confirmar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isValid) : ?>
                                            <i class="bi bi-check-circle text-success"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="Reserva válida"></i>
                                        <?php else : ?>
                                            <i class="bi bi-exclamation-triangle text-danger"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="<?= htmlspecialchars($errorMessage) ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalReservations > 0) : ?>
            <div class="pagination">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($request, ['page' => $page - 1, 'search' => $searchTerm])) ?>"
                               aria-label="Previous">
                                <span aria-hidden="true">« Anterior</span>
                            </a>
                        </li>
                        <?php
                        $maxPagesToShow = 5;
                        $halfPages = floor($maxPagesToShow / 2);
                        $startPage = max(1, $page - $halfPages);
                        $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

                        if ($endPage - $startPage + 1 < $maxPagesToShow) {
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                        }

                        if ($startPage > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($request, ['page' => 1, 'search' => $searchTerm])) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2) : ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++) : ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($request, ['page' => $i, 'search' => $searchTerm])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages) : ?>
                            <?php if ($endPage < $totalPages - 1) : ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($request, ['page' => $totalPages, 'search' => $searchTerm])) ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($request, ['page' => $page + 1, 'search' => $searchTerm])) ?>"
                               aria-label="Next">
                                <span aria-hidden="true">Siguiente »</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <p>Mostrando página <?= $page ?> de <?= $totalPages ?>
                    (Total de reservas: <?= $totalReservations ?>)</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
