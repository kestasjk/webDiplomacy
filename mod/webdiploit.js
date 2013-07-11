/*   tabs  per l'index di webdiplomacy*/
/* idTabs ~ Sean Catchpole - Version 2.2 - MIT/GPL */


jq(document).ready(function(){
jq('#tabs div').hide();
jq('#tabs div:first').show();
jq('#tabs ul li:first').addClass('active');
jq('#tabs ul li a').click(function(){ 
jq('#tabs ul li').removeClass('active');
jq(this).parent().addClass('active'); 
var currentTab = jq(this).attr('href'); 
jq('#tabs div').hide();
jq(currentTab).show();
return false;
});
});

/* FINE  tabs  per l'index di webdiplomacy */
