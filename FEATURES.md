* Possibility to start timer from gitlab - needs a chrome extension like clockify
* task description adden bei timer stop
* dailysummary for normal task tracking as well?
* store slashcommands somewhere and use in messages and code
* slackid aus user objekt auslagern in tool user id tabelle
* unique user identifier cannot be email as 
* start tracking time on message by right clicking and 


* wahrscheinlich muss ich die reporting endpunkte aus slack entfernen - kann man nicht sichern

# Security
* Normal Users can only see their own time entries
* Adminreport needs to query the users role to be executed only by admins
* Vielleicht sichern über passwort was an den command drangehangen wird?


# Registration / Install
* Slack admin adds yoTime app via slack
* redirect to slack api page to add app - klick add app
* slack dialog to view app permissions is shown - klick add
* redirect to yoTime page to setup team
* slack admin becomes yoTime admin automatically
* yoTime reads workspace user info via users.list slack api
* admin can register users and assign roles to yoTime via frontend
    * alternatively users can /register individually and should be forwarded to frontend register page
* a registered user is added to our users table
??? What notifications does a user get who was added by the admin? Should be redirected to a register frontend page
