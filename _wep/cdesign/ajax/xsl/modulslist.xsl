<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="modulslist">
		<div style="padding:0 20px;float:left;">&#160;</div>
	  <xsl:for-each select="item">
	    <div class='modullist' onclick="Lmtree('{@id}',this);"><xsl:value-of select="."/></div>
	  </xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
