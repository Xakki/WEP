<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="path">
	<xsl:if test="count(item)>0">
		<div class="path">
		<xsl:for-each select="item">
			<xsl:if test="position()>1"> / </xsl:if>
			<xsl:if test="position()!=last()">
				
				<xsl:if test="href=''"><xsl:value-of select="name"/></xsl:if>
				<xsl:if test="href!=''"><a href="{href}" onclick="return wep.load_href(this)"><xsl:value-of select="name"/></a></xsl:if>
			</xsl:if>
			<xsl:if test="position()=last()"><xsl:value-of disable-output-escaping="yes" select="name"/>&#160;<a class="buttonimg imgf5" href="{href}" onclick="return wep.load_href(this)"></a></xsl:if>
		</xsl:for-each>
		</div>
	</xsl:if>
	</xsl:template>
</xsl:stylesheet>