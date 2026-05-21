-- Allied programs: directed adjacency used by the defense scheduler's panel-2 eligibility
-- (hard constraint 6). A team whose program is `program_id` may seat, as panelist 2, faculty
-- whose program is `allied_program_id`. Directed on purpose: "CS allows Math" does not imply
-- "Math allows CS". A program is always implicitly allied with itself (the scheduler treats
-- same-program as eligible without a self-row), so self-edges are unnecessary.
--
-- NOTE: `programs` is MyISAM (no foreign-key support) and `programs.id` is signed int(11), so
-- this table carries no FK constraints; referential integrity is enforced in application code
-- (loadAlliedPrograms() joins on programs.id and silently drops orphans). Column types match
-- programs.id exactly. Safe to re-run.

CREATE TABLE IF NOT EXISTS allied_programs (
    program_id        INT(11) NOT NULL,
    allied_program_id INT(11) NOT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (program_id, allied_program_id),
    KEY idx_allied_lookup (program_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
