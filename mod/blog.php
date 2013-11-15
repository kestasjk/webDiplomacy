
	<div >
<?php 
	print '<div class="homeHeader">'.l_t('Blog').' <a href="blog.php">'.libHTML::link().'</a></div>';
	if( file_exists(libCache::dirName('forum').'/home-blog-index.html') )
		print file_get_contents(libCache::dirName('forum').'/home-blog-index.html');
	else
	{
		$buf_home_forum=libHome::forumNew();
		file_put_contents(libCache::dirName('forum').'/home-blog-index.html', $buf_home_forum);
		print $buf_home_forum;
	}
	print '</div>';
	
	 ?>	
