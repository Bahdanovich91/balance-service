#!/bin/bash

echo "🚀 Настройка Balance Service..."

# Проверка наличия Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Laravel не найден! Установите Laravel сначала:"
    echo "docker compose exec app composer create-project laravel/laravel . --prefer-dist"
    exit 1
fi

# Запуск контейнеров
echo "🐳 Запуск Docker контейнеров..."
docker compose up -d

# Ожидание запуска PostgreSQL
echo "⏳ Ожидание запуска PostgreSQL..."
sleep 10

# Установка зависимостей
echo "📦 Установка зависимостей..."
docker compose exec app composer install

# Создание .env файла если его нет
if [ ! -f ".env" ]; then
    echo "📝 Создание .env файла..."
    docker compose exec app cp .env.example .env
fi

# Настройка базы данных в .env
echo "🔧 Настройка базы данных..."
docker compose exec app sed -i 's/DB_CONNECTION=/DB_CONNECTION=pgsql/' .env
docker compose exec app sed -i 's/DB_HOST=/DB_HOST=postgres/' .env
docker compose exec app sed -i 's/DB_PORT=/DB_PORT=5432/' .env
docker compose exec app sed -i 's/DB_DATABASE=/DB_DATABASE=balance_service/' .env
docker compose exec app sed -i 's/DB_USERNAME=/DB_USERNAME=balance_user/' .env
docker compose exec app sed -i 's/DB_PASSWORD=/DB_PASSWORD=balance_password/' .env

# Удаление комментариев и лишних строк
docker compose exec app sed -i '/^# DB_/d' .env

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
docker compose exec app php artisan key:generate

# Запуск миграций
echo "🗄️ Запуск миграций..."
docker compose exec app php artisan migrate

echo "✅ Настройка завершена!"
echo ""
echo "Приложение доступно по адресу: http://localhost:8080"
echo ""
echo "Полезные команды:"
echo "docker compose exec app bash  # Вход в контейнер"
echo "docker compose logs -f        # Просмотр логов"
echo "docker compose down           # Остановка контейнеров"
