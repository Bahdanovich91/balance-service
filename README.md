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

## API Endpoints

### 1. Зачисление средств
```http
POST /api/deposit
Content-Type: application/json

{
    "user_id": 1,
    "amount": 500.00,
    "comment": "Пополнение через карту"
}
```

### 2. Списание средств
```http
POST /api/withdraw
Content-Type: application/json

{
    "user_id": 1,
    "amount": 200.00,
    "comment": "Покупка подписки"
}
```

### 3. Перевод между пользователями
```http
POST /api/transfer
Content-Type: application/json

{
    "from_user_id": 1,
    "to_user_id": 2,
    "amount": 150.00,
    "comment": "Перевод другу"
}
```

### 4. Получение баланса
```http
GET /api/balance/{user_id}
```


## Тестирование

```bash
# Запуск всех тестов
docker compose exec app php artisan test

# Запуск с покрытием
docker compose exec app php artisan test --coverage

# Запуск конкретного теста
docker compose exec app php artisan test tests/Feature/BalanceApiTest.php
```
## Запуск phpstan и cs-fixer

```bash
# cs-fixer
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run --diff

# phpstan
vendor/bin/phpstan analyse --configuration=phpstan.neon
```
