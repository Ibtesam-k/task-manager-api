# Task Manager API

A RESTful API for collaborative project and task management, built with Laravel. It supports project ownership, team membership, task assignment, and collaboration through comments and activity history.

## Features

- **Authentication:** User registration, login, and logout using Laravel Sanctum personal access tokens.
- **Project management:** Create, view, update, and soft-delete projects.
- **Membership and roles:** Add and remove project members, promote members to owners, demote owners, and leave projects.
- **Task management:** Create, update, and soft-delete tasks; manage task status and assignments with role-based permissions.
- **Comments:** Create, view, update, and delete task comments with membership-based authorization.
- **Activity history:** Automatically record project activities, including task changes, membership changes, and comments.
- **Notifications:** Database notifications for task assignments and new comments.
- **Account deletion:** Soft-delete user accounts while preserving historical records and enforcing project ownership rules.

## Technical Highlights

- Layered application structure using Controllers, Form Requests, Services, and Policies.
- Role-based authorization and explicit business rules.
- Database transactions and row-level locking to protect concurrent membership and ownership operations.
- Event-driven activity tracking.
- UUID-based resource identifiers and soft deletion.
- Feature tests covering API behavior, authorization, and business rules.

## Tech Stack

- PHP 8.3+
- Laravel 13
- MySQL 8.4
- Laravel Sanctum
- Eloquent ORM
- PHPUnit 12
- Docker and Laravel Sail

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Docker Desktop with Docker Compose

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/Ibtesam-k/task-manager-api.git
   cd task-manager-api
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Create the environment file:

   ```bash
   cp .env.example .env
   ```

4. Start the Docker services:

   ```bash
   ./vendor/bin/sail up -d
   ```

5. Generate the application key:

   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. Run database migrations:

   ```bash
   ./vendor/bin/sail artisan migrate
   ```

The API is available at `http://localhost`.

### Running Tests

Run the PHPUnit test suite using Laravel Sail:

```bash
./vendor/bin/sail artisan test
```

Tests use the separate `testing` database configured in `phpunit.xml`.

## License

This project is currently available for educational and portfolio purposes.