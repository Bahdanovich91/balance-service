# Balance Service

Приложение для работы с балансом пользователей на Laravel.

## 🚀 Быстрый старт

### Автоматическая установка (рекомендуется)

```bash
# Запуск скрипта автоматической настройки
./setup.sh
```

Скрипт автоматически:
- Проверит наличие Laravel
- Запустит Docker контейнеры
- Установит зависимости
- Настроит базу данных PostgreSQL
- Сгенерирует ключ приложения
- Запустит миграции

### Ручная установка

```bash
# 1. Запуск контейнеров
docker compose up -d

# 2. Установка зависимостей
docker compose exec app composer install

# 3. Настройка .env
docker compose exec app cp .env.example .env
docker compose exec app sed -i 's/DB_CONNECTION=/DB_CONNECTION=pgsql/' .env
docker compose exec app sed -i 's/# DB_HOST=/DB_HOST=postgres/' .env
docker compose exec app sed -i 's/# DB_PORT=/DB_PORT=5432/' .env
docker compose exec app sed -i 's/# DB_DATABASE=/DB_DATABASE=balance_service/' .env
docker compose exec app sed -i 's/# DB_USERNAME=/DB_USERNAME=balance_user/' .env
docker compose exec app sed -i 's/# DB_PASSWORD=/DB_PASSWORD=balance_password/' .env

# 4. Генерация ключа и миграции
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### Проверка работы

Откройте браузер и перейдите по адресу: http://localhost:8080

## 🛠 Полезные команды

### Работа с контейнерами

```bash
# Остановка всех контейнеров
docker compose down

# Пересборка контейнеров
docker compose build --no-cache

# Вход в контейнер приложения
docker compose exec app bash

# Просмотр логов конкретного сервиса
docker compose logs app
docker compose logs postgres
docker compose logs nginx
```

### Запуск phpstan и cs-fixer

```bash
# cs-fixer
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run --diff

# phpstan
vendor/bin/phpstan analyse --configuration=phpstan.neon
```

### Работа с Laravel

```bash
# Создание миграции
docker compose exec app php artisan make:migration create_transactions_table

# Создание модели
docker compose exec app php artisan make:model Transaction

# Создание контроллера
docker compose exec app php artisan make:controller Api/BalanceController

# Запуск миграций
docker compose exec app php artisan migrate

# Откат миграций
docker compose exec app php artisan migrate:rollback

# Создание сидеров
docker compose exec app php artisan make:seeder UserSeeder
```

### Тестирование

```bash
# Запуск тестов
docker compose exec app php artisan test

# Создание теста
docker compose exec app php artisan make:test BalanceTest
```

## 📁 Структура проекта

```
balance-service/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── local.ini
├── docker-compose.yml
├── Dockerfile
├── setup.sh
└── README.md
```

## 🐛 Решение проблем

### Контейнер не запускается
```bash
# Проверка статуса контейнеров
docker compose ps

# Просмотр логов
docker compose logs
```
