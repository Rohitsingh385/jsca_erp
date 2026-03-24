ALTER TABLE teams
    ADD COLUMN jsca_team_id  VARCHAR(30)  NULL AFTER id,
    ADD COLUMN updated_at    DATETIME     NULL;
