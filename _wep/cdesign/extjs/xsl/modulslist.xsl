<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" encoding="UTF-8" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="modulslist">
	  <xsl:for-each select="item">
	    <div class='modullist'>
			<xsl:if test="../@modul=@id"><xsl:attribute name="class">modullist selected</xsl:attribute></xsl:if>
			<a href="index.php?_view=list&amp;_modul={@id}"><xsl:value-of select="."/></a></div>
	  </xsl:for-each>
	  <div class="clk"></div>
	</xsl:template>
</xsl:stylesheet>
