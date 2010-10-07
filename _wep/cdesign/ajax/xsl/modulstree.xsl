<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="modulstree">
		<xsl:apply-templates select="moduls"/>
	</xsl:template>

	<xsl:template match="moduls">
	  <xsl:for-each select="serv">
	    <a class="bottonimg imgconf service" href="javascript:void('conf')" onclick="sf('{../@modul}','{@id}')"><xsl:value-of select="."/></a>
	  </xsl:for-each>
		<div class="treename">
			<div class="name"><xsl:value-of select="name"/>(<xsl:value-of select="cnt"/>) <xsl:apply-templates select="pagenum"/></div>
			<xsl:if test="@add=1"><a class="bottonimg ti_r imgadd" href="javascript:void('ADD')" onclick="lf('{@modul}','','{@oid}','{@pid}')" title="[ADD]"></a></xsl:if>
			<div class="clk"></div>
		</div>
		
		<xsl:for-each select="item">
	    <div class="treeitem" id="{../@modul}_{@id}" mod="{../@modul}">
				<xsl:if test="../@child=1"><a class="bottonimg ti imgplus" href="javascript:void('SHOW')" onclick="Lmtree('{../@modul}',this,'{@id}')" title="[SHOW]"></a></xsl:if>
				<div onclick="lf('{../@modul}','{@id}','{../@oid}','{../@pid}')" class="tiname">
					<xsl:if test="@act=0"><xsl:attribute name="class">tiname noact</xsl:attribute></xsl:if>
					<xsl:if test="not(../@child)">•</xsl:if><xsl:value-of select="."/></div>
				<div style="position:absolute;right:0;">
					<xsl:if test="@del=1"><a class="bottonimg ti_r imgdel" href="javascript:void('DEL')" onclick="df('{../@modul}','{@id}','{.}')" title="[DEL]"></a></xsl:if>
					<xsl:if test="../@ord=1">
						<!--<img onclick="ou('{../@modul}','{@id}',this)" class="ti_r" src="/admin/img/ou.png" width="12"/>
						<img onclick="od('{../@modul}','{@id}',this)" class="ti_r" src="/admin/img/od.png" width="12"/>-->
						<a class="bottonimg ti_replace" href="javascript:void('SORT')" title="[SORT]"></a>
					</xsl:if>
				</div>
				<div class="clk"></div>
				<xsl:if test="../@child=1"><!-- load js here -->
					<div id="_{../@modul}_{@id}" class="ui-sortable" style="display:none;"></div>
				</xsl:if>
			</div>
	  </xsl:for-each>
		<xsl:if test="count(item)=0">
			<div class="noelem">Пусто</div>
		</xsl:if>
	</xsl:template>

	<xsl:template match="pagenum">
		<select class="pagenum">
			<xsl:for-each select="mop">
				<option value="{.}" onclick="LmtreeP('{../../@modul}','{../../@oid}','{../../@pid}','&amp;{../../@modul}_mop={.}');">
					<xsl:if test="@sel=1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					<xsl:value-of select="."/>
				</option>
			</xsl:for-each>
		</select>
		<xsl:if test="@cntpage>0">
			<span class="pagenum"> (<xsl:value-of select="@cntpage"/>)</span>
			<select class="pagenum">
				<xsl:for-each select="link">
					<xsl:if test="@href!=''">
						<option value="{@id}" onclick="LmtreeP('{../../@modul}','{../../@oid}','{../../@pid}','&amp;_modul={@href}');">
							<xsl:choose>
								<xsl:when test="@href='select_page'">
									<xsl:attribute name="selected">selected</xsl:attribute><xsl:value-of select="."/>
								</xsl:when>
								<xsl:when test=".='first'">1</xsl:when>
								<xsl:when test=".='last'"><xsl:value-of select="@i"/></xsl:when>
								<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
							</xsl:choose>
						</option>
					</xsl:if>
				</xsl:for-each>
			</select>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
