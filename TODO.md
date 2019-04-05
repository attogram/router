# Attogram Router Future Development

* create Test Suite in ./tests 

* new functions

  * getHome(bool $full = false) 
    * URI of top level of site
      * as a relative URI (default), or full URL with protocol and hostname
    
  * getCurrent(bool $full = false)
    * URI of current request
      * as a relative URI (default), or full URL with protocol and hostname
    
  * sendHeaders(404)    
