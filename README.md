# 💰 Balance Service

Сервис для управления балансом пользователей на **Laravel** с интеграцией через Kafka.

## 🎯 Что делает сервис

- Управление балансом пользователей (пополнение, списание, переводы)
- Проверка баланса перед операциями
- Интеграция с order-service через Kafka команды
- Отправка событий о транзакциях в Kafka

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

## Тестирование

```bash
# Запуск всех тестов
docker compose exec app php artisan test

# Запуск конкретного теста
docker compose exec app php artisan test tests/Feature/BalanceApiTest.php
```

## Swagger документация

```bash
# Генерация Swagger документации
docker compose exec app php artisan l5-swagger:generate

# Доступ к документаци
# http://localhost:8080/api/documentation
```
## 📋 API Endpoints

### Пополнение баланса

**POST** `http://localhost:8080/api/deposit`

```json
{
    "user_id": 1,
    "amount": 1000.00
}
```

**Ответ:**
```json
{
    "success": true,
    "user_id": 1,
    "balance": 1000.00,
    "message": "Deposit successful"
}
```

### Получение баланса

**GET** `http://localhost:8080/api/balance/{userId}`

**Ответ:**
```json
{
    "success": true,
    "user_id": 1,
    "balance": 1000.00
}
```

### Списывание средств

**POST** `http://localhost:8080/api/withdraw`

```json
{
    "user_id": 1,
    "amount": 200.00
}
```

### Перевод между пользователями

**POST** `http://localhost:8080/api/transfer`

```json
{
    "from_user_id": 1,
    "to_user_id": 2,
    "amount": 100.00
}
```

## 🔗 Интеграция через Kafka

Сервис получает команды через Kafka топик `balance-commands`:

- **check_balance** - проверка баланса пользователя
- **withdraw** - списание средств с баланса

**Обработка команд:**
```bash
# Запуск consumer для обработки команд из Kafka
docker compose exec app php artisan kafka:consume
```

Сервис отправляет события в Kafka топик `balance-events`:

- `balance_deposited` - событие о пополнении баланса
- `balance_withdrawn` - событие о списании средств
- `balance_transferred` - событие о переводе

**Пример события:**
```json
{
    "type": "balance_deposited",
    "user_id": 1,
    "amount": 500.00,
    "new_balance": 1500.00,
    "transaction_id": 123,
    "timestamp": "2024-11-23T14:30:00+00:00"
}
```

## 📊 Интеграция с Elasticsearch

Все логи автоматически отправляются в Elasticsearch:

- Индекс: `microservices-logs`
- Поле `service`: `balance-service`
- Доступны через Kibana: http://localhost:5601

**Настройка:**
- `ELASTICSEARCH_HOST` - адрес Elasticsearch (по умолчанию: http://elasticsearch:9200)
- `ELASTICSEARCH_INDEX` - имя индекса (по умолчанию: microservices-logs)

## 📖 Примеры использования

См. подробные примеры в `/examples/POSTMAN_GUIDE.md`

## Запуск phpstan и cs-fixer

```bash
# cs-fixer
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run --diff

# phpstan
vendor/bin/phpstan analyse --configuration=phpstan.neon
```
