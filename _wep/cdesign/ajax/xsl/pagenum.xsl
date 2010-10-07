<?xml version="1.0" encoding="windows-1251" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="pagenum">
	 <xsl:if test="@cntpage>0">
	    <table border="0" cellpadding="0" cellspacing="0" width="475">
	     <tr><td align="left" style="padding:10px 0px 0px 0px;color:#4B4B4B;">
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
				<a href="{@href}" class="pagenum"><xsl:value-of select="."/></a>
			</xsl:otherwise>
		  </xsl:choose>
		 </xsl:for-each>
		  </td></tr>
		 </table>
	 </xsl:if>
	</xsl:template>
</xsl:stylesheet>