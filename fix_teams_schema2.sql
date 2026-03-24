ALTER TABLE teams
    ADD COLUMN manager_name  VARCHAR(100) NULL AFTER vice_captain_id,
    ADD COLUMN manager_phone VARCHAR(15)  NULL AFTER manager_name,
    ADD COLUMN registered_by INT UNSIGNED NULL AFTER manager_phone;
