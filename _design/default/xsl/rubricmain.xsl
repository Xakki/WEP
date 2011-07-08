<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template match="main">
<div class="wrapper">
	<span class="char">.</span> . 
	<span class="rubmain">
	  <xsl:for-each select="item">
		<div class="blockname" style="background-position:0 -{@imgpos}px;">
			<xsl:value-of select="name"/>
		</div>
		<ul>
		  <xsl:for-each select="item">
			<li>
				<xsl:if test="@cnt>0">
					<a href="/{@path}/list.html"><xsl:value-of select="name"/></a> <span class="minicount">(<xsl:value-of select="@cnt"/>)</span>
				</xsl:if>
				<xsl:if test="not(@cnt>0)">
					<a href="/{@path}/list.html"><xsl:value-of select="name"/></a>
				</xsl:if>
			</li>
		  </xsl:for-each>
		</ul>
		<xsl:if test="position()!=last() and position() mod ceiling((count(../item) div 3))=0">
			<xsl:text disable-output-escaping="yes">&lt;/span&gt; . &lt;span class='rubmain'&gt;</xsl:text>
		</xsl:if>
	  </xsl:for-each>
	</span> . 
	<span class="char">.</span>
	<span class="eol">.</span>
</div>
</xsl:template>

</xsl:stylesheet>
