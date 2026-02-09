# Notes for running project

- sail artisan migrate && sail artisan seed
- need to add OPENAI env vars for testing
- the ai commands will queue jobs to redis if you enable the redis queue locally
  - run sail artisan queue:work to process the jobs
- sail artisan storage:link
- storage/test/surf.mp4 & ./upload muxUrl



# Notes for myself

- Need to run the prompt 'Update our media table to have an expires_at column that is set based upon the timeout of the mux upload url. Create a console command and schedule it to run hourly to clean up expired links. If a link is expired, check the status w/ mux before we remove it so we don't delete an uploaded file. Store the upload_url on the model / table. When mux_status is set to ready then clear the expires and the upload_url.'
 - Finishing up mux integration.
 - Think about adding observer to media model to auto run the mux refresh in a central place (on read and on delete)

