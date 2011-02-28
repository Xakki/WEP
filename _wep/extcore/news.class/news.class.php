<?

class news_class extends news_extend {

	function _create() {
		parent::_create();
		$this->caption = "Новости";

		//$this->create_child("news_comments");
	}
}

?>
