# Phase 0: Foundation Issues Report

A detailed audit of every model, factory, migration, seeder, and route in the project.
Issues are categorized by severity: **Critical** (blocks endpoint development), **Important** (should fix for correctness/testing), **Minor** (quality improvement).

---

## Models

### User (`app/Models/User.php`)

| # | Severity | Issue |
|---|----------|-------|
| 1 | Critical | Missing `account_id` in `$fillable` — prevents creating users with mass assignment outside of seeders. |
| 2 | Critical | Missing `account(): BelongsTo` relationship to `Account`. The `BetterBeWillie` middleware loads User 1 but there's no way to traverse `$user->account`. |
| 3 | Minor | Uses legacy `$casts` property instead of `casts()` method (per project conventions, models should use the method). |

### Account (`app/Models/Account.php`)

| # | Severity | Issue |
|---|----------|-------|
| 4 | Critical | Missing `$fillable` — `['name', 'website']`. Seeders bypass this via `Model::unguard()`, but controller/test usage will fail. |
| 5 | Important | Missing `users(): HasMany` relationship to `User`. |
| 6 | Critical | Missing `socialMediaContents(): HasMany` relationship to `SocialMediaContent`. This is needed for CRUD scoping and autopost (filtering content by account). |
| 7 | Important | Missing `HasFactory` trait — no `AccountFactory` exists, so tests cannot create accounts via factories. |
| 8 | Minor | No `$casts` or `casts()` method defined. |

### SocialMediaContent (`app/Models/SocialMediaContent.php`)

| # | Severity | Issue |
|---|----------|-------|
| 9 | Critical | Missing `$fillable` — should be `['account_id', 'social_media_category_id', 'title', 'content']`. Required for the POST endpoint. |
| 10 | Critical | Missing `account(): BelongsTo` relationship to `Account`. Needed to scope content to the authenticated user's account. |
| 11 | Important | Missing `HasFactory` trait — no factory exists for test data creation. |
| 12 | Minor | The `category()` relationship uses `(new SocialMediaCategory)->getForeignKey()` which is functionally correct but unnecessarily verbose. `social_media_category_id` is the conventional FK name and could be passed directly. |
| 13 | Minor | No `$casts` or `casts()` method defined. |

### SocialMediaCategory (`app/Models/SocialMediaCategory.php`)

| # | Severity | Issue |
|---|----------|-------|
| 14 | Important | Completely empty model — no relationships, no `$fillable`, no traits. |
| 15 | Important | Missing `contents(): HasMany` relationship to `SocialMediaContent`. |
| 16 | Important | Missing `accountWeights(): HasMany` relationship to `SocialMediaAccountCategoryWeight`. |
| 17 | Important | Missing `$fillable` — `['name']`. |
| 18 | Important | Missing `HasFactory` trait. |

### SocialMediaAccountCategoryWeight (`app/Models/SocialMediaAccountCategoryWeight.php`)

| # | Severity | Issue |
|---|----------|-------|
| 19 | Critical | Completely empty model — no relationships, no `$fillable`. This model is central to the autopost algorithm. |
| 20 | Critical | Missing `account(): BelongsTo` relationship to `Account`. |
| 21 | Critical | Missing `category(): BelongsTo` relationship to `SocialMediaCategory`. |
| 22 | Critical | Missing `$fillable` — `['account_id', 'social_media_category_id', 'weight']`. |
| 23 | Important | Missing `HasFactory` trait. |
| 24 | Minor | No `$casts` — `weight` should be cast to `integer`. |

### SocialMediaSchedule (`app/Models/SocialMediaSchedule.php`)

| # | Severity | Issue |
|---|----------|-------|
| 25 | Important | Completely empty model — no relationships, no `$fillable`. |
| 26 | Important | Missing `account(): BelongsTo` relationship to `Account`. |
| 27 | Important | Missing `content(): BelongsTo` relationship to `SocialMediaContent`. |
| 28 | Important | Missing `$fillable` — `['account_id', 'social_media_content_id', 'scheduled_at']`. |
| 29 | Important | Missing `HasFactory` trait. |
| 30 | Minor | No `$casts` — `scheduled_at` should be cast to `datetime`. |

### SocialMediaPost (`app/Models/SocialMediaPost.php`)

| # | Severity | Issue |
|---|----------|-------|
| 31 | Important | Missing all relationships despite having `$timestamps = false` set. |
| 32 | Important | Missing `account(): BelongsTo` relationship to `Account`. |
| 33 | Important | Missing `content(): BelongsTo` relationship to `SocialMediaContent`. |
| 34 | Important | Missing `$fillable` — `['account_id', 'social_media_content_id', 'posted_at']`. |
| 35 | Important | Missing `HasFactory` trait. |
| 36 | Minor | No `$casts` — `posted_at` should be cast to `datetime`. |

---

## Factories

