<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="sysconf">
		<a href="login.php?exit=ok"><img src="img/close48.gif" class='exit' alt="CLOSE"/></a>
		<div class='modullist' onclick="wep.load_href('?_type=item&amp;_id={user/id}&amp;_oid={user/oid}&amp;_modul=users');nameSel(this);" style="margin-left:45px"><xsl:value-of select="user/name"/> [<xsl:value-of select="user/gr"/>]</div>
		<div class="modullist" onclick="nameSel(this);hrefConfirm('?_type=delete&amp;_id={user/id}&amp;_modul=users','delprof');return false;" style="font-weight: bold; color: rgb(144, 238, 144);margin-left:-23px;color:#ee3384;">(Удалить)</div>
	  <xsl:for-each select="item">
	    <div class='modullist' onclick="wep.load_href('?_view=list&amp;_modul={@id}');nameSel(this);"><xsl:value-of select="."/></div>
	  </xsl:for-each>
		<div class='modullist'><a href="../" target="_blank">Главная страница</a></div>
		<div class="clk"></div>
	</xsl:template>
</xsl:stylesheet>
