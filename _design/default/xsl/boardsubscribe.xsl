<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template match="main">
	<xsl:if test="count(item)>0">
		<div class="boardcount">Всего объявлений <xsl:value-of select="cnt"/></div>
		<ul class="boardlist">
		<xsl:for-each select="item">
			<li>
					<div class="name"><a href="{../url}board_{id}.html" target="_blank">
						[<xsl:value-of select="datea"/>]&#160;
						<xsl:value-of select="tname"/><xsl:for-each select="rname">&#160;/&#160;<xsl:value-of select="."/></xsl:for-each>
						<xsl:if test="count(param)>0">
							<xsl:for-each select="param">/ <xsl:value-of select="."/>&#160;<xsl:value-of select="@edi"/></xsl:for-each>
						</xsl:if>
						</a>
					</div>
					<div class="text"><xsl:value-of select="text"/></div>
					<div><div>Контакты</div>
							<xsl:if test="contact!=''"><div class="contact"><xsl:value-of disable-output-escaping="yes" select="contact"/></div></xsl:if>
							<xsl:if test="phone!=''"><div class="phone"><xsl:value-of disable-output-escaping="yes" select="phone"/></div></xsl:if>
							<xsl:if test="email!=''"><div class="email"><a href="{../url}mail_{id}.html" target="_blank">Написать письмо</a></div></xsl:if>
					</div>
	<!--
				<div class="imagebox">
					<xsl:for-each select="image">
						<xsl:if test=".!=''">
							<a href="/{.}" rel="fancy{../id}" title=""><img src="/{@s}" alt=""/></a>
						</xsl:if>
					</xsl:for-each>
				</div>
	-->
			</li>
		</xsl:for-each>
		</ul>
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
