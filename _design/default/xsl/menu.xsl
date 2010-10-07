<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="menu">&#160;
		<xsl:for-each select="item">
			<xsl:if test="position()!=last()">
				&#160;&#160;<a href="{href}"><xsl:value-of select="name"/></a>
				<xsl:apply-templates select="item"/>
			</xsl:if>
			<xsl:if test="position()=last()">
				&#160;&#160;<a href="{href}"><xsl:value-of select="name"/></a>
				<xsl:apply-templates select="item"/>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="item">
		&#160;&#160;<a href="{href}"><xsl:value-of select="name" /></a>
	</xsl:template>
</xsl:stylesheet>
