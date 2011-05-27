<?
class news_class extends extendnews_class {

	function _create() {
		parent::_create();
		$this->caption = "Новости";
		$this->addform_title = "Добавить новость";
		$this->editform_title = "Изменить новость";
		$this->listform_title = "Список новостей";
		$this->listform_itemcap = "заголовок";

		//$this->create_child("news_comments");
	}
}


