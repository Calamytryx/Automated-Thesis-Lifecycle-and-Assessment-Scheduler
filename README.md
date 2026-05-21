# ATLAS

Advanced Thesis Logistics and AI System. It's the thesis/defense management system we built for LPU-Cavite, specifically the CoECSA college (Engineering, Computer Studies, Architecture). It handles the whole lifecycle: teams and titles, scheduling defenses without conflicts, running the actual evaluations, and tracking whether a team has its requirements in before they're allowed to defend.

## What it actually does

Three kinds of people log in and each sees a different thing.

**Admin / coordinator** runs the show from the dashboard. They manage users, teams, programs and colleges, research titles, rubrics, panelist and adviser assignments, faculty specializations, and the document requirements per program. They're also the ones who fire off the scheduler and finalize defense schedules.

**Faculty** are advisers and/or panelists. They see the teams they're attached to, check submitted manuscripts, score teams against the rubric during defense, and leave comments.

**Students** see their team, what requirements they still owe, their defense schedule once it's out, and their results/feedback after.

## The scheduling part

This is the bit that's not trivial. Defense scheduling is a genetic algorithm (population, crossover, mutation, fitness scoring — it's all in `dashboard/includes/run_scheduler.php`). You give it the teams, the panelists, the rooms, and a time window, and it tries to fit everyone in without clashing.

It dodges:
- panelists being in two defenses at once
- students' regular class schedules (smart slot generation pulls those out automatically if you hand it a start/end time)
- room double-booking

It doesn't just spit out one answer either — it keeps alternate candidates and ranks them, so if the top pick has unplaced teams you can see the runner-ups. Once you're happy you finalize it and it locks in. Redefense scheduling is supported too.

There's a small Python helper (`process_schedule_data.py`) that just reads the GA metrics dump (generations, conflict counts, etc.) if you want to eyeball how a run went. Not required to run the app.

## The AI part

Gemini does the soft stuff, and it runs client-side through Google's JS SDK (`@google/generative-ai`, currently on `gemini-2.5-flash-lite` — see `assets/js/mainModule.js`). It's used for:
- feedback and sanity-checking on research titles
- suggesting thesis topics
- helping summarize/phrase evaluation feedback

The API key lives in the env variables table, not hardcoded in the app logic, and gets handed to the front end through `assets/js/get_api_key.php`.

## Stack

- PHP 8+, runs on Apache (we develop on XAMPP/LAMPP, hence `/opt/lampp/htdocs`)
- MySQL 8
- Bootstrap + jQuery on the front end, no big framework
- Gemini API for the AI bits
- Python 3 only for the optional scheduler-metrics script

## Getting it running

1. Drop the repo into your web root (`/opt/lampp/htdocs` or wherever Apache serves from).
2. Make a MySQL database and import the schema. The setup SQL is in `assets/setup/` — `MAIN.sql` is the current full one. The `assets/setup/migrations/` and dated SQL files are incremental changes on top.
3. Point it at your DB. Connection settings are in `assets/setup/db.inc.php` and `assets/setup/env.php` — change the host, db name, user, and password to yours. Heads up: there are live-looking credentials committed in there, swap them out, don't trust them.
4. Add your own Gemini API key. Either through the Environment Variables tab in the admin dashboard (`APP_GEMINI_API`) or directly in the env_variables table. Don't reuse the one that's sitting in the sample SQL dumps.
5. Hit the site root. `index.php` bounces you to login.

## Repo notes

The root is messy — lots of `debug_*.php`, `test_*.php`, `check_*.php`, and `.txt` manifests from while we were building. Those are scratch/diagnostic files, not part of the running app. The real stuff lives in:
- `dashboard/` — admin side, all the tabs and the scheduler
- `home/` — student and faculty side
- `decision-support/` — the evaluation / scoring screens
- `api/` — the assignment and requirements endpoints
- `assets/setup/` — DB schema and config

## License

MIT. See [LICENSE](LICENSE).