| # | Severity | Issue |
|---|----------|-------|
| 37 | Critical | Only `UserFactory` exists. Missing factories for all 6 other models: `Account`, `SocialMediaCategory`, `SocialMediaContent`, `SocialMediaAccountCategoryWeight`, `SocialMediaSchedule`, `SocialMediaPost`. Tests cannot create isolated data without these. |
| 38 | Important | `UserFactory` is missing `account_id` in its `definition()`. Creating a user via the factory will fail the foreign key constraint. |
| 39 | Minor | `UserFactory` uses a hardcoded bcrypt hash instead of `Hash::make('password')` — functional but fragile if bcrypt rounds change. |

---

## Migrations

| # | Severity | Issue |
|---|----------|-------|
| 40 | Important | `social_media_contents` table: `social_media_category_id` column created via `foreignIdFor(SocialMediaCategory::class)` but missing `->constrained()`. There is **no foreign key constraint** enforcing referential integrity to `social_media_categories`. An orphaned category ID would not be caught by the database. |
| 41 | Minor | `social_media_account_category_weights` table: No unique constraint on `(account_id, social_media_category_id)`. An account could theoretically have multiple weight entries for the same category. |
| 42 | Minor | `social_media_schedules` and `social_media_posts` tables: No unique constraint preventing the same content from being scheduled/posted multiple times for the same account. (This may be intentional, but worth noting for the autopost exclusion logic.) |
| 43 | Minor | Migrations use the older class-based style (`class CreateAccountsTable extends Migration`) instead of anonymous classes. Functional but inconsistent with modern Laravel conventions. |

---

## Seeders

| # | Severity | Issue |
|---|----------|-------|
| 44 | Minor | `SocialMediaContentSeeder` line 420/429: calls `scheduledSocialmediaPosts()` and `postedSocialmediaPosts()` — note the lowercase 'm' in `media`. The Account model methods are `scheduledSocialMediaPosts()` and `postedSocialMediaPosts()` (uppercase 'M'). **PHP method calls are case-insensitive so this works at runtime**, but it's inconsistent and confusing when reading the code. |
| 45 | Minor | `UserSeeder` stores `'password' => 'supersecurepass'` as plaintext — Laravel's `User` model doesn't auto-hash, so this password is stored unhashed. Not a functional blocker since auth goes through `BetterBeWillie`, but it's technically incorrect. |
| 46 | Minor | `SocialMediaContentSeeder` uses `rand()` which is not seedable/reproducible. Using `fake()->numberBetween()` or a seeded `mt_srand()` would make seeding deterministic. |
| 47 | Minor | Seed data only covers 8 of the 11 categories (`survey_published`, `review_featured`, `review_published` have no content). These categories will have weights but zero content — edge case for autopost. |

---

## Routes

| # | Severity | Issue |
|---|----------|-------|
| 48 | Critical | `routes/api.php` is empty — zero endpoints implemented. The middleware group exists but contains only a comment. All CRUD, autopost, and advanced endpoints need to be built. |

---

## Controllers

| # | Severity | Issue |
|---|----------|-------|
| 49 | Critical | No controllers exist beyond the base `Controller.php`. All controllers need to be created. |

---

## Form Requests & API Resources

| # | Severity | Issue |
|---|----------|-------|
| 50 | Critical | No Form Request classes exist. Validation needs to be created for all endpoints. |
| 51 | Critical | No API Resource classes exist. Response shapes need to be defined. |

---

## Tests

| # | Severity | Issue |
|---|----------|-------|
| 52 | Important | Only boilerplate example tests exist (`ExampleTest.php` in both Feature and Unit). No application-specific tests. |

---

## Summary: Fix Priority for Phase 0

### Must fix before building endpoints (Critical):
1. Add `$fillable` to all 7 models
2. Add missing `BelongsTo` relationships: `User->account`, `SocialMediaContent->account`, `SocialMediaAccountCategoryWeight->account`, `SocialMediaAccountCategoryWeight->category`, `SocialMediaSchedule->account`, `SocialMediaSchedule->content`, `SocialMediaPost->account`, `SocialMediaPost->content`
3. Add missing `HasMany` relationships: `Account->socialMediaContents`, `SocialMediaCategory->contents`, `SocialMediaCategory->accountWeights`
4. Add `HasFactory` trait to all models
5. Create factories for: `Account`, `SocialMediaCategory`, `SocialMediaContent`, `SocialMediaAccountCategoryWeight`, `SocialMediaSchedule`, `SocialMediaPost`
6. Fix `UserFactory` to include `account_id`

### Should fix (Important):
7. Add `$casts` / `casts()` for datetime and integer fields across models
8. Add `User->account` relationship for middleware/auth traversal
9. Add missing FK constraint on `social_media_contents.social_media_category_id`

### Nice to have (Minor):
10. Add unique constraint on `social_media_account_category_weights(account_id, social_media_category_id)`
11. Fix seeder casing inconsistency (`Socialmedia` vs `SocialMedia`)
12. Fix `UserSeeder` password hashing

---

**Total issues found: 52**
- Critical: 14
- Important: 20
- Minor: 18
