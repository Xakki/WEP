<?
global $_CFG;
include_once($_CFG['_PATH']['extcore'].'comments.extend/comments.extend.php');
class boardcomments_class extends comments_extends {

	function _set_features() {
		parent::_set_features();
	}

	function _create() {
		parent::_create();
		$this->tablename = $this->_CFG['sql']['dbpref'].'board_comments';
		$this->caption = 'Отзывы';
	}

}

?>