# Restaurant Booking System

Система бронирования столиков в ресторане на Laravel 13 с Docker.

## Требования

- Docker
- Docker Compose
- Make (опционально, для удобства)

## Установка

```bash
# Клонировать репозиторий
git clone <repository-url>
cd restaurant-booking-system

# Полная установка (сборка, запуск, миграции)
make install

# Или по шагам:
make build          # Сборка Docker образов
make up             # Запуск контейнеров
make composer-update # Установка зависимостей
make key            # Генерация ключа
make migrate        # Миграции базы данных
```

## Доступные сервисы

- **Приложение**: http://localhost:8080
- **PostgreSQL**: localhost:5433
  - База: restaurant_booking
  - Пользователь: restaurant_booking
  - Пароль: secret
- **Redis**: localhost:6380
- **MinIO**: localhost:9002 (API), localhost:9003 (Console)
  - Access Key: admin
  - Secret Key: password123
- **Mailpit**: localhost:8026 (UI), localhost:1026 (SMTP)

## Команды Make

### Развертывание
```bash
make install      # Полная установка
make build        # Сборка образов
make up           # Запуск контейнеров
make down         # Остановка контейнеров
make restart      # Перезапуск
```

### База данных
```bash
make migrate          # Миграции
make migrate-fresh    # Сброс и пересоздание с сидерами
make migrate-rollback # Откат последней миграции
```

### Качество кода
```bash
make lint         # Laravel Pint проверка
make lint-fix     # Исправление стиля
make analyse      # PHPStan статический анализ
make md           # PHPMD проверка качества
make quality      # Все проверки вместе
```

### Тестирование
```bash
make test            # Запуск тестов
make test-coverage   # Тесты с покрытием
```

### Разработка
```bash
make shell       # Shell в PHP контейнере
make php         # PHP команда (make php ARGS="artisan route:list")
make artisan     # Artisan команда (make artisan ARGS="route:list")
make logs        # Логи всех контейнеров
```

### Frontend
```bash
make npm-install # Установка NPM зависимостей
make npm-build   # Сборка фронтенда
make npm-dev     # Dev сервер
make npm-lint    # ESLint проверка
make npm-format  # Prettier форматирование
```

### Очистка
```bash
make clean    # Удаление контейнеров и volumes
make rebuild  # Полная пересборка
```

## Качество кода

### PHP
- **Laravel Pint** - форматтер кода PSR-12
- **PHPStan** - статический анализ (level 5)
- **PHPMD** - проверка качества кода

### JavaScript
- **ESLint** - линтер JavaScript
- **Prettier** - форматтер кода

## Отладка

Xdebug уже установлен в PHP контейнере. Настройте IDE для подключения к контейнеру:

- Host: localhost
- Port: 9003
- Path mappings: `/var/www` -> локальный путь к `src/`

## Структура проекта

```
restaurant-booking-system/
├── docker/           # Docker конфигурации
│   ├── nginx/       # Nginx конфиг
│   └── php/         # PHP Dockerfile и настройки
├── src/             # Laravel приложение
│   ├── app/         # Код приложения
│   ├── config/      # Конфигурации
│   ├── database/    # Миграции и сидеры
│   ├── resources/   # Views, assets
│   └── routes/      # Роуты
├── docker-compose.yml
├── Makefile
└── README.md
```

## Лицензия

MIT