# 🎓 Умный Календарь (Smart Calendar)

> **Интеллектуальный помощник с элементами геймификации, который превращает хаотичную подготовку к экзаменам в понятный, управляемый процесс.**
div align="center">
  <img src="https://img.shields.io/badge/Symfony-6.x-green?style=for-the-badge&logo=symfony" alt="Symfony" />
  <img src="https://img.shields.io/badge/PHP-8.x-blue?style=for-the-badge&logo=php" alt="PHP" />
</div>

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

## ✨ Ключевые возможности

### ️ Умное расписание
Загрузите список вопросов и укажите дату экзамена. Алгоритм равномерно распределит нагрузку.
*Пример: 100 вопросов за 20 дней = ровно 5 билетов в день без перегрузок.*

<div align="center">
  <img src="https://github.com/user-attachments/assets/ae5c5839-1d66-41aa-9a84-59b4fcd2b18a" width="800" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea; display: block;">
  <p><i>Умное расписание: равномерное распределение вопросов до экзамена</i></p>

### 🏆 Геймификация и XP
Мгновенная обратная связь за каждое действие:
   **+30 XP** за выполненный учебный день
   **+10 XP** за закрытую задачу
   **+5 XP** за сессию Pomodoro

### ⏱️ Встроенный Pomodoro
Таймер 25/5 интегрирован прямо в интерфейс. Статистика сессий сохраняется и влияет на уровень аватара. Никаких отвлекающих переключений между приложениями.

<div align="center">
  <img src="https://github.com/user-attachments/assets/90a1a5b2-8fe1-46d6-bd86-95eb3fd6d744" width="800" style="border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: 1px solid #eaeaea; display: block;">
  <p><i>Встроенный таймер Pomodoro и система геймификации XP</i></p>
</div>  

---

## 🛠 Технологический стек

| Компонент | Технологии |
| :--- | :--- |
| **Backend** | PHP , Symfony , Doctrine ORM |
| **Frontend** | HTML, CSS, JavaScript, SVG |
| **Шаблоны** | Twig  |
| **База данных** | MySQL |

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
