# SF-County-Experience-App-API
A WordPress plugin that established API endpoints for managing rservations at a customer level. 


### Returning Users (or existing website users)
**Endpoint: /wp-json/experience/v1/login**
#### Example Post
```javascript
fetch('https://makesantafe.org/wp-json/experience/v1/login', {
  method: 'POST',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    clientID: [clientID],//constant defined in app that matches API constant
    email: [user_email],//email address that user used to signup with
    password: [user_password],//password chosen by user
  }),
})
```
#### Example Return
```javascript
{
    "success": true,
    "message": "Success!",
    "user_key": "[key associated with the user]"
}
```


### Updating User Information
**Endpoint: /wp-json/experience/v1/userprofile_update**
#### Example Post
```javascript
fetch('https://makesantafe.org/wp-json/experience/v1/userprofile_update', {
  method: 'POST',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    clientID: [clientID],//constant defined in app that matches API constant
    userKey: [userKey],//email address that user used to signup with
    options: [
      //TODO: Add options to update
    ]
  }),
})
```
#### Example Return
```javascript
{
    //TODO: Returns the options and updated values
}
```



### User Profile Screen
**Endpoint: /wp-json/experience/v1/userprofile**
#### Example Post
```javascript
fetch('https://makesantafe.org/wp-json/experience/v1/userprofile', {
  method: 'POST',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    clientID: [clientID],//constant defined in app that matches API constant
    userKey: [userKey], //userKey returned during the login or account creation process
  }),
})
```
#### Example Return
```javascript
{
    "userID": "4",
    "success": true,
    "name": "support@mindsharelabs.com",
    "user_meta": {
        //TODO: Update DOCS with proper return
    },
    "options": {
        //TODO: Update DOCS with proper return
    },
}
```
