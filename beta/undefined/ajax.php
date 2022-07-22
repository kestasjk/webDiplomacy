<?php                                                                                               

// Not sure why this happens, but some people report being stuck on the loading screen and I also ran into this eventually, across different browsers and subdomains,
// where requests ended up going to /beta/undefined/api.php and /beta/undefined/ajax.php , and got stuck requesting gameID=0

// This is a quick workaround for this issue to prevent bad first experiences of the first UI

chdir('../..');                                                                                     
                                                                                                    
require_once('ajax.php');                                                                           
                                                                                                    
