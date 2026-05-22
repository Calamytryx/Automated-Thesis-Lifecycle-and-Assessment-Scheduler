-- Add explicit finalization lock metadata for defense schedules.
ALTER TABLE defense_schedules
    ADD COLUMN is_finalized TINYINT(1) NOT NULL DEFAULT 0 AFTER approval_status,
    ADD COLUMN finalized_at DATETIME NULL AFTER is_finalized,
    ADD COLUMN finalized_by INT(11) UNSIGNED NULL AFTER finalized_at;

ALTER TABLE defense_schedules
    ADD INDEX idx_defense_finalized (is_finalized),
    ADD INDEX idx_defense_schedule_time (schedule_date, start_time, end_time),
    ADD INDEX idx_defense_team (team_id);
