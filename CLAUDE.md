# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

- **Server:** WAMP (Windows) — Apache + PHP 8+ + MySQL 8.4
- **Base URL:** `http://localhost/controle_despesas/`
- **MySQL binary:** `D:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe`
- **Database:** `controle_despesas`, user `root`, no password

## Setup

Run `http://localhost/controle_despesas/setup.php` in a browser to create (or reset) the database schema and seed the default categories. Do **not** pipe `database/schema.sql` through the PowerShell terminal — it corrupts UTF-8 characters. Always use `setup.php` instead.

## Architecture

The app is a thin-server / AJAX-heavy design with no framework:

- **Pages** (`index.php`, `mensal.php`, `semanal.php`, `categorias.php`) are pure HTML shells. They load no data on the server side — all data is fetched via JS after `DOMContentLoaded`.
- **API layer** (`api/*.php`) returns JSON only. Every endpoint starts with `error_reporting(0)` and `header('Content-Type: application/json')`. Reads use `$_GET`; writes use `$_POST` via `FormData`.
- **Database access** is always through `Database::getInstance()` (PDO singleton in `src/Database.php`). The DSN includes `charset=utf8mb4` and `SET NAMES utf8mb4, time_zone='-03:00'` as init command.
- **Shared JS** (`assets/js/app.js`) is loaded on every page and provides `apiGet()`, `apiPost()`, `formatMoeda()`, `toast()`, `abrirModal()`, `fecharModal()`, and modal event wiring.
- **Transaction modal** is a native `<dialog>` element defined in `includes/modal_transacao.php` and included in every page. When the tipo radio changes, it reloads the category `<select>` via AJAX filtered by type.
- **Page-specific JS** (`dashboard.js`, `mensal.js`, `semanal.js`, `categorias.js`) each define a `recarregarDados()` function that `app.js` calls after any save/delete.
- **Period state** (current month or week) is stored in `localStorage` under keys `cd_mes` and `cd_semana`, so navigation persists across page loads.

## API contract

All responses: `{"success": true, "data": ...}` or `{"success": false, "error": "..."}`.

| Endpoint | Method | Key params |
|---|---|---|
| `api/transacoes.php` | GET | `?ano&mes` \| `?ano&semana` \| `?id` |
| `api/transacoes_salvar.php` | POST | `id?`, `tipo`, `descricao`, `valor`, `data`, `id_categoria` |
| `api/transacoes_excluir.php` | POST | `id` |
| `api/categorias.php` | GET | `?tipo=receita\|despesa` (optional filter) |
| `api/categorias_salvar.php` | POST | `id?`, `nome`, `tipo`, `cor` |
| `api/categorias_excluir.php` | POST | `id` (blocked if category has transactions) |
| `api/dashboard.php` | GET | `?ano&mes` — returns `resumo`, `por_categoria`, `por_dia`, `recentes` |

## Database

Two tables:
- `categorias`: `id`, `nome`, `tipo` ENUM(`receita`,`despesa`,`ambos`), `cor` CHAR(7)
- `transacoes`: `id`, `tipo` ENUM(`receita`,`despesa`), `descricao`, `valor` DECIMAL(12,2), `data` DATE, `id_categoria` FK, `observacao`

Weekly queries use `YEARWEEK(data, 3)` (ISO 8601, week starts Monday). Never use FLOAT for monetary values.

## CSS

All styles are in `assets/css/style.css`. Colors are defined as CSS custom properties on `:root` (e.g. `--cor-primaria: #2E7D32`, `--cor-receita`, `--cor-despesa`). The layout is a single centered column (`max-width: 1100px`). Responsive breakpoints at 900px, 768px, and 480px.

## Category color contrast

When rendering category badges, `texto_cor` (`#ffffff` or `#000000`) is computed server-side using luminance: `($r*299 + $g*587 + $b*114) / 1000 < 128`. This field is returned by every API endpoint that includes category data — use it as `style="background:${cor};color:${texto_cor}"`.
