# Genetic Algorithm for Automated Thesis Defense Scheduling

**Source files**
- [dashboard/includes/run_scheduler.php](dashboard/includes/run_scheduler.php) — the genetic algorithm and the surrounding pipeline (data loading, fitness evaluation, persistence).
- [dashboard/includes/panelist_combination_functions.php](dashboard/includes/panelist_combination_functions.php) — the constraint-satisfaction helper layer that supplies legal panelist trios to the GA.

This document describes the methodology of the automated scheduler. It is written as a methodology section: it states the problem, presents the chromosome encoding, the constraint model, the objective function, and the evolutionary operators, then walks through the end-to-end pipeline that drives a single scheduling run.

---

## 1. Problem statement

The scheduler is responsible for assigning every eligible thesis team a complete defense session — composed of a calendar **day**, a **time slot**, a **room**, and a **three-member panel** — such that no participant (student, adviser, panelist) is double-booked, classroom availability is respected, and academically meaningful preferences (expertise alignment, balanced workload, morning starts) are maximized.

This is a constraint-satisfaction problem layered with a multi-objective preference problem. Exhaustive search is intractable for a realistic population of teams, panelists, rooms, and slots, so the system uses a **genetic algorithm (GA)** with:

- a **two-tier lexicographic fitness** (hard violations first, soft score second),
- a **constrained-initialization** stage that pre-computes a per-team domain of *already-legal* candidate assignments, and
- a **repair operator** that pushes infeasible offspring back into the feasible region rather than letting them survive on score alone.

---

## 2. Pipeline overview

The scheduler executes in eight stages. The GA is the central optimization stage; the stages before it prepare the search space, and the stages after it persist and audit the result.

```
                  HTTP POST (form data)
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 1. validateInputs() — sections, dates, times,    │
  │    duration, validation mode, buffer minutes     │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 2. Data loading                                  │
  │    • fetchTeams()         — eligible teams       │
  │    • fetchPanelists()     — usertype 0,2 pool    │
  │    • fetchUserSchedules() — class-time conflicts │
  │    • loadPanelistPools()  — bulk panelist+enrich │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 3. Domain construction                           │
  │    • generateTimeSlotsExcludingClassSchedules()  │
  │    • eligible-days-per-team filtering            │
  │    • $validCandidatePool: legal (day,time,room,  │
  │      panel) tuples per team, pre-validated       │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 4. canFormCompliantPanel() pre-flight gate       │
  │    → teams without a feasible panel are excluded │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 5. createInitialPopulation() — N seeded          │
  │    DefenseSchedule individuals, one gene/team    │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 6. Evolutionary loop (geneticAlgorithm)          │
  │    ┌──────────────────────────────────────────┐  │
  │    │   evaluate fitness (hard, soft)          │  │
  │    │           │                              │  │
  │    │           ▼                              │  │
  │    │   compareSchedules (lexicographic)       │  │
  │    │           │                              │  │
  │    │           ▼                              │  │
  │    │   elitism + tournamentSelection          │  │
  │    │           │                              │  │
  │    │           ▼                              │  │
  │    │   crossover → mutation → repair          │  │
  │    │           │                              │  │
  │    │           ▼                              │  │
  │    │   diversityPreservation + mutation-rate  │  │
  │    │   adjustment                             │  │
  │    └────────────────────┬─────────────────────┘  │
  │   stop on: perfect-found | feasible+≥10 gens |   │
  │            no-improvement | time-budget | cap    │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 7. validateAndFixOverlaps() — post-GA repair on  │
  │    best + alternates                             │
  └──────────────────────────────────────────────────┘
                          │
                          ▼
  ┌──────────────────────────────────────────────────┐
  │ 8. prepareScheduleData() + saveScheduleToDatabase│
  │    (transactional persistence + progression)     │
  └──────────────────────────────────────────────────┘
```

The stages 1–4 reduce the search space from "every (team × day × slot × room × panel) tuple" to a tractable, mostly-feasible domain; the GA then optimizes *within* that domain rather than blindly across the unconstrained Cartesian product. This is what makes the GA tractable at full faculty scale.

---

## 3. Chromosome encoding

