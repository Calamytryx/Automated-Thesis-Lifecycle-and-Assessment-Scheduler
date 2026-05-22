-- ============================================================================
-- Pre-Flight Feasibility Matrix — supporting indexes
-- Target engine: MariaDB / MySQL (InnoDB), as used by this project (XAMPP).
--
-- These indexes accelerate the FILTERED queries that feed the pre-flight
-- availability matrix in dashboard/includes/run_scheduler.php:
--   * fetchExistingDefenseSchedules() : WHERE status = 'scheduled'
--   * section/program class-template joins that augment room/user occupancy
--   * user_schedules conflict lookups keyed by user_id + day_of_week
--
-- NOTE on the requested "partial/filtered index": MariaDB/MySQL does NOT support
-- partial (WHERE-clause) indexes — that is a PostgreSQL feature. The equivalent
-- here is a STATUS-LEADING composite index so the optimizer range-scans only the
-- relevant status rows. Also note the real enum is ('scheduled','completed',
-- 'cancelled') — there is no 'confirmed'/'pending' status on this table — so the
-- "active" rows we care about for scheduling are status = 'scheduled'.
--
-- CREATE INDEX IF NOT EXISTS requires MariaDB 10.5+. On MySQL/older MariaDB,
-- drop the "IF NOT EXISTS" and ensure the index does not already exist.
-- ============================================================================

-- 1) Availability lookup by user + day. start_time/end_time are carried in the
--    leaf so user_schedules conflict lookups filtering by user_id/day_of_week can
--    be served from the index (index-only / covering scan) without row fetches.
CREATE INDEX IF NOT EXISTS idx_us_user_day_time
    ON user_schedules (user_id, day_of_week, start_time, end_time);

-- 2) Section/program class-template joins (program + section + day_of_week) used
--    when augmenting the occupancy maps from section timetables.
CREATE INDEX IF NOT EXISTS idx_us_program_section_day
    ON user_schedules (program, section, day_of_week);

-- 3) "Active defenses" composite, status-leading (MySQL/MariaDB partial-index
--    equivalent). Lets `WHERE status = 'scheduled'` resolve as a range on the
--    index prefix, then carry date/room/time for occupancy checks.
CREATE INDEX IF NOT EXISTS idx_ds_status_date_room
    ON defense_schedules (status, schedule_date, room, start_time, end_time);

-- ----------------------------------------------------------------------------
-- PostgreSQL equivalent (for reference only — DO NOT run on MariaDB/MySQL).
-- A true partial index limited to active rows:
--
--   CREATE INDEX idx_ds_active
--       ON defense_schedules (schedule_date, room, start_time, end_time)
--       WHERE status = 'scheduled';
-- ----------------------------------------------------------------------------

-- Verify access paths after applying:
--   EXPLAIN SELECT team_id, room, schedule_date, start_time, end_time
--     FROM defense_schedules WHERE status = 'scheduled';
--   -- expect key = idx_ds_status_date_room, type ref/range (not ALL).
