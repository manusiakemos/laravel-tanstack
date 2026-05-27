# Project Decisions Log

Significant decisions about direction, format, content, approach, or strategy.
Append new entries at the top. Never contradict a logged decision without flagging it first.

---

## 2026-05-27, English-only for all project content
**What was decided:** All guides, documentation, code, comments, identifiers, commit messages, PR descriptions, error messages, and example strings in this repo must be written in English. This applies to `README.md`, `CHANGELOG.md`, `MEMORY.md`, `ERRORS.md`, source under `src/`, tests under `tests/`, config files, and any future docs.
**Why:** The package is published publicly on Packagist and targets an international audience. Mixed-language content (e.g. Indonesian example strings like "Aktif"/"Nonaktif", "Nama") fragments the reading experience for non-Indonesian-speaking users and signals lower polish.
**What was rejected:** Allowing Indonesian in example strings or local-flavored docs. Rejected because consistency matters more than localization in a library README — localization belongs in the consuming app, not the package docs.

## 2026-05-27, Established decision log + error log workflow
**What was decided:** Maintain `MEMORY.md` (this file) for significant decisions and `ERRORS.md` for approaches that took >2 attempts. Read `MEMORY.md` at the start of every session. End every coding task with a short status update (files changed, what was modified, intentionally untouched, follow-up).
**Why:** User wants persistent context across sessions to avoid re-litigating decisions and to skip approaches already known to fail.
**What was rejected:** Relying solely on the auto-memory system at `~/.claude/projects/.../memory/`. Rejected because the user wants the log to live in-repo, visible alongside the code and reviewable in PRs.
