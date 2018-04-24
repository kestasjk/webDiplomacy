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
class postlove_acp_test extends postlove_base
{

	public function test_acp_pages()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('anavaro/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-anavaro-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$this->assertContainsLang('POSTLOVE_SHOW_LIKES', $crawler->text());
		$this->assertContainsLang('POSTLOVE_SHOW_LIKED', $crawler->text());
	}
}