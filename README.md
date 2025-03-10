# Hotel Reservation Application

This application displays a list of hotel reservations obtained from an external server, allows text-based searches, and provides data download in JSON format.

## Requirements

- Docker and Docker Compose (recommended for running the application)
- PHP 8.1 or higher (if running without Docker)
- Composer (if running without Docker)
- PHP extensions: `json`, `curl`

## Installation

### Option 1: Using Docker (Recommended)

1. **Clone the repository**:

git clone https://github.com/baroque-works/hotel-reservations.git
cd hotel-reservations

2. **Build and run the Docker container**:

docker-compose up --build

This will build the Docker image and start the application on port `8080`.

3. **Access the application**:
   Open a web browser and visit `http://localhost:8080`.

4. **Stop the container** (when done):

docker-compose down

### Option 2: Using PHP's Built-in Server (Without Docker)

1. **Clone the repository**:

git clone https://github.com/yourusername/hotel-reservations.git
cd hotel-reservations

2. **Install dependencies**:

composer install

3. **Set up environment variables**:
   Copy the `.env.example` file to `.env` and fill in the required values (e.g., API credentials for the external server):

cp .env.example .env

Edit `.env` with your credentials:

API_BASE_URL=https://api.example.com
API_USERNAME=your_username
API_PASSWORD=your_password

4. **Start the PHP built-in server**:

php -S localhost:8080 -t .

5. **Access the application**:
   Open a web browser and visit `http://localhost:8080`.

### Option 3: Using Apache or Nginx (Without Docker)

1. Follow steps 1-3 from "Option 2" to clone the repository, install dependencies, and set up environment variables.
2. Configure a virtual host in Apache or Nginx pointing to the project root directory (e.g., `/path/to/hotel-reservations/`).
3. Ensure the web server has PHP 8.1 or higher and the required extensions (`json`, `curl`).
4. Access the application at the configured URL (e.g., `http://hotel-reservations.local`).

## Project Structure

The project follows a clean domain architecture with the following layers:

- **Domain** (`app/Domain/`): Contains entities and business rules.
- **Application** (`app/Application/`): Contains application services.
- **Infrastructure** (`app/Infrastructure/`): Contains concrete implementations of repositories and external interfaces.
- **Interface** (`app/Interface/`): Contains controllers and templates for the web interface.
- **Tests** (`tests/`): Contains unit and integration tests.
- **Docker** (`docker/`): Contains Docker configuration files (`Dockerfile`, `docker-compose.yml`, Apache configs, etc.).

## Features

1. Display all reservations in a table with pagination.
2. Free-text search across any reservation field.
3. Download reservations (all or filtered) in JSON format.

## Technical Decisions

- **PHP 8.1**: Leveraging features like constructor property promotion, property types, and arrow functions.
- **Docker**: Provides a consistent environment for development and deployment.
- **No full framework**: Opted for a lightweight implementation with specific libraries for each need.
- **Clean architecture**: Clear separation of responsibilities in well-defined layers.
- **Simple interface**: Uses Bootstrap for a responsive and modern UI.
- **Error handling**: Basic error handling and data validation implementation.
- **Unit tests**: Includes unit tests for controllers and services using PHPUnit.

## Libraries Used

- **guzzlehttp/guzzle**: HTTP client for consuming the external API.
- **league/csv**: For handling and processing CSV files.
- **nikic/fast-route**: Lightweight router to manage application routes.
- **vlucas/phpdotenv**: For managing environment variables.
- **phpunit/phpunit**: For unit testing (development dependency).

## Running Tests

The project includes unit tests for controllers and services. To run the tests:

1. **Access the Docker container** (if using Docker):

docker exec -it hotel-reservations-app bash

2. **Run the tests**:

vendor/bin/phpunit

This will execute all tests in the `tests/` directory.

3. **Run specific tests** (optional):
   To run tests for a specific file, e.g., `ReservationControllerTest.php`:

vendor/bin/phpunit tests/App/Interface/Web/Controller/ReservationControllerTest.php

## Environment Variables

The application uses environment variables for configuration, managed via a `.env` file. Required variables:

- `API_BASE_URL`: Base URL of the external API (e.g., `https://api.example.com`).
- `API_USERNAME`: Username for API authentication.
- `API_PASSWORD`: Password for API authentication.
- `APP_DEBUG`: Set to `true` for debug mode, `false` for production (default: `false`).

Example `.env` file:

API_BASE_URL=https://api.example.com
API_USERNAME=your_username
API_PASSWORD=your_password
APP_DEBUG=true

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
