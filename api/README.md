# API

## Heroku
Install mysql cleardb addon to the dyno
Add database_url to heroku config:set DATABASE_URL lala
Clear cache - heroku run "php bin/console cache:clear"
Create schema - heroku run "php bin/console doc:sch:crea"


git commit -m "heroku"
heroku create
git push heroku master
heroku open

heroku config:set VAR=value
