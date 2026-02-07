# Advanced Task Menu

The tasks below are **suggestions** organized into two categories. Pick items that let you showcase your strongest skills.

**Recommendation:** Pick **2–3 Product Features** and **1–2 Engineering Excellence** tasks.

**Feel free to go beyond this menu.** The opportunities are endless — use your imagination to do something that fits and shows off your skills.

---

## Category A: Product Features

These tasks add visible, demo-worthy functionality to the Social Media Manager.

---

### 1. AI Content Writer

Generate platform-specific social media posts using an LLM (OpenAI, Anthropic, etc.).

| Verb | Path | Description |
|------|------|-------------|
| POST | `/api/social_media_contents/{id}/rewrite` | Takes a content item + platform/tone parameters, returns tailored variations. |
| POST | `/api/social_media_contents/generate` | *(Optional)* Creates content from a freeform prompt. |

- Extract LLM interaction into a dedicated service class.
- Mock the LLM in tests so the suite runs without an API key.
- **API keys:** OpenAI/Anthropic free tiers work fine. A mock fallback is acceptable if you prefer not to sign up.
- *Skills tested:* API integration, service extraction, prompt design, async processing.

---

### 2. Social Platform Publishing

Integrate with Facebook Graph API (or another social platform) to actually publish content.

| Verb | Path | Description |
|------|------|-------------|
| POST | `/api/social_media_contents/{id}/publish` | Sends the post to the external platform, stores the external ID, handles errors gracefully. |

- Add a `social_credentials` table (or similar) for encrypted access tokens.
- Implement retry logic for transient failures.
- Candidates who don't want to create a Facebook app can implement a fully-functional **mock/sandbox mode** instead.
- **API keys:** Facebook Developer account is free. Sandbox/mock mode is an acceptable fallback.
- *Skills tested:* OAuth/token management, external API integration, encrypted credential storage, retry logic.

---

### 3. Media Uploads

Extend content to support image and video attachments.

| Verb | Path | Description |
|------|------|-------------|
| POST | `/api/social_media_contents/{id}/media` | Accepts file uploads, validates file types/sizes, stores media. |
| GET  | `/api/social_media_contents/{id}/media` | Returns media attached to a content item. |

- Add a `media` table with a polymorphic or direct relationship.
- Validate file types and sizes.
- Generate video thumbnails via FFmpeg (already available in the Sail container).
- **Bonus:** Integrate Mux for video processing and HLS streaming.
- **API keys:** None required for the base task. Mux free tier is optional.
- *Skills tested:* File handling, media processing, schema design, storage architecture.

---

### 4. Content Calendar & Bulk Scheduling

Build calendar and bulk-scheduling endpoints.

| Verb | Path | Description |
|------|------|-------------|
| GET  | `/api/calendar/{year}/{month}` | Returns a structured calendar of scheduled/posted content grouped by day. |
| POST | `/api/social_media_contents/bulk-schedule` | Distributes content across a date range using a strategy parameter. |

- Support strategy options: `even_spread`, `peak_hours`, `weighted_random`.
- Include a `dry_run` mode that returns what *would* be scheduled without persisting.
- Detect and report scheduling conflicts.
- *Skills tested:* Complex queries, strategy pattern, bulk operations, transaction safety, API design.

---

### 5. Content Analytics & Recommendations

Add analytics tracking and data-driven content recommendations.

| Verb | Path | Description |
|------|------|-------------|
| GET  | `/api/analytics/overview` | Returns account-level KPIs (total posts, engagement rates, top categories). |
| GET  | `/api/analytics/categories` | Per-category performance breakdown. |
| GET  | `/api/analytics/recommendations` | Suggests category weight adjustments based on performance data. |

- Add a `content_analytics` table and seed it with realistic fake data.
- **Bonus:** Use an LLM to generate natural-language recommendation summaries.
- *Skills tested:* Aggregation queries, seeder design, recommendation algorithms, optional LLM integration.

---

### 6. Full-Text Search with Meilisearch

Meilisearch is **already running** in docker-compose but currently unused. Wire it up.

| Verb | Path | Description |
|------|------|-------------|
| GET  | `/api/social_media_contents?q=...` | Add a `q` parameter to the existing index endpoint with typo tolerance and highlighted snippets. |
| GET  | `/api/search/suggest` | Autocomplete endpoint for search-as-you-type. |

- Integrate Laravel Scout with the Meilisearch driver.
- Configure filterable attributes for faceted category filtering.
- Return highlighted matching snippets in results.
- *Skills tested:* Laravel Scout integration, search index configuration, faceted search, leveraging existing infrastructure.

---

## Category B: Engineering Excellence

These tasks demonstrate depth in algorithms, performance, reliability, and API craft.

---

### 7. Autopost v2

Upgrade the autopost selection algorithm with smarter logic.

| Verb | Path | Description |
|------|------|-------------|
| GET  | `/api/social_media_contents/autopost` | Enhanced version of the base autopost endpoint. |
| GET  | `/api/autopost/explain` | Returns a decision trace explaining *why* a particular piece of content was selected. |

- **Category diversity:** Avoid selecting the same category consecutively.
- **Cooldown periods:** Recently-posted content gets deprioritized.
- **Exploration/exploitation balance:** Occasionally surface lower-weighted categories.
- **Reproducible RNG:** Accept a `seed` parameter for deterministic, testable selections.
- *Skills tested:* Algorithm design, testability, decision transparency.

---

### 8. Performance & Observability

Profile, optimize, and instrument the application.

- Profile with **Telescope** (already installed) — eliminate N+1 queries, add database indices and query scopes.
- Add **structured JSON logging** for autopost decisions (what was selected, why, what was skipped).
- Add **request-timing middleware** that logs response times.
- Show **before/after metrics** (query counts, response times) to demonstrate impact.
- *Skills tested:* Profiling, query optimization, structured logging, middleware.

---

### 9. Scheduling & Data Integrity

Harden scheduling and validation for production-readiness.

- Make scheduling **idempotent** — safe to call multiple times without creating duplicates.
- Add **unique constraints** and **optimistic locking** to prevent concurrent autopost from double-posting.
- Centralize validation with **custom rules** (e.g., category must belong to account).
- Write a **concurrency test** proving no duplicates under parallel execution.
- *Skills tested:* Idempotency, concurrency safety, custom validation rules, constraint design.

---

### 10. Contract-First API

Provide a complete OpenAPI 3.1 specification for all endpoints.

- Cover every endpoint (basic CRUD, autopost, and your chosen advanced features).
- Include sample requests and responses.
- **Bonus:** Auto-generate the spec using [`dedoc/scramble`](https://github.com/dedoc/scramble) or [`l5-swagger`](https://github.com/DarkaOnLine/L5-Swagger).
- **Bonus:** Serve interactive documentation at `/api/docs`.
- **Bonus:** Write a test that validates API responses against the spec.
- *Skills tested:* API documentation, OpenAPI/Swagger, spec-driven development.
