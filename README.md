# OmniBill

OmniBill is a modular monolith multi-tenant billing platform.

## Local Development

The project standard is Docker Compose. We do **not** use Laravel Sail.

1. **Spin up the environment:**
   ```bash
   docker compose up -d
   ```
   This provisions:
   - `app` (Web server: `localhost:8000`)
   - `worker` (Horizon queue processor)
   - `pgsql` (PostgreSQL Database)
   - `redis` (Cache, Session, Queues)
   - `mailhog` (SMTP Interceptor: `localhost:8025`)
   - `minio` (S3 Object Storage: `localhost:8900`)

2. **Run migrations:**
   ```bash
   docker compose exec app php artisan migrate
   ```

3. **Run tests:**
   ```bash
   docker compose exec app vendor/bin/pest
   ```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
