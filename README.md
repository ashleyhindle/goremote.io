# GoRemote.io
Silex app for monitoring sites with remote friendly positions - we'll generate a database of the jobs, and create an API for other people to get the information  


# API
Added a basic api which returns JSON:
https://goremote.io/api/


`GET /jobs/` - latest jobs  
`GET /job/{id}` - single jobs  

`GET /companies/` - all companies  
`GET /company/{id}` - single company  

`GET /sources/` - all sources of jobs  
`GET /source/{id}` - single source  

`GET /search/{query}` - search results, for the past two months  


Nearly at the 'refactor' stage! Exciting times!