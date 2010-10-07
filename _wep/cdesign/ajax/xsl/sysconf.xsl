<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="sysconf">
		<a href="login.php?exit=ok"><img src="img/close48.gif" class='exit' alt="CLOSE"/></a>
		<div class='uname'><xsl:value-of select="user/name"/> [<xsl:value-of select="user/gr"/>]</div>
	  <xsl:for-each select="item">
	    <div class='modullist' onclick="return Lmtree('{@id}',this);"><xsl:value-of select="."/></div>
	  </xsl:for-each>
		<div class='modullist'><a href="../">Главная страница</a></div>
	</xsl:template>
</xsl:stylesheet>
