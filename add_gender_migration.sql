-- Добавляем колонку gender в таблицу avatar
ALTER TABLE avatar ADD COLUMN gender VARCHAR(20) DEFAULT 'male' AFTER id;