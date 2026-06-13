# Gaming App — Laravel + React + Docker

A browser-based gaming platform built with **Laravel 13** (backend API) and **React 19** (frontend SPA). The platform hosts a card comparison game where players compete against the computer, with scores persisted in MySQL and displayed on a global leaderboard.

## What it does

- **Card Game** — A suit is drawn at random at the start of each round. The player selects any number of cards (A–K) from that suit; the computer draws the same number of cards from the remaining ones. Cards are compared pair by pair: the higher value wins each point, with ties going to the computer.
- **Leaderboard** — Every completed game is saved to the database. The top 10 scores are displayed on a leaderboard page, ranked by player score.
- **Backend-driven logic** — All game logic (card generation, comparison, score resolution) lives in the Laravel service layer. The React frontend is a thin display shell that calls the API and renders the results.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | React 19, TypeScript, Vite |
| Styling | Tailwind CSS 4 |
| Database | MySQL 8.4 |
| Infrastructure | Docker, Nginx |

## Project Structure

The backend follows **Domain-Driven Design (DDD)**. Each domain owns its models, repositories, services, and requests, keeping cross-domain dependencies explicit.

```
.
├── gaming/                         # Laravel + React application
│   ├── app/
│   │   ├── Domains/
│   │   │   ├── Card/               # Card domain
│   │   │   │   ├── Enums/          # CardEnum (suits, faces, values)
│   │   │   │   ├── Messages/       # CardMessage (response messages)
│   │   │   │   ├── Models/         # Card
│   │   │   │   ├── Repositories/   # CardRepositoryInterface + CardRepository
│   │   │   │   └── Services/       # CardService (hand generation, comparison)
│   │   │   └── Score/              # Score domain
│   │   │       ├── Messages/       # ScoreMessage (response messages)
│   │   │       ├── Models/         # Score
│   │   │       ├── Repositories/   # ScoreRepositoryInterface + ScoreRepository
│   │   │       ├── Requests/       # ScoreRequest (validation)
│   │   │       └── Services/       # ScoreService (persistence, top-10)
│   │   ├── Http/Controllers/
│   │   │   ├── Card/               # CardController  — GET /api/cards/{suit}
│   │   │   ├── Score/              # ScoreController — POST /api/scores, GET /api/scores/top10
│   │   │   └── GameController.php  # POST /api/game/play
│   │   ├── Models/                 # User (framework model)
│   │   ├── Providers/              # AppServiceProvider (DI bindings)
│   │   └── Service/                # GameService (orchestrates card + score domains)
│   ├── database/
│   │   ├── factories/              # ScoreFactory
│   │   ├── migrations/             # cards, scores tables
│   │   └── seeders/                # CardSeeder (52 cards)
│   ├── resources/js/
│   │   ├── pages/                  # CardGamePage, HomePage, LeaderboardPage
│   │   ├── components/             # CardItem, PlayerNameModal
│   │   ├── lib/                    # api.ts (fetchCards, playGame)
│   │   └── constants/              # cards.ts (SUIT_SYMBOL_MAP)
│   └── routes/api.php
├── docker/                         # Nginx, PHP, MySQL Docker configs
├── docker-compose.yml
├── Makefile                        # Developer shortcuts
└── scripts/install.sh              # Full bootstrap script
```

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/health` | Health check |
| GET | `/api/cards/{suit}` | Returns 13 cards for the given suit |
| POST | `/api/game/play` | Runs a game round, saves score, returns result |
| POST | `/api/scores` | Manually save a score record |
| GET | `/api/scores/top10` | Top 10 leaderboard entries |

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes Docker Compose)
- `make` (pre-installed on macOS/Linux)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/gustavomagalhaess/gaming-app-laravel-react.git
cd gaming-app-laravel-react
```

### 2. Set up environment variables

```bash
cp .env.example .env
```

Open `.env` and adjust if needed. The defaults work out of the box with Docker:

```env
APP_PORT=8081       # Port the app will be served on
VITE_PORT=5173      # Port for the Vite dev server (dev mode only)
DB_CONNECTION=mysql
DB_HOST=db           # Matches the MySQL service name in docker-compose.yml
DB_DATABASE=gaming
DB_USERNAME=gaming
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root
```

### 3. Bootstrap the application

```bash
make install
```

This single command:
1. Builds the Docker images (PHP, Nginx)
2. Installs PHP dependencies via Composer
3. Copies and configures `gaming/.env`
4. Starts MySQL and waits for it to be healthy
5. Runs all database migrations (including cards table with 52-card seed)
6. Installs Node dependencies and builds the frontend assets

## Running the Application

### Production mode (pre-built assets)

```bash
make up
```

Open [http://localhost:8081](http://localhost:8081)

### Development mode (Vite HMR)

```bash
make dev
```

Open [http://localhost:8081](http://localhost:8081) — frontend changes hot-reload automatically.

## Common Commands

| Command | Description |
|---|---|
| `make up` | Start the app stack (background) |
| `make dev` | Start with Vite dev server (hot reload) |
| `make down` | Stop containers (preserves database) |
| `make nuke` | Stop containers and wipe the database volume |
| `make test` | Run the PHPUnit test suite |
| `make fresh` | Drop all tables, re-migrate, and re-seed |
| `make shell` | Open a shell inside the PHP container |
| `make logs` | Tail logs from all containers |
| `make artisan c="<cmd>"` | Run an Artisan command, e.g. `make artisan c="migrate"` |

## CI

| Workflow | Trigger | What it does |
|---|---|---|
| **Code Style** (`.github/workflows/code-style.yml`) | Every Pull Request | Runs `./vendor/bin/pint --test` — fails if any file needs reformatting |
| **Tests** (`.github/workflows/tests.yml`) | Every Pull Request | Runs the full PHPUnit suite (Unit + Feature) against SQLite in-memory |

To fix code style failures locally before pushing:

```bash
make pint
```

## Running Tests

```bash
make test
```

Or directly from your host machine inside the `gaming/` directory:

```bash
cd gaming
php artisan test
```

The test suite covers:
- `CardControllerTest` — `GET /api/cards/{suit}` validation and response shape
- `GameControllerTest` — full play flow, validation, score persistence
- `CardServiceTest` — unit tests for game logic (ties, comparisons, hand generation)
- `ScoreControllerTest` — score store and top-10 leaderboard
