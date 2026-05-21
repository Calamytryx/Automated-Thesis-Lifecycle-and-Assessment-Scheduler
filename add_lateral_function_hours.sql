-- Per-user working-hours window for the defense scheduler's "With Lateral Functions" setting.
--
-- The `is_parttime` column is reused as the "with lateral functions" flag (UI relabel only; the
-- column name is unchanged because the scheduler, profile, and many SQL dumps reference it).
-- When is_parttime = 1, the scheduler restricts that faculty/admin panelist to the [work_start_time,
-- work_end_time] window: a defense must start no earlier than work_start_time and end no later than
-- work_end_time. Either bound may be NULL (NULL = that side is unbounded); both NULL = no time
-- restriction. This replaces the old hardcoded "part-time cannot be scheduled before 4:00 PM" rule.
--
-- MariaDB supports ADD COLUMN IF NOT EXISTS, so this is safe to re-run.

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS work_start_time TIME NULL DEFAULT NULL AFTER is_parttime,
    ADD COLUMN IF NOT EXISTS work_end_time   TIME NULL DEFAULT NULL AFTER work_start_time;
