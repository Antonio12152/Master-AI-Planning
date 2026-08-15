# Master AI Planning

A small Laravel + React app for managing plans, ideas, and collaborative planning workflows.

## Stack
- Laravel
- React + Inertia
- Vite
- Tailwind CSS
- SQLite by default

## Run locally
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
npm run dev
```

Then open http://localhost:8000
Try https://master-ai-planning.fly.dev/
## Test
```bash
php artisan test
```
