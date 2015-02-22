 Desc: 
--------------------------------------------------------------------------------------
This project is meant for checking the update status of a large number of wordpress
sites hosted across several servers, all in one place.

 Use:
--------------------------------------------------------------------------------------
Move the contents of api to the server hosting wordpress sites.

WARNING: api displays the update status and info of a wordpress site unauthenticated
You must restrict access to it through some other means to prevent the public from
seeing the information.

Move the contents of frontend to any web server you wish to show the update
information. This server does not require php support.

Update app.js and add a new loadUpdateData(); line for each additional server hosting
the api files
