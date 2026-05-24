# studio-logged

Live demo of **[logged-cloud/page-studio](https://github.com/Logged-Cloud/page-studio)** at https://studio.logged.cloud.

A Laravel app that mounts the page-studio package on `/playground`, gives every visitor a session-scoped Page row, and renders the result on `/preview`. Hourly cron prunes abandoned demo pages.

## Stack

- PHP 8.4-fpm, nginx, MySQL 8, Redis
- Laravel 13, Livewire 4, `logged-cloud/page-studio ^2.2`
- Docker Compose · host port **8107** behind shared Caddy at `studio.logged.cloud`

## Local boot

```bash
docker compose build
docker compose up -d
docker compose exec studio-logged-app composer install
docker compose exec studio-logged-app php artisan key:generate
docker compose exec studio-logged-app php artisan migrate
```

Then browse to http://localhost:8107.

## Routes

| Path | Purpose |
| --- | --- |
| `/` | Landing card with the live-demo pitch |
| `/playground` | `<livewire:page-studio.page-builder>` against a session-scoped Page row |
| `/preview` | Renders the session's blocks via `PageRenderer::render()` |
| `POST /reset` | Drops + recreates the session page |

## Updating the package

```bash
docker compose exec studio-logged-app composer update logged-cloud/page-studio
docker compose exec studio-logged-app php artisan migrate --force
```
