# Social Media Manager

Below are the setup instructions & tasks completed as outlined in [_INSTRUCTIONS.md](./_INSTRUCTIONS.md).

## Setup & Requirements

Including the dependencies of the original project that are [detailed here](#instructions), the following command should get you up and running with everything needed to test. I will need to provide you with a key.

```shell
./setup.sh --key={provided-key}
```

## Tasks

### Foundation

This step was basically fixing the existing project. Updating the 6 existing [models](#the-data) missing relationships, some are completely empty. Primarly creating a baseline for how I want things to be structured. Also creating missing factories.

### Basic CRUD

Building out the requested [CRUD endpoints](#basic-crud) for content. These can all be found in the API docs & Postman.

### Autopost

Built out the [autopost feature](#extra-functionality) as outlined with weighted algorithm for selecting content to post.

### AI Content Writer

Integrated [prism](https://prismphp.com/) and ~~OpenAI~~ OpenRouter (swapped because it's free) api to generate content as outlined [here](_ADVANCED_TASKS.md#1-ai-content-writer). Requests to rewrite or generate content are queued and dispatched by a job in the background. Another endpoint is available for checking the status, which will return the results when complete. You can set `QUEUE_CONNECTION` to `sync` in your .env file to disconnect redis, which will disable the queuing and run everything synchronously.

### Media Uploads

As outline [here](_ADVANCED_TASKS.md#3-media-uploads), I added the ability to upload media and associate it with content. It will accept images and videos. I added `minio` as a new docker service to simulate S3, it should spin up automatically with the project and be ready to go. There's also a video to test uploading with in `storage/test/`.

Image's are uploaded directly to the server. Video uploads require that you fetch a presigned url, upload your video to that and then hit the store media route. I did both approaches just to show different methods of tackling the problem.

For testing purposes, if you hit the presigned url path and then copy the response url, I created a shell script to test the upload and store after that point.

```bash
./upload.sh --save {presigned-url}
```

This will upload the sample video to the url and then hit the API to store it.

### Performance & Observability

Outlined [here](_ADVANCED_TASKS.md#8-performance--observability), I created a task and performance report while working on this one.

- [Performance Updates & Report](_PERFORMANCE.md)

### AI Usage Report

This one wasn't specifically on the task list and was just for fun but it did help me complete the AI task requests under [deliverables](./_INSTRUCTIONS.md#deliverables). At the start of the project I created a [rule in cursor](./.cursor/rules/prompt-logging.mdc) that would run each time I sent a prompt and log it w/ details. As I worked, I added some notes as needed. I then prompted to generate this [AI Report](./_AI_REPORT.md) and followed up by rewriting and tweaking the output as needed.

- [AI Report](./_AI_REPORT.md)

### Contract-First API

As [outlined here](./_ADVANCED_TASKS.md#10-contract-first-api), I integrated dedoc/scramble for API docs. Once the project is running they can be found at the links below. I'm not personally a fan of the inline comments across the codebase but it was a task so I completed it. I personally use Postman and I was adding routes to Postman for testing as I worked. So, I exported that here as well. You should be able to import the entire `postman` folder directly into Postman if you use it.

- [API Docs](http://localhost/api/docs)
- [API OpenAPI JSON](http://localhost/api/docs.json)
- [Postman](./postman/)


---
---

**Below is the original readme**

---
---

# Laravel Social Media Manager

This is the backend template for the Code Challenge.

This project comes directly out of one of our existing features. Our Social Media Manager provides pre-written content for our clients to approve and ultimately be automatically posted to their social media feeds.

What we want to create here is a small RESTful JSON API that recreates some of the endpoints we utilize every day.

> **Candidates:** Start by reading [`INSTRUCTIONS.md`](INSTRUCTIONS.md) for the challenge rules, evaluation rubric, and submission guidelines. This README serves as the technical reference for setup, data model, endpoints, and infrastructure.

- The project consists of 4 endpoints that fetch or manipulate content for posts to social media networks.
- There is no need to be concerned with auth, all endpoints should be routed through the `auth.betterbewillie` middleware to assume `User` 1 and `Account` 1 automatically.  The `routes/api.php` file already has a group set up for this.
- This project is built upon Laravel 12 boilerplate, with Telescope pre-installed.
- Migrations and full seeds have been provided.

## Instructions
This project is already set up with Laravel Sail, so getting up and running is designed to be relatively simple.

The only dependency you should need is [Docker Desktop](https://www.docker.com/products/docker-desktop).  The installation process for this can be followed in the [Laravel & Docker](https://laravel.com/docs/12.x/installation#docker-installation-using-sail) portion of Laravel's installation instructions.  Follow the instructions for your environment until it starts talking about `example-app` 😁

Once complete, follow these instructions to run the app.

- Clone this repo via `git clone git@github.com:practicegenius/laravel-social-media-manager.git`
- `cd laravel-social-media-manager`
- Run `./setup.sh --key={provided-key}` (**NOTE:** This will initialize Docker containers and can run a little long the first time.  If you drink coffee, I suggest grabbing a cup ☕ 😅)

Once setup is complete, you should be able to access the boilerplate app at http://localhost

Run `./vendor/bin/sail help` or see [Laravel Sail Documentation](https://laravel.com/docs/12.x/sail) for details on how to further utilize the environment.

## The Data

Within the `App\Models` namespace, you will find the basic data structure pre-defined.  The models are fairly simple, but here is a quick overview:

- `Account` represents our clients.  Each account designates a different business for whom we may be posting social media content.
- `SocialMediaContent` represents a piece of content for posting.
    - Each content entry has a required category associated.
    - An item of content belongs to an `Account`.
- `SocialMediaCategory` is a category for social content e.g. "trivia" or "news"
- `SocialMediaSchedule` represents an individual piece of content that has been scheduled to be posted on a future date on an `Account`'s behalf.
- `SocialMediaPost` is a piece of content that was posted on an `Account`'s behalf on a past date.
- `SocialMediaAccountCategoryWeight` belongs to an `Account` and represents that account's level of interest in the related category.

## Endpoints

### Basic CRUD
Let's stand up some really quick resource endpoints for working with social media content.

| Verb | Path                               | Description                                                                                                                                                          |
| ---- |------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| GET | `/api/social_media_contents`       | Get a list of content for the authenticated account.  Add some basic search and pagination parameters here: posted (boolean), scheduled (boolean), category (array). |
| POST | `/api/social_media_contents`       | Add a piece of content for the authenticated account. Whatever validation you cook up will do 😄                                                                     |
| DELETE | `/api/social_media_contents/{:id}` | Just delete!                                                                                                                                                         |

### Extra Functionality
One of the major features of the existing Social Media Manager is AutoPost. A client is able to set number of posts per day/week/month, and set its level of like/dislike for particular categories. AutoPost then fires at the appropriate intervals (based on their frequency selection) and selects a single piece of content to post at that time. This API endpoint here simulates that selection of content at any given time.

| Verb | Path | Description |
| ---- | ---- | ---- |
| GET| `/api/social_media_contents/autopost` | This is definitely one of the more complicated elements. Every account has what we call `SocialmediaAccountCategoryWeight`s . They are basically how the account describes how much they like each category. If they love jokes, they might give that category a weight of 10. If they hate the news, they might give that category a weight of 2. The purpose of this endpoint is to choose a piece of content based upon the account's settings. Create an algorithm that chooses a "random" piece of SocialMedia content based upon the account's weight settings. Exclude any content that is already posted or scheduled to be posted by the account in question. |

### Advanced Task Menu

In addition to the basic CRUD and AutoPost endpoint, you should implement several advanced tasks. See the full menu in [`ADVANCED_TASKS.md`](ADVANCED_TASKS.md) and the selection guidance in [`INSTRUCTIONS.md`](INSTRUCTIONS.md).

## Available Infrastructure

The docker-compose environment includes services beyond the core app that are ready to use:

- **Meilisearch** — full-text search engine, available at `localhost:7700`. Used by Advanced Task #6. (Note: `docker compose ps` may show Meilisearch as "unhealthy" — this is a cosmetic Docker healthcheck issue. Meilisearch is running fine; verify with `curl http://localhost:7700/health`.)
- **Redis** — available at `localhost:6379`. Useful for caching, queues, and rate limiting.
- **Telescope** — Laravel's debug assistant, pre-installed. Useful for Advanced Task #8.

Feel free to add any other services you see fit!
