<?php
/**
*
* Postlove Control test
*
* @copyright (c) 2014 Stanislav Atanasov
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\tests\functional;

/**
* @group functional
*/
class postlove_post_test extends postlove_base
{
	protected $post2 = array();
	public function test_post()
	{
		$this->login();

		// Test creating topic and post to test
		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$post2 = $this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test [b]post[/b] posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		//Do we see the static?
		$class = $crawler->filter('#p' . $post2['post_id'])->filter('.postlove')->filter('span')->attr('class');

		//toggle like
		$url = $crawler->filter('#p' . $post2['post_id'])->filter('.postlove')->filter('a')->attr('href');
		$crw1 = self::request('GET', substr($url, 1), array(), array(), array('CONTENT_TYPE'	=> 'application/json'));

		//reload page and test ...
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");
		$class = $crawler->filter('#p' . $post2['post_id'])->filter('.postlove')->filter('span')->attr('class');

		$this->logout();
	}

	public function test_guest_see_loves()
	{
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertContains('1 x', $crawler->filter('#p3')->filter('.postlove')->text());
	}
	
	public function test_guests_cannot_like()
	{
		$crw1 = self::request('GET', 'app.php/postlove/toggle/3', array(), array(), array('CONTENT_TYPE'	=> 'application/json'));
		
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertContains('1 x', $crawler->filter('#p3')->filter('.postlove')->text());
		
	}
	public function test_show_likes_given()
	{
		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.liked_info')->count());
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.like_info')->count());
		$this->logout();
		
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('anavaro/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-anavaro-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'poslove[postlove_show_likes]'	=> 1,
			'poslove[postlove_show_liked]'	=> 0,
		));
		$crawler = self::submit($form);
		$this->assertContains('Changes saved!', $crawler->text());
		$this->logout();
		$this->logout();

		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertContains('x 1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.liked_info')->parents()->text());
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.like_info')->count());
		$this->logout();
		
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('anavaro/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-anavaro-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'poslove[postlove_show_likes]'	=> 0,
			'poslove[postlove_show_liked]'	=> 1,
		));
		$crawler = self::submit($form);
		$this->assertContains('Changes saved!', $crawler->text());
		$this->logout();
		$this->logout();
		
		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertContains('x 1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.like_info')->parents()->text());
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.liked_info')->count());
		$this->logout();
		
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('anavaro/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-anavaro-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'poslove[postlove_show_likes]'	=> 1,
			'poslove[postlove_show_liked]'	=> 1,
		));
		$crawler = self::submit($form);
		$this->assertContains('Changes saved!', $crawler->text());
		$this->logout();
		$this->logout();
		
		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertContains('x 1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.like_info')->parents()->text());
		$this->assertContains('x 1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.liked_info')->parents()->text());
		$this->logout();
	}

	public function test_show_list()
	{
		$this->login();
		$this->add_lang_ext('anavaro/postlove', 'postlove');
	
		$crawler = self::request('GET', "app.php/postlove/2?sid={$this->sid}");
		//$this->assertContains('zzazaza', $crawler->text());
		$this->assertEquals(1, $crawler->filter('.inner')->filter('.topiclist')->filter('ul')->filter('li')->count());
	}
}
