<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:import href="design/default/xsl/form.xsl"/>
<xsl:import href="design/default/xsl/path.xsl"/>
<xsl:import href="design/default/xsl/messages.xsl"/>
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	
	<xsl:template match="loadform/form">
			<xsl:call-template name="formelem" />
	</xsl:template>

	<xsl:template match="formblock">
		<xsl:apply-templates select="path"/>
		<div align="center">
			<xsl:apply-templates select="messages"/>
			<xsl:if test="count(form)">
				<div class="dscr"><span style='color:#F00'>*</span> - обязательно для заполнения</div>
				<div class="dscr"><span style='color:#F00'>**</span> - обязательно для заполнения хотябы одно поле</div>
			</xsl:if>
			<xsl:apply-templates select="form"/>
			<xsl:for-each select="messages/script">
				<script type="text/javascript"><xsl:value-of disable-output-escaping="yes" select="."/></script>
			</xsl:for-each>
		</div>
	</xsl:template>

</xsl:stylesheet>
