# Hotel Reservation Application

This application displays a list of hotel reservations obtained from an external server, allows text-based searches, and provides data download in JSON format with optimized streaming for large datasets.

## Requirements

- Docker and Docker Compose (recommended for running the application)
- PHP 8.1 or higher (if running without Docker)
- Composer (if running without Docker)
- PHP extensions: `json`, `curl`

## Installation

### Option 1: Using Docker (Recommended)

1. **Clone the repository**:

   ```bash
   git clone https://github.com/baroque-works/hotel-reservations.git
   cd hotel-reservations
   ```

2. **Build and run the Docker container**:

   ```bash
   docker-compose up --build
   ```

   This will build the Docker image and start the application on port 8080. Ensure sufficient memory allocation (e.g., mem_limit: 1g in docker-compose.yml) to handle large datasets.

3. **Access the application**: Open a web browser and visit http://localhost:8080.

4. **Stop the container (when done)**:
   ```bash
   docker-compose down
   ```

### Option 2: Using PHP's Built-in Server (Without Docker)

1. **Clone the repository**:

   ```bash
   git clone https://github.com/baroque-works/hotel-reservations.git
   cd hotel-reservations
   ```

2. **Install dependencies**:

   ```bash
   composer install
   ```

3. **Set up environment variables**: Copy the .env.example file to .env and fill in the required values (e.g., API credentials for the external server):

   ```bash
   cp .env.example .env
   ```

4. **Edit .env with your credentials**:

   ```
   API_BASE_URL=https://api.example.com
   API_USERNAME=your_username
   API_PASSWORD=your_password
   ```

5. **Start the PHP built-in server**:

   ```bash
   php -S localhost:8080 -t public
   ```

6. **Access the application**: Open a web browser and visit http://localhost:8080.

### Option 3: Using Apache or Nginx (Without Docker)

1. Follow steps 1-3 from "Option 2" to clone the repository, install dependencies, and set up environment variables.
2. Configure a virtual host in Apache or Nginx pointing to the project root directory (e.g., /path/to/hotel-reservations/public/).
3. Ensure the web server has PHP 8.1 or higher and the required extensions (json, curl).
4. Access the application at the configured URL (e.g., http://hotel-reservations.local).

## Project Structure

The project follows a clean domain architecture with the following layers:

- **Domain** (`app/Domain/`): Contains entities and business rules.
- **Application** (`app/Application/`): Contains application services.
- **Infrastructure** (`app/Infrastructure/`): Contains concrete implementations of repositories and external interfaces.
- **Interface** (`app/Interface/`): Contains controllers and templates for the web interface.
- **Tests** (`tests/`): Contains unit and integration tests.
- **Docker** (`docker/`): Contains Docker configuration files (Dockerfile, docker-compose.yml, Apache configs, etc.).
- **Public** (`public/`): Contains the entry point (index.php) and static assets.

## Features

- Display all reservations in a table with pagination.
- Free-text search across any reservation field.
- Download reservations (all or filtered) in JSON format using streaming to handle large datasets efficiently.

## Technical Decisions

- **PHP 8.1**: Leveraging features like constructor property promotion, property types, and arrow functions.
- **Docker**: Provides a consistent environment for development and deployment, with configurable memory limits.
- **No full framework**: Opted for a lightweight implementation with specific libraries for each need.
- **Clean architecture**: Clear separation of responsibilities in well-defined layers.
- **Simple interface**: Uses Bootstrap for a responsive and modern UI, with PSR-12 compliance for code formatting.
- **Error handling**: Enhanced error handling with logging and validation, especially for JSON downloads.
- **Unit tests**: Includes comprehensive unit tests for controllers and services using PHPUnit, with test coverage for streaming functionality.
- **Performance optimization**: Implemented streaming in downloadJsonAction to manage memory usage for large JSON downloads.

## Libraries Used

- **guzzlehttp/guzzle**: HTTP client for consuming the external API.
- **league/csv**: For handling and processing CSV files.
- **nikic/fast-route**: Lightweight router to manage application routes.
- **vlucas/phpdotenv**: For managing environment variables.
- **phpunit/phpunit**: For unit testing (development dependency).
- **phpstan/phpstan**: Added for static analysis to ensure code quality (development dependency).

## Running Tests

The project includes unit tests for controllers and services. To run the tests:

1. **Access the Docker container** (if using Docker):

   ```bash
   docker exec -it hotel-reservations-app bash
   ```

2. **Run the tests**:

   ```bash
   vendor/bin/phpunit
   ```

   This will execute all tests in the `tests/` directory and generate a testdox.html report (ignored in version control).

3. **Run specific tests** (optional): To run tests for a specific file, e.g., ReservationControllerTest.php:
   ```bash
   vendor/bin/phpunit tests/App/Interface/Web/Controller/ReservationControllerTest.php
   ```

## Static Analysis with PHPStan

To ensure code quality, the project uses PHPStan for static analysis:

1. **Install PHPStan** (if not already included):

   ```bash
   composer require --dev phpstan/phpstan
   ```

2. **Run PHPStan**:
   ```bash
   vendor/bin/phpstan analyse -c phpstan.neon
   ```
   This analyzes the code against the configuration in phpstan.neon.

## Environment Variables

The application uses environment variables for configuration, managed via a `.env` file. Required variables:

- **API_BASE_URL**: Base URL of the external API (e.g., https://api.example.com).
- **API_USERNAME**: Username for API authentication.
- **API_PASSWORD**: Password for API authentication.
- **APP_DEBUG**: Set to true for debug mode, false for production (default: false).

Example `.env` file:

```
API_BASE_URL=https://api.example.com
API_USERNAME=your_username
API_PASSWORD=your_password
APP_DEBUG=true
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.
