* Build chrome extension to start timer from gitlab/github
* Add task description when starting a new timer
* Create separate userTools table & extract slackId from user table

# Security
* Add admin report functionailty - list all users time entries - make sure to restrict access to admin roles

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
