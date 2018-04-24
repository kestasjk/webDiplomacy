(function($) {  // Avoid conflicts with other libraries

"use strict";

phpbb.addAjaxCallback('toggle_love', function(data) {
	if (data.toggle_action === 'add')
	{
		$('#likeimg_' + data.toggle_post).removeClass('like').addClass('liked');
		var curVal = $('#like_' + data.toggle_post).text();
		if( isNaN(parseInt(curVal)) )
		{
			curVal = 0;
		}
		else
		{
			curVal = parseInt(curVal);
		}
		$('#like_' + data.toggle_post).text(curVal + 1);
	}
	else
	{
		$('#likeimg_' + data.toggle_post).removeClass('liked').addClass('like');
		$('#like_' + data.toggle_post).text(parseInt($('#like_' + data.toggle_post).text()) - 1);
	}
});

})(jQuery); // Avoid conflicts with other libraries