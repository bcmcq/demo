# AI Usage Report

I used the `prompt-logging.mdc` I created to generate these stats. I set it to always apply while building the project and also added notes as I went. From that data it generated the following output. I then cleaned it up and rewrote incorrect parts, etc.

## Prompt Stats

| Metric | Value |
|--------|-------|
| Total prompts | **32** |
| Date range | 2026-02-08 → 2026-02-09 |
| Average prompt length | ~244 chars |
| Longest prompt | 1287 chars |
| Shortest prompt | 41 chars |
| Total characters sent | 7809 |
| Most active hour | **1:00pm** (8 prompts) |

### Prompts by Model

| Model | Count | Share |
|-------|-------|-------|
| `claude-4.6-opus-high-thinking` | 29 | ██████████████████ 90% |
| `unknown` | 3 | █ 9% |

### Activity by Hour (8am → 4pm EST)

```
▃▁▂▂░░░░░░░░▅▄█▃▁░▁░░░░░
8  9 10 11 12 1  2  3  4
```

---

## My 3 Best Prompts
```
Discalimer: I used AI to scan my logged prompts and select these. I didn't personally write about why each one is great. 😁
```

### 1. Laravel Policies — Auth & Authorization Setup
> Let's create a simple admin / auth validation setup using Laravel Policies:
> - Add a migration for the users table to add is_admin and set to true for user id 1
> - Add policies for the existing methods on my three API controllers / models
> - Create an isAdmin() method on the user model
> - Make sure that create, update or delete methods for models that belong to a user can only be performed by said user OR a user w/ is_admin
> - Make sure that create, update or delete methods for models that don't belong to a user can only be performed by an admin.
> - Default to route / model binding for all CRUD operations in my current controllers.

**Why it was great:**
Clear high-level goal with six specific, actionable requirements. Specifies the Laravel-native approach (Policies, route/model binding), covers ownership vs admin edge cases, and is thorough without being overly prescriptive — leaving room for the AI to use best practices while constraining the scope.

### 2. AI Content Generation — Architecture Decisions Upfront
> Let's move to phase 3, I would like to use `prism-php/prism` (I've already installed it).
> - Use __invokeable controllers for these routes
> - Create a queuable job for executing the content generation
> - Return a reference id for rewrite and generate and a status route that will poll the job status.

**Why it was great:**
States the tool choice upfront and confirms it's already installed (no wasted cycles). Makes three key architectural decisions in advance — invokeable controllers, queueable jobs, and the async polling pattern — giving the AI a clear contract to implement against. Concise but complete.

### 3. Project Kickoff — Planning Before Coding
> I've got a eval project to complete. I would like to make a plan to tackle everything before I get started.
>
> Taking into account the following files:
> @INSTRUCTIONS.md
> @README.md
> @ADVANCED_TASKS.md
>
> I've already got the project setup and I'm to the stage of starting to work on building things out.
>
> My initial thoughts:
> - For the AI usage report deliverable, I would like to track some fun facts about AI use (cursor, claude, etc) and also implement some type of > commenting in the code so I can track stats and then create a shell or artisan command to spit out a report. Something along those lines, let's discuss this point and come up with a fun solution. I've already created a mdc rule for cursor that will log everytime I submit a prompt.
> - For the advanced tasks, I'm thinking I tackle:
>     - A:1, A:3 & maybe (A:4, A:5 or A:6) -- let's confirm 1 and 3 and add placeholders for 4, 5 and 6 we can plan later.
>     - B:8 (maybe B:9 or B:10 -- add placeholders)
> - Note the "What to build" section "NOTE: Feel free to break out of the Advanced Task Menu items. The opportunities are endless here, feel free to use your imagination to do something you feel fits and will show off your skills."
>    - if you have suggestions of something else that might be cool to integrate please let me know.

**Why it was great:**
Starts with planning instead of jumping into code. References all relevant context files, shares initial thinking with priorities, identifies specific tasks with fallbacks ("maybe A:4, A:5 or A:6"), and explicitly invites AI collaboration. Sets the tone for the entire project as a human-directed, AI-assisted workflow.

---

## When AI Was Wrong

### 1. Autopost Selection Algorithm — Cumulative Weight Logic

The AI's first pass at the weighted category selection in `AutopostService` got the algorithm wrong. The cumulative weight distribution (where you build running totals and pick the category whose range contains a random value) is straightforward, but the initial implementation had issues with boundary conditions and the fallback behavior when a selected category had no available content. The random value handling didn't properly respect the weight ranges, leading to skewed selection that didn't match the expected outcomes.

**How I caught it:** Testing with deterministic random values (`$randomValue` parameter) exposed that the boundaries weren't landing on the right categories. For example, with weights holidays=5, trivia=3, news=2, a random value of 6 should hit trivia but was selecting holidays.

**What I did:** Rewrote the `pickCategoryByCumulativeWeight()` and `selectFromWeights()` methods to correctly build the cumulative ranges and walk them with `<=` comparisons. Added the detailed PHPDoc & table documenting exactly which random values map to which categories so the logic is unambiguous for anyone reading the code later.

### 2. Mux PHP SDK Integration — No Docs, Wrong API Calls

The AI had no access to up-to-date documentation for the `muxinc/mux-php` SDK. Its initial implementation used incorrect method signatures, wrong class constructors, and a polling-based workflow that was unnecessarily complex for our use case. The AI hallucinated API methods that didn't exist in the installed version of the package.

**How I caught it:** The code failed immediately at runtime — classes weren't found, method signatures didn't match, and the asset creation flow was broken end to end.

**What I did:** Rewrote the `MuxService` from scratch: fixed the `Configuration` setup (username/password, not token-based), used the correct `CreateAssetRequest` / `InputSettings` / `PlaybackPolicy` constructors, and simplified the entire flow to a synchronous direct upload instead of the polling loop the AI originally proposed. Also stripped out the unnecessary `MuxStatus` enum and database column that came from the AI's over-engineered first attempt.

---

## What I Edited vs. Accepted As-Is

| Category | Estimate | Notes |
|----------|----------|-------|
| Accepted as-is | ~40% | Migrations, factories, seeders, Eloquent resources, form requests, and boilerplate-heavy files. When the pattern was well-established and I gave clear specs, these went straight in. |
| Light edits | ~30% | Controllers, policies, and test files. Usually needed minor adjustments — tweaking validation rules, fixing assertion values, renaming variables, or adjusting response structures to match what I actually wanted. |
| Heavy rewrites | ~20% | The autopost selection algorithm, Mux service integration, and parts of the media upload flow. Kept the structural scaffolding but substantially rewrote the core logic. |
| Rejected entirely | ~10% | The AI's original Mux polling workflow, the `MuxStatus` enum, and some overly complex middleware configurations. Threw these away and wrote simpler solutions from scratch. |

**Observations:**

AI was most helpful with boilerplate and pattern-following — anything where Laravel has a strong convention (resources, form requests, policies, migrations) came out clean and fast. It was also great at changes like "add a show route to all three controllers and update tests + Postman" where the work is repetitive but touches many files.

AI was least helpful when integrating third-party packages it didn't have docs for (Mux) and when implementing novel algorithms (weighted selection). In both cases, it produced confident-looking code that was fundamentally wrong. The lesson: for well-documented Laravel patterns, trust but verify. For anything domain-specific or involving unfamiliar packages, treat AI output as a starting point and plan to rewrite.
