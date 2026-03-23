# Import People

A Symfony console command that imports people from a CSV file into the database and asynchronously calculates statistics.

## Requirements

- Docker and Docker Compose

## Starting the Infrastructure

```bash
docker compose up -d
```

This starts the following services:

| Service    | Description                           | Port  |
|------------|---------------------------------------|-------|
| `php`      | PHP application                       | —     |
| `worker`   | Messenger worker (async transport)    | —     |
| `mariadb`  | MariaDB 11 (3 databases: app, app_job_statistics, app_age_statistics) | 3306 |
| `valkey`   | Valkey (Redis-compatible) for Messenger | 6379 |
| `nginx`    | Nginx                                 | 8080  |

## CSV File Format

The file must contain a header row followed by data:

```csv
User Id,First Name,Last Name,Sex,Email,Phone,Date of birth,Job Title
dY2Gfj1,John,Doe,Male,john@example.com,+1234567890,1990-05-15,Developer
aB3Hkl2,Jane,Smith,Female,jane@example.com,+0987654321,2001-11-20,Designer
```

| Column          | Description                                |
|-----------------|--------------------------------------------|
| `User Id`       | Unique identifier (up to 15 characters)    |
| `First Name`    | First name                                 |
| `Last Name`     | Last name                                  |
| `Sex`           | `Male` or `Female`                         |
| `Email`         | Email address                              |
| `Phone`         | Phone number                               |
| `Date of birth` | Date of birth (any format accepted by PHP `DateTimeImmutable`) |
| `Job Title`     | Job title                                  |

## Usage

```bash
docker compose exec php bin/console app:import-people <file> [--batch-size=500]
```

### Arguments

| Argument | Required | Description        |
|----------|----------|--------------------|
| `file`   | Yes      | Path to the CSV file |

### Options

| Option           | Short | Default | Description                          |
|------------------|-------|---------|--------------------------------------|
| `--batch-size`   | `-b`  | 500     | Number of rows to flush to DB at once |

### Examples

Import with default settings:

```bash
docker compose exec php bin/console app:import-people var/share/people.csv
```

Import with a batch size of 1000:

```bash
docker compose exec php bin/console app:import-people var/share/people.csv --batch-size=1000
```

## How It Works

1. The command reads the CSV file and creates or updates a `Person` record in the main database (`app`) for each row. If a record with the given `User Id` already exists, it is updated.

2. After the import completes, the command dispatches an `ImportCompleted` message to the async queue (Valkey/Redis).

3. The worker (`worker` container) processes the message and runs two handlers:

   - **RefreshJobStatisticsHandler** — groups people by job title, counts totals, and saves results to `app_job_statistics`.
   - **RefreshAgeStatisticsHandler** — calculates age statistics (oldest, youngest, average age, count born before/after 2000) and saves to `app_age_statistics`.

## Worker

The worker starts automatically via `docker compose`. To run it manually:

```bash
docker compose exec php bin/console messenger:consume async -vv
```

To view failed messages:

```bash
docker compose exec php bin/console messenger:failed:show
```

To retry failed messages:

```bash
docker compose exec php bin/console messenger:failed:retry
```