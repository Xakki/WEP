<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="modulsforms">
		<xsl:choose>
			<xsl:when test="count(item)>0">
				<xsl:for-each select="item">
					<div onclick="">qqqqqqqqqq</div>
				</xsl:for-each>
			</xsl:when>

			<xsl:otherwise>

			</xsl:otherwise>
		</xsl:choose>


	</xsl:template>
</xsl:stylesheet>
