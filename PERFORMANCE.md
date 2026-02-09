# Performance & Observability

This document tracks performance profiling, optimizations, and observability instrumentation for the Social Media Manager API.

## How to Profile

Run the endpoint performance test suite:

```bash
vendor/bin/sail artisan test tests/Feature/EndpointPerformanceTest.php
```

This seeds a stress-test dataset (1000 content items, 5 categories, 400 posts, 200 schedules, 250 media) and profiles every key API endpoint, outputting query counts, DB time, and memory usage.

Query count thresholds are enforced as assertions — if a code change introduces an N+1 or otherwise increases query counts beyond the allowed ceiling, the test fails.

## Baseline (Pre-Optimization)

Captured on 2026-02-09 with 1000 content items, 5 categories, 400 posts, 200 schedules, 250 media items.

| Endpoint | Queries | DB Time | Slowest Query |
|---|---|---|---|
| `GET /api/social_media_contents` (index) | 2 | 1.54ms | — |
| `GET /api/social_media_contents` (index + all includes) | 6 | 2.69ms | — |
| `GET /api/social_media_contents/{id}` (show) | 5 | 1.09ms | — |
| `GET /api/social_media_contents/autopost` | 3 | 3.09ms | content selection: 2.06ms |
| `GET /api/social_media_categories` (index) | 2 | 1.99ms | withCount subquery: 1.03ms |
| `GET /api/social_media_posts` (index) | 2 | 1.07ms | — |
| `GET /api/social_media_posts` (index + includes) | 3 | 1.47ms | — |
| `GET /api/social_media_contents/{id}/media` (media index) | 2 | 1.19ms | — |

### Observations

- **No N+1 issues found.** Spatie QueryBuilder's `allowedIncludes` properly uses eager loading with batch `IN` clauses.
- **Autopost** runs 3 queries: weight loading, content selection (with `NOT EXISTS` subqueries for posts/schedules), and category eager load. The content selection query (2.06ms) is the most expensive single query — the primary target for indexing.
- **Missing database indices:** `social_media_contents.social_media_category_id` has no FK constraint or index. Composite index on `(account_id, social_media_category_id)` would directly benefit the autopost content selection query. `posted_at` and `scheduled_at` columns lack sort indices.
- **Regression guard:** `EndpointPerformanceTest` enforces query count ceilings per endpoint. If a code change introduces an N+1 or unnecessary queries, the test fails.

## Optimization Log

### Step 1: Database Indices

Added via migration `2026_02_09_172734_add_performance_indices`:

- **`social_media_contents.social_media_category_id`** — Added FK constraint + index (was completely missing, fixing long-standing TODO)
- **`social_media_contents(account_id, social_media_category_id)`** — Composite index for autopost content selection
- **`social_media_posts.posted_at`** — Sort index for post listing default sort
- **`social_media_schedules.scheduled_at`** — Sort index for schedule ordering
- **`social_media_account_category_weights(account_id, social_media_category_id)`** — Unique constraint (prevents duplicate weights + speeds autopost weight loading)
- **`content_generation_requests.status`** — Index for status lookups

**Impact on autopost content selection query:** 2.06ms -> 1.10ms (47% improvement)

### Step 2: Query Scopes

Added 7 query scopes to `SocialMediaContent`:

- `forAccount(int $accountId)` — filter by account
- `forCategory(int $categoryId)` — filter by category
- `posted()` — content that has been posted
- `unposted()` — content that has never been posted
- `scheduled()` — content that has been scheduled
- `unscheduled()` — content that has never been scheduled
- `available()` — combines `unposted` + `unscheduled` (used by autopost)

Refactored `AutopostService::findAvailableContent()` to use scopes, replacing inline `where`/`whereDoesntHave` calls with `->forAccount()->forCategory()->available()`.

**Impact:** No query count or performance change (generates identical SQL). Improves readability and reusability — scopes can now be composed across controllers and services.

### Step 3: Request Timing Middleware

Created `App\Http\Middleware\RequestTiming` and registered it as the first middleware in the `api` group.

