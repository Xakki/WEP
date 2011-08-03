<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template match="main">
	<xsl:if test="count(item)>0">
		<ul class="boardlist">
		<xsl:for-each select="item">
			<li style="margin:0 20px 20px 100px;width:600px;position:relative;">
				<span style="position:absolute;top:5px;left:-60px;">
					<xsl:choose>
						<xsl:when test="position()=1"><img src="/_design/_img/nomin/medal_gold_1.png" alt="" width="25"/></xsl:when>
						<xsl:when test="position()=2"><img src="/_design/_img/nomin/medal_silver_1.png" alt="" width="25"/></xsl:when>
						<xsl:otherwise><img src="/_design/_img/nomin/medal_bronze_1.png" alt="" width="25"/></xsl:otherwise>
					</xsl:choose>
					<span style="position:absolute;left:8px;top:8px;"><xsl:value-of disable-output-escaping="yes" select="nomination[number(//i)]/@value"/></span>			
				</span>
				<xsl:if test="count(image)>0">
					<div class="imagebox">
						<xsl:for-each select="image">
							<xsl:if test="../../imcookie='1'">
								<a href="{.}" rel="fancy{../id}" title="Фото#{position()}"><img src="{@s}" alt=""/></a>
							</xsl:if>
							<xsl:if test="../../imcookie!='1'">
								<a href="{.}" rel="fancy{../id}" title="Фото#{position()}" class="to-load-img">Фото#<xsl:value-of select="position()"/></a>
							</xsl:if>
						</xsl:for-each>
					</div>
				</xsl:if>
				<div class="name"><a href="{domen}{rubpath}/{path}_{id}.html" target="_blank">
					[<xsl:value-of select="datea"/>]&#160;
					<xsl:value-of select="tname"/><xsl:for-each select="rname">&#160;/&#160;<xsl:value-of select="."/></xsl:for-each>
					<xsl:if test="count(param)>0">
						<xsl:for-each select="param">/ <xsl:value-of select="."/>&#160;<xsl:value-of select="@edi"/></xsl:for-each>
					</xsl:if>
					</a>
				</div>
				<div class="text" style="display:block;"><xsl:value-of select="text"/></div><div style="clear:right;"></div>
			</li>
		</xsl:for-each>
		</ul>
	</xsl:if>
	<xsl:if test="count(item)=0">
		<br/><br/>
		<div class="messages">
			<div class="alert">К сожалению на текущий момент нет объявлений, достойных этой номинации.</div>
		</div>
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
