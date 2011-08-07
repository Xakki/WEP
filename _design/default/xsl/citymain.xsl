<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template match="main">
<div>
	<h2 style="text-align: left;margin:0 0 5px;"><a href="http://{all/@city}{all/@host}/index.html"><xsl:value-of select="all"/></a> <a class="minicount">(<xsl:value-of select="all/@cnt"/>)</a>
		<xsl:for-each select="center/item">
			&#160;&#160;&#160;&#160;&#160;&#160;<a href="http://{@city}.{../../all/@host}/index.html"><xsl:value-of select="."/></a> <span class="minicount">(<xsl:value-of select="@cnt"/>)</span>
		</xsl:for-each>
	</h2>
	<div class="citilist">
	  <xsl:for-each select="item">
		<div>
			<a>
				<xsl:if test="@center=1"><xsl:attribute name="class">centercity</xsl:attribute></xsl:if>
				<xsl:if test="../noscript=0"><xsl:attribute name="href">city.html?region=<xsl:value-of select="@id"/></xsl:attribute></xsl:if>
				<xsl:if test="../noscript=1"><xsl:attribute name="href">http://<xsl:value-of select="@city"/>.<xsl:value-of select="../all/@host"/>/index.html</xsl:attribute></xsl:if>
				<xsl:value-of select="."/>
			</a>
			<a class="minicount">(<xsl:value-of select="@cnt"/>)</a>
		</div>
		<xsl:if test="position()!=last() and position()=ceiling(count(../item) div 3)*ceiling(position() div ceiling(count(../item) div 3))">
			<xsl:text disable-output-escaping="yes">&lt;/div&gt;&lt;div class='citilist'&gt;</xsl:text>
		</xsl:if>
	  </xsl:for-each>
	</div>
	<div class="clear">&#160;</div>
</div>
</xsl:template>

<xsl:template match="mainadd">
<div style="width:800px;height:90%;">
	<h2 style="text-align: left;margin:0 0 5px;">
		<a href="http://{all/@city}{all/@host}/add.html" onclick="return cityAdd('{all/@id}','{all}')"><xsl:value-of select="all"/></a>
		<span class="minicount">(<xsl:value-of select="all/@cnt"/>)</span>
	<xsl:for-each select="center/item">
		&#160;&#160;&#160;&#160;&#160;&#160;<a href="http://{@city}.{../../all/@host}/index.html"><xsl:value-of select="."/></a> <span class="minicount">(<xsl:value-of select="@cnt"/>)</span>
	</xsl:for-each>
	</h2>
	<div class="citilist">
	  <xsl:for-each select="item">
			<div>
				<a href="http://{@city}.{../all/@host}/add.html">
					<xsl:if test="@center=1"><xsl:attribute name="class">centercity</xsl:attribute></xsl:if>
					<xsl:if test="../noscript=0"><xsl:attribute name="onclick">return JSWin({'href':'/_js.php?_view=addcity&amp;region=<xsl:value-of select="@id"/>'})</xsl:attribute></xsl:if>
					<xsl:if test="../noscript=1"><xsl:attribute name="onclick">return cityAdd('<xsl:value-of select="@id"/>','<xsl:value-of select="."/>');</xsl:attribute></xsl:if>
					<xsl:value-of select="."/>
				</a>
				<span class="minicount">(<xsl:value-of select="@cnt"/>)</span>
			</div>
			<xsl:if test="position()!=last() and position()=ceiling(count(../item) div 3)*ceiling(position() div ceiling(count(../item) div 3))">
				<xsl:text disable-output-escaping="yes">&lt;/div&gt;&lt;div class='citilist'&gt;</xsl:text>
			</xsl:if>
	  </xsl:for-each>
	</div>
	<div class="clear">&#160;</div>
</div>
</xsl:template>

<xsl:template match="mainjs">
<div style="width:800px;height:90%;">
	<h2 style="text-align: left;margin:0 0 5px;"><a href="http://{all/@city}{all/@host}/index.html"><xsl:value-of select="all"/></a> <span class="minicount">(<xsl:value-of select="all/@cnt"/>)</span>
	<xsl:for-each select="center/item">
		&#160;&#160;&#160;&#160;&#160;&#160;<a href="http://{@city}.{../../all/@host}/index.html"><xsl:value-of select="."/></a> <span class="minicount">(<xsl:value-of select="@cnt"/>)</span>
	</xsl:for-each>
	</h2>
	<div class="citilist">
	  <xsl:for-each select="item">
			<div>
				<a href="http://{@city}.{../all/@host}/index.html">
					<xsl:if test="@center=1"><xsl:attribute name="class">centercity</xsl:attribute></xsl:if>
					<xsl:if test="../noscript=0"><xsl:attribute name="onclick">return JSWin({'href':'/_js.php?_view=city&amp;region=<xsl:value-of select="@id"/>'})</xsl:attribute></xsl:if>
					<xsl:value-of select="."/></a>
				<span class="minicount">(<xsl:value-of select="@cnt"/>)</span>
			</div>
			<xsl:if test="position()!=last() and position()=ceiling(count(../item) div 3)*ceiling(position() div ceiling(count(../item) div 3))">
				<xsl:text disable-output-escaping="yes">&lt;/div&gt;&lt;div class='citilist'&gt;</xsl:text>
			</xsl:if>
	  </xsl:for-each>
	</div>
	<div class="clear">&#160;</div>
</div>
</xsl:template>

</xsl:stylesheet>
