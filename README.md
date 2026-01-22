# Translation Management API Service

A scalable, high-performance Translation Management API built with Laravel 12, designed to handle 100k+ translation records with optimized response times.

## Features

- 🌍 **Multi-locale Support**: Store translations for multiple locales (en, fr, es, etc.)
- 🏷️ **Contextual Tagging**: Tag translations by context (mobile, desktop, web)
- 🔍 **Advanced Search**: Search by key, content, locale, or tags
- 📤 **JSON Export**: Optimized export endpoint for frontend applications
- 🔐 **Token Authentication**: Secure API access with Laravel Sanctum
- 🐳 **Docker Ready**: Full Docker setup with MySQL and Nginx
- 📚 **API Documentation**: OpenAPI/Swagger documentation

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+ (or SQLite for development)
- Docker & Docker Compose (optional)

## Quick Start

### Local Development

```bash
# Clone the repository
git clone <repository-url>
cd translation-service

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed default users
php artisan db:seed

# Start development server
php artisan serve
```

### Docker Setup

```bash
# Copy Docker environment
cp .env.docker .env

# Generate app key
docker compose run --rm app php artisan key:generate

# Start containers
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate

# Seed database
docker compose exec app php artisan db:seed
```

Access the API at `http://localhost:8080`

## Authentication

All endpoints except `/api/login` require Bearer token authentication.

```bash
# Login to get token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "password"}'

# Use token for requests
curl http://localhost:8000/api/translations \
  -H "Authorization: Bearer <your-token>"
```

### Default Users

| Email | Password |
|-------|----------|
| admin@example.com | password |
| test@example.com | password |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Get authentication token |
| POST | `/api/logout` | Revoke current token |
| GET | `/api/translations` | List translations (paginated) |
| POST | `/api/translations` | Create translation |
| GET | `/api/translations/{id}` | Get translation |
| PUT | `/api/translations/{id}` | Update translation |
| DELETE | `/api/translations/{id}` | Delete translation |
| GET | `/api/translations/search` | Search translations |
| GET | `/api/tags` | List tags |
| POST | `/api/tags` | Create tag |
| GET | `/api/tags/{id}` | Get tag |
| PUT | `/api/tags/{id}` | Update tag |
| DELETE | `/api/tags/{id}` | Delete tag |
| GET | `/api/export` | Export all translations |

### API Documentation

Access Swagger UI at: `http://localhost:8000/api/docs`

OpenAPI spec available at: `http://localhost:8000/openapi.yaml`

## Seeding Test Data

Seed 100k+ translations for performance testing:

```bash
# Seed 100,000 translations
php artisan translations:seed --count=100000

# With tags attached
php artisan translations:seed --count=100000 --with-tags

# Custom batch size
php artisan translations:seed --count=50000 --batch=2000
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run performance tests
php artisan test --filter=Performance
```

## Architecture

```
app/
├── Console/Commands/        # Artisan commands
├── Http/
│   ├── Controllers/Api/     # Thin API controllers
│   ├── Requests/            # Form request validation
│   └── Resources/           # API resource transformers
├── Models/                  # Eloquent models
├── Repositories/            # Data access layer
│   └── Contracts/           # Repository interfaces
├── Services/                # Business logic layer
└── Providers/               # Service providers
```

### Design Principles

- **SOLID Principles**: Single responsibility, dependency inversion
- **Repository Pattern**: Abstracts data access from business logic
- **Service Layer**: Encapsulates business rules
- **Thin Controllers**: Controllers only handle HTTP concerns
- **Form Requests**: Validation separated from controllers

## Performance Optimizations

| Optimization | Implementation | Benefit |
|--------------|----------------|---------|
| **Database Indexing** | Indexes on `key`, `locale` | O(log n) lookups |
| **Composite Index** | `(key, locale)` unique | Fast export queries |
| **Eager Loading** | `with('tags')` on queries | Prevents N+1 |
| **Batch Inserts** | 1000 records per batch | Efficient seeding |
| **Optimized Export** | Select only needed columns | Reduced memory |

### Performance Targets

- All CRUD endpoints: < 200ms
- Export endpoint (100k records): < 500ms

## Security

- **Token Authentication**: Laravel Sanctum for API tokens
- **Password Hashing**: Bcrypt with configurable rounds
- **Input Validation**: Form requests validate all inputs
- **SQL Injection Prevention**: Eloquent ORM parameterized queries
- **Mass Assignment Protection**: Fillable attributes defined

## Project Structure

```
translation-service/
├── app/                     # Application code
├── bootstrap/               # Framework bootstrap
├── config/                  # Configuration files
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── docker/                  # Docker configuration
│   ├── nginx/               # Nginx config
│   └── php/                 # PHP config
├── public/                  # Public assets
├── routes/                  # Route definitions
├── tests/
│   ├── Feature/             # Feature tests
│   │   ├── Api/             # API endpoint tests
│   │   └── Performance/     # Performance tests
│   └── Unit/                # Unit tests
│       ├── Repositories/    # Repository tests
│       └── Services/        # Service tests
├── docker-compose.yml       # Docker compose config
├── Dockerfile               # Docker image config
└── phpunit.xml              # PHPUnit configuration
```

## License

MIT License
