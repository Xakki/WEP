<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="modulslist">
		<div class='modullist'><a href="index.php?_view=list&amp;_modul=users" onclick="document.location='index.php?_view=list&amp;_modul=users&amp;users_id={user/id}&amp;_type=edit';return false;">Профиль</a></div>
		<div class="modullist"><a href="login.php?exit=ok" onclick="return df('Вы действительно хотите безвозвратно удалить свой профиль?','js.php?_modul=users&amp;users_id={user/id}&amp;_type=del')" style="margin-left:-23px;color:red;">(Удалить)</a></div>
	  <xsl:for-each select="item">
	    <div class='modullist'><a href="index.php?_view=list&amp;_modul={@id}"><xsl:value-of select="."/></a></div>
	  </xsl:for-each>
	  <div class="clk"></div>
	</xsl:template>
</xsl:stylesheet>
