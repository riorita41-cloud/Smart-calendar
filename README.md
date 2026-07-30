# 🎓 Умный Календарь (Smart Calendar)

> **Интеллектуальный помощник с элементами геймификации, который превращает хаотичную подготовку к экзаменам в понятный, управляемый процесс.**

<div align="center">
  <img src="https://img.shields.io/badge/Symfony-6.x-green?style=for-the-badge&logo=symfony" alt="Symfony" />
  <img src="https://img.shields.io/badge/PHP-8.x-blue?style=for-the-badge&logo=php" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-8.0-orange?style=for-the-badge&logo=mysql" alt="MySQL" />
</div>

<br/>

## 🚀 О проекте

«Умный Календарь» решает три главные боли студента перед сессией:
1.  **Систематичность:** Алгоритм сам распределяет вопросы, исключая зубрежку в последнюю ночь.
2.  **Мотивация:** Система XP, уровней и серий дней превращает учебу в прокачку персонажа.
3.  **Фокус:** Все инструменты (расписание, таймер Pomodoro, материалы) собраны в одном месте.

<div align="center">
  <img src="https://github.com/user-attachments/assets/65f6cf5f-c6d8-4ea3-8cc0-3561a9ee75d0" width="800" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea; display: block;">
  <p><i>Интерфейс дашборда: прогресс, задачи на сегодня и статус аватара</i></p>
</div>

---

##  Ключевые возможности

###  Умное расписание
Загрузите список вопросов и укажите дату экзамена. Алгоритм равномерно распределит нагрузку.
*Пример: 100 вопросов за 20 дней = ровно 5 билетов в день без перегрузок.*

<div align="center">
  <img src="https://github.com/user-attachments/assets/ae5c5839-1d66-41aa-9a84-59b4fcd2b18a" width="800" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea; display: block;">
  <p><i>Умное расписание: равномерное распределение вопросов до экзамена</i></p>
</div>

### 🏆 Геймификация и XP
Мгновенная обратная связь за каждое действие:
-   **+30 XP** за выполненный учебный день
-   **+10 XP** за закрытую задачу
-   **+5 XP** за сессию Pomodoro

### ️ Встроенный Pomodoro
Таймер 25/5 интегрирован прямо в интерфейс. Статистика сессий сохраняется и влияет на уровень аватара. Никаких отвлекающих переключений между приложениями.

<div align="center">
  <img src="https://github.com/user-attachments/assets/90a1a5b2-8fe1-46d6-bd86-95eb3fd6d744" width="800" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea; display: block;">
  <p><i>Встроенный таймер Pomodoro и система геймификации XP</i></p>
</div>

---

## 🛠 Технологический стек

| Компонент | Технологии |
| :--- | :--- |
| **Backend** | PHP 8.x, Symfony 6, Doctrine ORM |
| **Frontend** | HTML5, CSS3, JavaScript (ES6+), SVG |
| **Шаблоны** | Twig (наследование, XSS-защита) |
| **База данных** | MySQL |


---

## ️ Архитектура проекта

Проект построен по паттерну **MVC** с четким разделением слоев. Бизнес-логика вынесена в сервисы, что обеспечивает чистоту кода и легкость поддержки.

| Папка | Назначение | Ключевые элементы |
| :--- | :--- | :--- |
| **`src/Controller/`** | Прием HTTP-запросов | `ExamController`, `CalendarController` |
| **`src/Service/`** | Бизнес-логика и алгоритмы | `ScheduleGenerator`, `XpService` |
| **`src/Repository/`** | Абстракция над БД (Doctrine) | `UserRepository`, `TaskRepository` |
| **`src/Entity/`** | Описание сущностей | `User`, `Exam`, `StudyTask`, `XpLog` |
| **`src/Form/`** | Валидация и типы форм | `ExamType`, `RegistrationFormType` |
| **`src/Security/`** | Аутентификация и защита | `AppAuthenticator` |
| **`templates/`** | Генерация HTML (Twig) | `base.html.twig`, дашборд, календарь |
| **`public/`** | Публичные файлы | `index.php`, стили, скрипты, uploads |
| **`config/`** | Конфигурация приложения | Маршруты, security.yaml, doctrine.yaml |
| **`migrations/`** | Версии схемы БД | История изменений структуры таблиц |

### 🔑 Ключевые сервисы

| Сервис | Назначение |
| :--- | :--- |
| **`ScheduleGenerator`** | Алгоритм распределения вопросов по дням до экзамена |
| **`XpService`** | Начисление опыта, управление уровнями и разблокировка контента |
| **`DashboardService`** | Сбор статистики и данных для главной страницы пользователя |

---

## ⚙️ Установка и запуск

### Локальная разработка
```bash
git clone https://github.com/your-username/smart-calendar.git
cd smart-calendar
composer install
cp .env .env.local
# Настройте DATABASE_URL в .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

## ⚙️ Деплой на продакшен
```bash
# 1. Обновляем код из репозитория
git pull origin main

# 2. Устанавливаем зависимости (без dev-пакетов)
composer install --no-dev --optimize-autoloader

# 3. Очищаем и прогреваем кэш для prod-окружения
php bin/console cache:clear --env=prod

# 4. Настраиваем права доступа для веб-сервера
chown -R www-data:www-data var public

# 5. Перезапускаем PHP-FPM и Nginx для применения изменений
systemctl restart php8.2-fpm nginx
```
## 🔒 Безопасность

*   🛡️ **CSRF-токены:** Защита всех форм от подделки запросов.
*   🔐 **Password Hashing:** Пароли хранятся только в виде bcrypt-хешей.
*   👁️ **XSS:** Автоматическое экранирование вывода в Twig.
