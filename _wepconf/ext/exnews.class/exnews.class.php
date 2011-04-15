<?

class exnews_class extends news_class {

	function _create() {
		parent::_create();
		$this->caption = "Экс Новости";

		//$this->create_child("news_comments");
	}
}

?>
