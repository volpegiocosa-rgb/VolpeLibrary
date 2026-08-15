# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

VolpeLibrary is a home board-game catalog ("Libreria Volpe Giocosa") — a small, static search page over a hardcoded game list, plus in-progress Python tooling to enrich that list with data from BoardGameGeek (BGG).

## Repository layout

- `legacy/` — the original static site.
  - `game_library-1.js` — the entire data store: a single `const gamelist = [...]` array of ~571 plain objects, each `{ "Gioco": "<title>", "Posizione": "<shelf location>" }`. There is no database and no JSON file; this JS file *is* the data.
  - `catalogo.html` / `catalogo.php` — byte-identical page bodies. `catalogo.php` only prepends three `header()` calls to disable HTTP caching; it contains no PHP logic, no routes, no `$_GET`/`$_POST` handling. Treat both as the same static page.
  - Inline `<script>` in the HTML/PHP implements client-side search: Levenshtein distance against `Gioco` (spaces/punctuation stripped, substring matches forced to distance 1), sorted results, 20-per-page pagination with 🟢/🟠/🔴 relevance icons.
  - `libreria-3.tgz` — just the page's JPEG image assets, not code or data.
  - There is no build tooling, bundler, or package.json for this static site — plain `<script src=...>` includes.

## Data model notes

- Existing fields (`Gioco`, `Posizione`) are Italian, capitalized, no underscores. `Posizione` is a manually maintained physical shelf/box location (e.g. `Cubo 12`, `Libreria alta 3`, `Sopra Kallax`) with no BGG equivalent — never treat it as something to fetch or validate against an external API.
- Known data-quality issues in `game_library-1.js` to be aware of when writing migration/enrichment code: a typo `"Linbreia alta 4"` and a trailing-space variant `"Cubo 30 "`.
- No `bgg_id` or other external-API fields exist yet in the legacy data — any BGG integration is new, additive work (e.g. `giocatori_min`, `giocatori_max`, `durata`, `peso`/complexity, `bgg_id`), matched onto games by title.

## BoardGameGeek (BGG) API integration

BGG's XML API2 (`https://boardgamegeek.com/xmlapi2/search` and `/thing`) now requires registration and an authorization token sent as `Authorization: Bearer <token>` — this is a policy change from the historically unauthenticated API, so don't assume old unauthenticated examples still work. Registration/token approval is a manual, external step (not something to automate in code); read the token from an environment variable rather than hardcoding it.
