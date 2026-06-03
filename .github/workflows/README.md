# CI/CD Настройка

## Обзор

Этот workflow автоматизирует проверку качества кода и деплой на сервер `139.100.224.56`.

## Триггеры

- **Pull Request** → только проверки качества
- **Push в develop** → только проверки качества
- **Push в main/master** → проверки + автоматический деплой

## Что проверяется

1. Сборка Docker образов
2. Установка зависимостей
3. Миграции БД
4. Laravel Pint (PSR-12 стиль)
5. PHPStan (статический анализ)
6. PHPUnit тесты

## Необходимые настройки в GitHub

### 1. Добавь Secrets в репозиторий

Перейди в: **Settings → Secrets and variables → Actions**

| Secret | Описание |
|--------|----------|
| `SSH_PRIVATE_KEY` | Приватный SSH ключ для доступа к серверу |
| `SSH_USER` | Имя пользователя SSH (обычно `root` или `ubuntu`) |

### 2. Как получить SSH ключ

На твоём сервере (139.100.224.56) выполни:

```bash
# Сгенерируй ключ для GitHub Actions (если нужно)
ssh-keygen -t ed25519 -C "github-actions" -f /root/.ssh/github_actions

# Покажи приватный ключ (скопируй в GitHub Secret)
cat /root/.ssh/github_actions

# Добавь публичный ключ в authorized_keys
cat /root/.ssh/github_actions.pub >> /root/.ssh/authorized_keys
```

### 3. Структура на сервере

Убедись, что на сервере есть:

```
/var/www/restaurant-booking/    # Git репозиторий проекта
├── docker/
├── docker-compose.yml
├── Makefile
└── src/
```

Инициализация:

```bash
ssh root@139.100.224.56
mkdir -p /var/www/restaurant-booking
cd /var/www/restaurant-booking
git clone <твой-репозиторий> .
```

### 4. Доступ Docker без sudo (опционально)

```bash
usermod -aG docker $USER
newgrp docker
```

## Ручной деплой (если CI/CD не работает)

```bash
ssh root@139.100.224.56
cd /var/www/restaurant-booking
git pull
docker-compose down
docker-compose up -d --build
docker-compose exec php php artisan migrate --force
```

## Устранение неполадок

**Проблема**: `Permission denied (publickey)`
- Проверь что ключ добавлен в `authorized_keys`
- Проверь права: `chmod 600 ~/.ssh/authorized_keys`

**Проблема**: `docker-compose: command not found`
- Установи Docker Compose на сервере

**Проблема**: Порт занят
- Проверь что на сервере нет других контейнеров на порту 8080