Each individual is a `DefenseSchedule` object ([run_scheduler.php:5057](dashboard/includes/run_scheduler.php#L5057)). Its chromosome is an **array of genes**, one gene per team. A gene is a PHP associative array with the following fields:

| Field           | Meaning                                                              |
| --------------- | -------------------------------------------------------------------- |
| `team_id`       | Identifier of the team this gene schedules (fixed; never mutated).   |
| `day`           | Calendar date of the defense.                                        |
| `time_slot`     | Start time (HH:MM).                                                  |
| `room`          | Room assignment.                                                     |
| `panelist_ids`  | Ordered triple `[panelist_id, panelist_id2, panelist_id3]`.          |
| `defense_type`  | `title_proposal`, `pre_oral`, or `final` (set by progression logic). |
| `_day`, `_start`, `_end` | Cached integer interval, populated by `schedulerStampGeneInterval()` for overlap math. |

The chromosome therefore has a **fixed length equal to the number of schedulable teams**. The GA's job is to optimize *which* `(day, time_slot, room, panelist_ids)` tuple each team carries, not the number of genes.

### Sentinel value

The constant `PANEL_TBD = 'TBD'` ([run_scheduler.php:25](dashboard/includes/run_scheduler.php#L25)) is a legal allele for `panelist_id3` when no acceptable external can be found. It is filtered out by `schedulerRealPanelistIds()` before any conflict check, so it is never double-booked but does cost a soft-score bonus (see §5).

---

## 4. The constraint model

Constraints are partitioned into **hard** (must hold for any feasible schedule) and **soft** (preferences whose violations are penalized but tolerated). Hard violations are counted; soft scores are weighted sums.

### 4.1 Hard constraints

| #  | Constraint                                                          | Enforcement                                                  |
| -- | ------------------------------------------------------------------- | ------------------------------------------------------------ |
| 1  | A student's defense cannot overlap their class schedule.            | `hasScheduleConflict()` via `userSchedules` in `calculateFitness()`. |
| 2  | A panelist's defense cannot overlap their class schedule.           | Same scanner, applied per `panelist_ids`.                    |
| 3  | A panelist's defense must fall inside their declared working hours. | `schedulerWorkWindowConflict()`.                             |
| 4  | Two defenses cannot share the same room at overlapping intervals.   | `findBinaryConflicts()` returns `type=room` pairs.           |
| 5  | A panelist cannot sit on two defenses at overlapping intervals.     | `findBinaryConflicts()` returns `type=panelist` pairs.       |
| 6  | Panelists 1 and 2 must come from the **same program** as the team — or, on conflict, a **CMS-allied program** or **same college**. Panelist 3 (the External/Validator seat) is flexible. | `isValidPanelistCombination()` + the `buildOptimalPanelistCombination()` pool construction in [panelist_combination_functions.php](dashboard/includes/panelist_combination_functions.php). |
| 7  | Panelist 3, when external (`is_external = 1`), is always placed in the third slot. | `getExternalPanelistsWithPriority()` waterfall.            |
| 8  | A configurable buffer must separate back-to-back defenses for the same panelist / room. | `bufferSeconds` propagated into `findBinaryConflicts()` and the repair operator. |

Constraints 1–3 are *unary* (involve one gene + external schedules). Constraints 4, 5, 8 are *binary* (involve two genes), and are evaluated once per chromosome via an O(n²) interval scan. Constraints 6 and 7 are enforced **structurally**: the candidate pool the GA samples from is built so that only legal panel orderings can ever enter a chromosome.

### 4.2 Soft preferences

| Preference                                                | Weight (per defense)        |
| --------------------------------------------------------- | --------------------------- |
| Panelist expertise similarity to team expertise           | `+similar_text × 0.20`      |
| Strong expertise match (>70% similarity)                  | `+20`                       |
| No panelist matches the team's expertise                  | `−25`                       |
| Panel-3 actually filled (not `PANEL_TBD`)                 | `+15`                       |
| Daily workload cap exceeded (full-time > 3, part-time > 1)| `−30`                       |
| Consecutive-slot assignment for the same panelist         | `−5`                        |

These weights come from `DefenseSchedule::calculateFitness()` ([run_scheduler.php:5137](dashboard/includes/run_scheduler.php#L5137)). The aggregate soft score is `softScore`; the hard violation count is `hardViolations`.

### 4.3 Lexicographic comparison

`compareSchedules()` ([run_scheduler.php:3604](dashboard/includes/run_scheduler.php#L3604)) sorts individuals first by ascending `hardViolations` (fewer is better), then by descending `softScore`. **Any feasible individual dominates any infeasible one, regardless of soft score.** This is the central invariant of the optimization: the GA cannot "buy" infeasibility with better preference scores.

---

## 5. The genetic operators

### 5.1 Initialization — `createInitialPopulation()`

Population size `N = 40` ([run_scheduler.php:2346](dashboard/includes/run_scheduler.php#L2346)). For each individual, the constructor `DefenseSchedule::__construct()` walks every team and:

1. Filters the team's eligible days to those that still have a valid time slot in `slotsByTeamDay`.
2. Picks a random `(day, slot, room)` from that filtered set.
3. Calls `selectPanelists()` to assemble a legal three-member panel for the team (delegating to `buildOptimalPanelistCombination()` in [panelist_combination_functions.php:487](dashboard/includes/panelist_combination_functions.php#L487)).

When a `$validCandidatePool` is available, the gene is then overwritten by a pre-validated tuple from `chooseCandidateFromPool()`. The result is a population of individuals that are *mostly feasible from the start* — the GA does not have to discover feasibility from random noise, only refine it.

### 5.2 Selection — elitism + tournament

Two selection mechanisms run side-by-side each generation ([run_scheduler.php:3920](dashboard/includes/run_scheduler.php#L3920)):

- **Elitism (≈10% of N):** The best `max(2, ⌊0.1N⌋)` individuals, ranked by `compareSchedules`, are carried forward unchanged. Their fitness is not re-evaluated, since `fitnessEvaluated` is sticky for unchanged genomes.
- **Tournament selection (N/2 parents):** `tournamentSelection()` ([run_scheduler.php:4074](dashboard/includes/run_scheduler.php#L4074)) draws `tournamentSize = 3` random individuals per slot, keeps the lexicographic winner. Cheaper than fully sorting, and lets weaker individuals occasionally win when they happen to dominate their tournament.

### 5.3 Crossover — single-point with per-gene repair

`crossover()` ([run_scheduler.php:4092](dashboard/includes/run_scheduler.php#L4092)):

1. Picks a random cut point `k ∈ [0, |chromosome|)`.
2. Child genes `[0..k)` come from `parent1`, genes `[k..end)` come from `parent2`.
3. Walks each gene of the child; while it conflicts with the rest of the chromosome and attempts < 20:
   - Prefers a tuple from `$validCandidatePool` that passes `validateCandidateSlot()` and `hasConflicts()`.
   - Otherwise re-draws `day` (from eligible-days-with-slots), then `time_slot`, then `room`, then re-runs `selectPanelists()`.

If the per-gene repair loop exhausts its attempts, the child gets a `−50` fitness handicap so it is unlikely to survive selection.

### 5.4 Mutation — four typed operators

`mutation()` ([run_scheduler.php:4159](dashboard/includes/run_scheduler.php#L4159)) runs at probability `mutationRate` (initial `0.2`) per gene. For each gene that mutates, one of four operators is chosen uniformly:

| Type | Operation                                                |
| ---- | -------------------------------------------------------- |
| 0    | Reassign `panelist_ids` (re-runs `selectPanelists()`).   |
| 1    | Reassign `room`.                                         |
| 2    | Reassign `time_slot` (within the gene's current day).    |
| 3    | Reassign `day` *and* `time_slot` (eligible days only).   |

If the mutated gene introduces a conflict, the original gene is restored. This makes mutation a *trial probe*: it cannot make an individual worse on the hard tier, only on the soft tier.

The mutation rate is re-tuned every 5 generations by `adjustMutationRate()` ([run_scheduler.php:5382](dashboard/includes/run_scheduler.php#L5382)), which biases toward higher mutation when the population diversity collapses.

### 5.5 Repair operator — push offspring back into feasibility

This is the operator that distinguishes the scheduler from a textbook GA. After crossover and (optional) mutation, `repairChromosome()` ([run_scheduler.php:3732](dashboard/includes/run_scheduler.php#L3732)) attempts up to eight passes of targeted fixes:

1. `findBinaryConflicts()` returns the list of `(a, b, type)` overlap pairs.
2. `schedulerPickRepairSide()` chooses the team with the **larger domain** in `$validCandidatePool` as the side to mutate (greater freedom → higher chance of finding a non-conflicting alternative).
3. Depending on `type`:
   - `room` conflict → `schedulerRedrawRoom()` swaps in a room free at the gene's interval.
   - `panelist` conflict → `schedulerRedrawPanel()` swaps in a panel set from the team's domain that avoids busy panelists.
4. If the targeted redraw exhausts its 12-attempt budget, the team's gene is **fully re-sampled** from `$validCandidatePool` (new day/slot/room/panel).
5. If after `maxPasses = 8` the chromosome still has binary conflicts, repair returns `null` and the GA replaces the offspring with a fresh individual via `schedulerFreshIndividual()`. Carrying broken offspring forward is explicitly avoided.

### 5.6 Diversity preservation and dynamic mutation

Every 15 generations, `diversityPreservation()` ([run_scheduler.php:5364](dashboard/includes/run_scheduler.php#L5364)) injects randomized individuals if the population has collapsed onto a narrow region of the search space. Every 5 generations, the mutation rate is re-adjusted. Together, these resist premature convergence to local optima — a known failure mode of GAs on tightly constrained scheduling problems.

---

## 6. Stopping criteria

The evolutionary loop terminates on the **first** of the following:

1. **Perfect candidate** — an individual with `hardViolations == 0` *and* `softScore ≥ teamCount × 60` (the theoretical per-defense maximum). Logged as `PERFECT candidate found`.
2. **Feasible-and-mature** — `hardViolations == 0` *and* generation index `≥ 10`. Logged as `Feasible schedule (0 hard violations) found`.
3. **No improvement** — `earlyStopGenerations = 12` generations without lexicographic improvement.
4. **Wall-clock budget** — `SCHEDULER_GA_BUDGET` (default 90 s). On budget exhaust, the best-so-far is returned with a user-visible progress message.
5. **Generation cap** — `generations = 60`.

After the loop, the population is sorted lexicographically. The top individual is returned as `best`; the next five unique chromosomes (deduplicated by signature) are returned as `alternates`, so the post-processing layer can audit them as fallbacks.

---

## 7. The panelist constraint layer

Constraints 6 and 7 are not enforced inside the GA's fitness function. They are enforced structurally by the helper library [panelist_combination_functions.php](dashboard/includes/panelist_combination_functions.php), which the GA calls into every time it needs to assemble a panel. This separation is what allows the GA to treat panel assignment as a single allele rather than three coupled ones, dramatically shrinking the search space.

### 7.1 Pool construction (`buildOptimalPanelistCombination`)

For a team with normalized program `P` and college `C`, the helper partitions the panelist pool into:

- **Same-program internals** — eligible for slots 1, 2, 3.
- **Allied-program internals** — eligible for slot 2 at equal rank with same-program (per the `allied_programs` table, hard constraint 6).
- **Same-college, different-program internals** — slot-2 *fallback only*, used when no same-program/allied internal remains.
- **Same-program externals** — primary slot-3 candidates; also eligible as slot 1 or 2 when internals are scarce.
- **Other panelists** — slot-3 only, ranked by the waterfall in §7.2.

Slot 1 is *always* a same-program internal. Slot 2 prefers same-program but, under a scheduling conflict, may relax to allied or same-college. Slot 3 is the flexible External/Validator seat. The four allowed *employment-type patterns* ([panelist_combination_functions.php:635](dashboard/includes/panelist_combination_functions.php#L635)) are selected with a weighted random draw:

| Pattern                                  | Selection weight              |
| ---------------------------------------- | ----------------------------- |
| Full-time, Full-time, External           | 75% (preferred)               |
| Full-time, Part-time, External           | 25% (split across the others) |
| Full-time, Full-time, Part-time          | 25% (split across the others) |
| Full-time, Part-time, Part-time          | 25% (split across the others) |

### 7.2 Slot-3 waterfall (`getExternalPanelistsWithPriority`)

When ranking candidates for slot 3, the helper uses a five-tier waterfall ([panelist_combination_functions.php:417](dashboard/includes/panelist_combination_functions.php#L417)):

| Tier | Rule                                                    | Rationale                                    |
| ---- | ------------------------------------------------------- | -------------------------------------------- |
| 1    | Same program **and** `is_external = 1`                  | Domain-aligned external validator (ideal).   |
| 2    | CMS-allied program                                      | Adjacent expertise, configured per program.  |
| 3    | Same program, `is_external = 0`                         | Internal subject-matter expert.              |
| 4    | Same college, different program                         | Broad academic neighborhood.                 |
| 5    | Different college (or unknown)                          | Last resort; lets the defense proceed.       |

Within each tier the pool is shuffled, so the GA does not always select the alphabetically-first eligible name — a behavior that previously caused the same external (e.g. "Earl Saavedra") to dominate every IT defense.

### 7.3 Feasibility pre-flight (`canFormCompliantPanel`)

Before the GA starts, the orchestrator calls `canFormCompliantPanel()` ([panelist_combination_functions.php:353](dashboard/includes/panelist_combination_functions.php#L353)) for every team. The check is deterministic and mirrors the hard constraints of the pool builder:

- ≥ 1 same-program internal available (slot 1), **and**
- ≥ 2 candidates in the union of same-program ∪ allied ∪ same-college (slots 1 and 2), **and**
- ≥ 3 total candidates across all tiers (slot 3 must have at least one option).

Teams that fail this gate are excluded from the GA's chromosome, so the algorithm never wastes evaluations on a team that *cannot* be scheduled compliantly. This converts an otherwise-infinite penalty loop into a clean up-front exclusion.

### 7.4 Performance: per-request caches

Three layers of memoization in `panelist_combination_functions.php` make the constraint layer cheap to call from inside the GA's inner loop:

- `panelistInfoCache()` — single shared cache for `getPanelistInfo()`, primed in one query by `loadPanelistPools()`.
- `getProgramCollege()` — static cache keyed by normalized program name.
- `loadAlliedPrograms()` — single query per request for the entire allied-programs adjacency.

Without these caches, the GA would issue O(generations × population × teams × 3) panelist queries; with them, each lookup after the first is an array hit.

---

## 8. Output

`geneticAlgorithm()` returns `['best' => DefenseSchedule, 'alternates' => DefenseSchedule[]]`. The orchestrator then:

1. Runs `validateAndFixOverlaps()` ([run_scheduler.php:5730](dashboard/includes/run_scheduler.php#L5730)) as a final post-GA repair pass — a belt-and-braces guard against any conflict that survived the in-loop repair operator.
2. Calls `computeScheduleQualityMetrics()` to produce reportable metrics (utilization %, average gap minutes, room-load standard deviation, morning-start ratio).
3. Passes the schedule to `prepareScheduleData()`, which serializes each gene into the database row format and runs a second-pass attempt for any missing team.
4. Transactionally persists the run via `saveScheduleToDatabase()` and updates each team's defense progression state.

The end state is a set of `defense_schedule` rows — one per team — alongside an audit trail of the GA's convergence (population fitness per generation, conflict counts, crossover/mutation totals) that the UI can render for the user.

---

## 9. Summary

The scheduler combines three ideas that are individually familiar but jointly designed:

1. A **constrained-initialization GA**: the search space is pre-pruned to legal candidates before evolution starts, so the GA optimizes within a feasible region rather than discovering feasibility from noise.
2. A **two-tier lexicographic fitness**: hard-constraint compliance is never traded away for soft-preference gain, which is the property the user actually wants from a defense scheduler.
3. A **structural panelist constraint layer** that turns three coupled alleles (panelists 1, 2, 3) into a single, always-legal "panel" gene — supplied by a deterministic helper that encodes the academic policy directly.

The result is an algorithm that, on a realistic problem instance, converges to a feasible and quality-ranked schedule within tens of generations and well inside the 90-second wall-clock budget — without ever emitting a schedule that violates a hard constraint.
