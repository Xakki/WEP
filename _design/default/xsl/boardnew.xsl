<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="main">
			<ul class="boardnew">
			<xsl:for-each select="item">
				<li>
					<div class="name"><a target="_blank" href="{domen}{rubpath}/{path}_{id}.html">[<xsl:value-of select="datea"/>]&#160;<xsl:value-of select="tname"/><xsl:for-each select="rname">/ <xsl:value-of select="."/></xsl:for-each>
						<xsl:if test="name!=''">
							<xsl:value-of select="name"/>
						</xsl:if>
						</a>
					</div>
					<div class="text"><xsl:value-of select="text"/></div>
<!--<div><div class="spoiler-head folded clickable" onclick="clickSpoilers(this)">Контакты</div><div class="spoiler-body" style="display: none;">
		<xsl:if test="contact!=''"><div class="contact"><xsl:value-of disable-output-escaping="yes" select="contact"/></div></xsl:if>
		<xsl:if test="phone!=''"><div class="phone"><xsl:value-of disable-output-escaping="yes" select="phone"/></div></xsl:if>
		<xsl:if test="email!=''"><div class="email"><a href="/mail_{id}.html" target="_blank">Написать письмо</a></div></xsl:if>
</div></div>-->
				</li>
			</xsl:for-each>
			</ul>
	</xsl:template>
</xsl:stylesheet>
