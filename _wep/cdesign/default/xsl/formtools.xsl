<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:import href="cdesign/default/xsl/form.xsl"/>
<xsl:import href="cdesign/default/xsl/path.xsl"/>
<xsl:import href="cdesign/default/xsl/messages.xsl"/>
<xsl:output method="xml" encoding="UTF-8" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	
	<xsl:template match="loadform/form">
			<xsl:call-template name="formelem" />
	</xsl:template>

	<xsl:template match="formblock">
		<xsl:apply-templates select="path"/>
		<div align="center">
			<xsl:apply-templates select="messages"/>
			<xsl:apply-templates select="form"/>
		</div>
	</xsl:template>

</xsl:stylesheet>
