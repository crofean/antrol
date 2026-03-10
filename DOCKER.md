# Docker Setup Guide for RSAM Antrol

## Quick Start

### Prerequisites

- Docker and Docker Compose installed on your system
- At least 4GB of available RAM for the containers

### Setup Steps

1. **Clone and Navigate**

    ```bash
    cd /path/to/folder/antrol
    ```

2. **Configure Environment**

    ```bash
    cp .env.docker .env
    ```

3. **Build and Start Containers**

    ```bash
    docker-compose up -d
    ```

4. **Generate Application Key** (if not already set)

    ```bash
    docker-compose exec app php artisan key:generate
    ```

5. **Run Migrations**

    ```bash
    docker-compose exec app php artisan migrate
    ```

6. **Seed Database** (optional)

    ```bash
    docker-compose exec app php artisan db:seed
    ```

7. **Access the Application**
    - Open your browser and go to: `http://localhost:8000`

## Available Services

- **Web App**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

## Common Commands

### Start Containers

```bash
docker-compose up -d
```

### Stop Containers

```bash
docker-compose down
```

### View Logs

```bash
# View all services
docker-compose logs -f

# View specific service
docker-compose logs -f app
docker-compose logs -f mysql
```

### Execute Commands in Container

```bash
# Run artisan commands
docker-compose exec app php artisan tinker
docker-compose exec app php artisan cache:clear

# Run npm commands
docker-compose exec app npm run dev
docker-compose exec app npm run build

# Access container shell
docker-compose exec app sh
```

### Database Commands

```bash
# Access MySQL
docker-compose exec mysql mysql -u antrol_user -p antrol -e "SELECT * FROM table_name;"

# Export database dump
docker-compose exec mysql mysqldump -u antrol_user -p antrol > backup.sql

# Import database dump
docker-compose exec -T mysql mysql -u antrol_user -p antrol < backup.sql
```

## Development Workflow

### Install PHP Dependencies

```bash
docker-compose exec app composer install
```

### Install NPM Dependencies

```bash
docker-compose exec app npm install
```

### Run Frontend Dev Server (Vite)

```bash
docker-compose exec app npm run dev
```

### Build Frontend Assets

```bash
docker-compose exec app npm run build
```

### Run Tests

```bash
docker-compose exec app php artisan test
```

### Check Code Style with Pint

```bash
docker-compose exec app ./vendor/bin/pint
```

## Environment Variables

Key environment variables in `.env`:

- `DB_CONNECTION`: Database driver (mysql)
- `DB_HOST`: MySQL hostname (mysql)
- `DB_DATABASE`: Database name (antrol)
- `DB_USERNAME`: Database user (antrol_user)
- `DB_PASSWORD`: Database password (antrol_password)
- `REDIS_HOST`: Redis hostname (redis)
- `CACHE_DRIVER`: Cache driver (redis)
- `QUEUE_CONNECTION`: Queue driver (redis)

## Volume Management

### Clean Up Volumes

```bash
# Remove all volumes (WARNING: deletes database data)
docker-compose down -v
```

### Backup Database Volume

```bash
docker-compose exec mysql mysqldump -u antrol_user -p antrol > backup.sql
```

## Production Considerations

For production deployment:

1. Set `APP_ENV=production`
2. Set `APP_DEBUG=false`
3. Enable HTTPS with a reverse proxy (nginx/traefik)
4. Use environment-specific database credentials
5. Configure proper backup strategies
6. Monitor container resources and logs
7. Use a registry for Docker images

## Troubleshooting

### Container won't start

```bash
docker-compose logs app
```

### Database connection errors

```bash
# Check if MySQL is running
docker-compose exec mysql mysql -u antrol_user -p antrol -e "SELECT 1;"
```

### Permission issues

```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Port already in use

Modify ports in `docker-compose.yml`:

```yaml
ports:
    - "8080:80" # Change 8000 to 8080 or any available port
```

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com)
- [Docker Compose Documentation](https://docs.docker.com/compose)
