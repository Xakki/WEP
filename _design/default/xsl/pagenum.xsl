<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="pagenum">
	 <xsl:if test="@cntpage>0">
	    <div align="left" class="pagenum">
			Страницы: (<xsl:value-of select="@cntpage"/>)
			 <xsl:for-each select="link">
				<xsl:choose>
				<xsl:when test="@href=''">
					<xsl:value-of select="."/>
				</xsl:when>
				<xsl:when test="@href='select_page'">
					<b>[<xsl:value-of select="."/>]</b>
				</xsl:when>
				<xsl:otherwise>
					<a href="{@href}">
						<xsl:choose>
							<xsl:when test=".='first'">Первая</xsl:when>
							<xsl:when test=".='last'">Последняя</xsl:when>
							<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
						</xsl:choose>
					</a>
				</xsl:otherwise>
				</xsl:choose>
			 </xsl:for-each>
		  </div>
		  <div class="ppagenum"></div>
	 </xsl:if>
	 <xsl:if test="not(@cntpage)">
		<div style="padding-top:40px;"></div>
	 </xsl:if>

	</xsl:template>

</xsl:stylesheet>