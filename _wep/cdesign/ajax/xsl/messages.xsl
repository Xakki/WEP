<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" encoding="UTF-8" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="messages">
		<xsl:if test="count(ok) or count(error) or count(alert)">
			<div class="messages">
				<xsl:for-each select="ok"><div class="ok"><xsl:value-of disable-output-escaping="yes" select="."/></div></xsl:for-each>
				<xsl:for-each select="error"><div class="error"><xsl:value-of disable-output-escaping="yes" select="."/></div></xsl:for-each>
				<xsl:for-each select="alert"><div class="alert"><xsl:value-of disable-output-escaping="yes" select="."/></div></xsl:for-each>
			</div>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
