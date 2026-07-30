# 🎓 Умный Календарь (Smart Calendar)

> **Интеллектуальный помощник с элементами геймификации, который превращает хаотичную подготовку к экзаменам в понятный, управляемый процесс.**

<div align="center">
  <img src="https://img.shields.io/badge/Symfony-6.x-green?style=for-the-badge&logo=symfony" alt="Symfony" />
  <img src="https://img.shields.io/badge/PHP-8.x-blue?style=for-the-badge&logo=php" alt="PHP" />
  <img src="https://img.shields.io/badge/MariaDB-10.4-orange?style=for-the-badge&logo=mariadb" alt="MariaDB" />
  <img src="https://img.shields.io/badge/Twig-Templating-black?style=for-the-badge&logo=twig" alt="Twig" />
</div>

<br/>

## 🚀 О проекте

«Умный Календарь» решает три главные боли студента перед сессией:
1.  **Систематичность:** Алгоритм сам распределяет вопросы, исключая зубрежку в последнюю ночь.
2.  **Мотивация:** Система XP, уровней и серий дней превращает учебу в прокачку персонажа.
3.  **Фокус:** Все инструменты (расписание, таймер Pomodoro, материалы) собраны в одном месте.

<div align="center">
<img width="1890" height="964" alt="111" src="https://github.com/user-attachments/assets/65f6cf5f-c6d8-4ea3-8cc0-3561a9ee75d0" />
  <img src="docs/screenshots/dashboard.png" width="800" alt="Главная страница Умного Календаря" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea;"/>
  <p><i>Интерфейс дашборда: прогресс, задачи на сегодня и статус аватара</i></p>
</div>

---

## ✨ Ключевые возможности

### ️ Умное расписание
Загрузите список вопросов и укажите дату экзамена. Алгоритм равномерно распределит нагрузку.
*Пример: 100 вопросов за 20 дней = ровно 5 билетов в день без перегрузок.*

<div align="center">
  <img width="1888" height="966" alt="222" src="https://github.com/user-attachments/assets/e9f5c8ca-c42a-4846-9382-9dbf7f0ead7d" />
  <img src="docs/screenshots/calendar.png" width="800" alt="Календарь подготовки" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea;"/>
</div>

### 🏆 Геймификация и XP
Мгновенная обратная связь за каждое действие:
-   **+30 XP** за выполненный учебный день
-   **+10 XP** за закрытую задачу
-   **+5 XP** за сессию Pomodoro

### ⏱️ Встроенный Pomodoro
Таймер 25/5 интегрирован прямо в интерфейс. Статистика сессий сохраняется и влияет на уровень аватара. Никаких отвлекающих переключений между приложениями.

<div align="center">
  <img width="1893" height="964" alt="333" src="https://github.com/user-attachments/assets/4d17a791-aa95-4f90-bbef-1080ef02efed" />
  <img src="docs/screenshots/pomodoro-avatar.png" width="800" alt="Таймер и Аватар" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea;"/>
</div>

---

## 🛠 Технологический стек

| Компонент | Технологии |
| :--- | :--- |
| **Backend** | PHP 8.x, Symfony 6, Doctrine ORM |
| **Frontend** | HTML5, CSS3, JavaScript (ES6+), SVG |
| **Шаблоны** | Twig (наследование, XSS-защита) |
| **База данных** | MariaDB 10.4 |
| **Сервер** | Nginx, PHP-FPM |

---

## 🏗️ Архитектура проекта

Проект построен по паттерну **MVC** с четким разделением слоев. Бизнес-логика вынесена в сервисы, что обеспечивает чистоту кода и легкость поддержки.

<details>
<summary><b> Нажми, чтобы посмотреть структуру папок</b></summary>

```text
src/
── Controller/      # Прием запросов и маршрутизация
├── Service/         # Бизнес-логика (ScheduleGenerator, XpService)
── Repository/      # Работа с БД через Doctrine
├── Entity/          # Сущности (User, Exam, Task, XpLog...)
── Form/            # Типы форм и валидация
└── Security/        # Аутентификация и авторизация

templates/           # Twig-шаблоны (base.html.twig + страницы)
public/              # Точка входа, CSS, JS, Uploads
config/              # Маршруты, security.yaml, doctrine.yaml
migrations/          # История изменений схемы БД
