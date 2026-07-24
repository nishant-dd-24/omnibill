# OmniBill v1.0

OmniBill is a modular monolith multi-tenant billing platform.

## Local Development

The project standard is Docker and Laravel Sail.

1. **Spin up the environment:**
   ```bash
   ./vendor/bin/sail up -d
   ```
   This provisions the necessary services (web server, database, Redis, Mailhog, MinIO).

2. **Run migrations:**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

3. **Run tests:**
   ```bash
   ./vendor/bin/sail artisan test
   ```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