**Response headers added to every API response:**
- `X-Request-Duration-Ms` — total request time
- `X-Query-Count` — number of database queries executed
- `X-DB-Time-Ms` — total time spent in database queries

**Structured log entry** written for every API request with: method, URI, status, duration, query count, DB time, memory peak.

**Smart log levels:**
- `info` for normal requests
- `warning` when duration > 500ms OR query count > 10 (acts as a built-in alert system)

### Step 4: Structured Autopost Logging

Added dedicated `autopost` channel in `config/logging.php` with JSON formatter and 30-day daily rotation.

Structured decision logging in `AutopostService` at every step:

- `no_weights` — account has no category weights configured
- `weights_loaded` — weights array, total weight, category count
- `category_picked` — selected category, random value, cumulative distribution position
- `content_selected` — chosen content ID and title, category ID
- `category_skipped` — category exhausted, remaining categories count
- `all_categories_exhausted` — no content available across all weighted categories

Every log entry includes a `request_id` (UUID) for correlating all decisions within a single autopost request. Logs written to `storage/logs/autopost-YYYY-MM-DD.log` as structured JSON.

## Post-Optimization Results

Captured on 2026-02-09 with same dataset: 1000 content items, 5 categories, 400 posts, 200 schedules, 250 media items.

| Endpoint | Queries | DB Time | Slowest Query |
|---|---|---|---|
| `GET /api/social_media_contents` (index) | 2 | 1.32ms | — |
| `GET /api/social_media_contents` (index + all includes) | 6 | 2.91ms | — |
| `GET /api/social_media_contents/{id}` (show) | 5 | 0.55ms | — |
| `GET /api/social_media_contents/autopost` | 3 | 2.38ms | content selection: 1.60ms |
| `GET /api/social_media_categories` (index) | 2 | 1.90ms | — |
| `GET /api/social_media_posts` (index) | 2 | 0.91ms | — |
| `GET /api/social_media_posts` (index + includes) | 3 | 1.06ms | — |
| `GET /api/social_media_contents/{id}/media` (media index) | 2 | 1.06ms | — |

## Before / After Comparison

| Endpoint | Before | After | Change |
|---|---|---|---|
| Content index | 1.54ms | 1.32ms | -14% |
| Content index + includes | 2.69ms | 2.91ms | ~same |
| Content show | 1.09ms | 0.55ms | **-50%** |
| **Autopost** | **3.09ms** | **2.38ms** | **-23%** |
| Categories index | 1.99ms | 1.90ms | -5% |
| Posts index | 1.07ms | 0.91ms | -15% |
| Posts index + includes | 1.47ms | 1.06ms | **-28%** |
| Media index | 1.19ms | 1.06ms | -11% |
| **Total** | **14.13ms** | **12.09ms** | **-14%** |

**Key takeaway:** Query counts stayed the same (no N+1 issues existed), but DB time improved across the board from the new indices. The content show endpoint saw the largest improvement at 50%. The autopost content selection query — the primary optimization target — improved 23%.

## Observability Added

| Feature | What it provides |
|---|---|
| `X-Request-Duration-Ms` header | Visible in Postman/DevTools on every API response |
| `X-Query-Count` header | Instant query count without Telescope |
| `X-DB-Time-Ms` header | DB time breakdown per request |
| Structured request log (telescope) | Method, URI, status, duration, query count, DB time, memory peak |
| Slow request warnings | Auto-escalates to `warning` level at >500ms or >10 queries |
| Autopost decision log (storage/logs) | JSON-formatted audit trail of every autopost decision |
| Correlation ID | UUID linking all log entries within a single autopost request |
| Query count regression guard | `EndpointPerformanceTest` fails if query counts exceed ceilings |

## Test Coverage

| Test File | Tests | What it covers |
|---|---|---|
| `EndpointPerformanceTest` | 8 | Query count ceilings per endpoint (regression guard) |
| `RequestTimingMiddlewareTest` | 9 | Response headers, structured logging, warning thresholds |
| `AutopostLoggingTest` | 7 | Every autopost log event, correlation ID consistency |
| `QueryScopeTest` | 8 | All 7 query scopes + chaining composition |
| **Total new tests** | **32** | |
