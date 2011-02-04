<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="main">
		<xsl:apply-templates select="path"/>
		<xsl:if test="count(topmenu/item)"><xsl:apply-templates select="topmenu"/></xsl:if>
		<xsl:apply-templates select="pagenum"/>
		<div class="clk"></div>
		<div id="tools_block" style="display:none;"></div>
		<xsl:apply-templates select="messages"/>
		<xsl:if test="count(data/item)"><xsl:apply-templates select="data"/></xsl:if>
		<xsl:if test="count(topmenu/item)"><xsl:apply-templates select="topmenu"/></xsl:if>
		<xsl:apply-templates select="pagenum"/>
		<div class="clk"></div>
	</xsl:template>

	<xsl:template match="topmenu">
		<div class="menu_new">
		<xsl:for-each select="item">
			<div class="botton">
			<xsl:choose>
				<xsl:when test="@type='tools'">
					<a href="{@href}" onclick="return ShowTools('tools_block',this)" class="{@class}" title="{.}"><xsl:value-of select="."/></a>
				</xsl:when>	
				<xsl:otherwise>
					<a href="{@href}" onclick="return load_href(this)" class="{@class}">
						<xsl:if test="@sel=1"><xsl:attribute name="style">border:2px solid red;</xsl:attribute></xsl:if>
						<xsl:value-of select="."/></a>
				</xsl:otherwise>
			</xsl:choose>
			</div>
		</xsl:for-each>
		</div>
	</xsl:template>

	<xsl:template match="data">
	<table class="product">
		 <tr>
		  <th>№</th>
		<xsl:for-each select="thitem">
		  <th>
			<xsl:choose>
				<xsl:when test="@href!=''">
					<xsl:if test="@sel>0"><xsl:attribute name="style">border: 1px solid red;</xsl:attribute></xsl:if>
					<a class="bottonimg imgup" title="[SORT]" href="?{../../data/req}sort={@href}" onclick="return load_href(this)">
						<xsl:if test="@sel=1"><xsl:attribute name="class">bottonimg_sel imgup</xsl:attribute></xsl:if>
					</a>
					<xsl:value-of select="."/>
					<a class="bottonimg imgdown" title="[SORT]" href="?{../../data/req}dsort={@href}" onclick="return load_href(this)">
						<xsl:if test="@sel=2"><xsl:attribute name="class">bottonimg_sel imgdown</xsl:attribute></xsl:if>
					</a>
				</xsl:when>
				<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
			</xsl:choose>
		  </th>
		</xsl:for-each>
			<th>&#160;</th>
		 </tr>
		<xsl:for-each select="item">
			<tr>
			  <xsl:choose>
				<xsl:when test="@class!=''"><xsl:attribute name="class"><xsl:value-of select="@class"/></xsl:attribute></xsl:when>
				<xsl:when test="@style!=''"><xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute></xsl:when>
				<xsl:when test="position() mod 2= 1"><xsl:attribute name="class">odd</xsl:attribute></xsl:when>
				<xsl:otherwise></xsl:otherwise>
			  </xsl:choose>
			  <td valign="top">
				<a href="#{id}"><xsl:value-of select="position()+../pcnt"/></a><a name="{id}"></a>
			  </td>
			<xsl:for-each select="tditem">

			  <xsl:if test="count(@onetd)=0 or @onetd='open'">
					<xsl:text disable-output-escaping="yes">&lt;td valign="top"&gt;</xsl:text>
			  </xsl:if>

				<xsl:choose>
					<xsl:when test="@type='img'">
						<xsl:if test=".!=''">
							<a rel="fancy" title="рисунок" class="fancyimg" href="/{.}"><img src="/{.}" alt="" width="50"/></a>&#160;</xsl:if>
					</xsl:when>
					<xsl:when test="@type='swf'">
						<xsl:if test=".!=''">
							<xsl:value-of disable-output-escaping="yes" select="."/>&#160;
							<object type="application/x-shockwave-flash" data="/{.}" height="60" width="200"><param name="movie" value="/{.}" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>
						</xsl:if>
					</xsl:when>
					<xsl:when test="@type='file'">
						<xsl:if test=".!=''">
							<a href="{.}" target="_blank">Файл</a>&#160;
						</xsl:if>
					</xsl:when>
					<xsl:when test="@href!=''">
						<a href="{@href}" target="_blank"><xsl:value-of disable-output-escaping="yes" select="."/></a>&#160;
					</xsl:when>
					<xsl:otherwise><xsl:value-of disable-output-escaping="yes" select="."/>&#160;</xsl:otherwise>
				</xsl:choose>

			  <xsl:if test="count(@onetd)=0 or @onetd='close'">
					<xsl:text disable-output-escaping="yes">&lt;/td&gt;</xsl:text>
			  </xsl:if>

			</xsl:for-each>

			<td class="ic" style="vertical-align:top;white-space:nowrap;">
				 <xsl:if test="active/@type!='bool'">
					<xsl:if test="active='7'"><a class="bottonimg img7" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Истек срок размещения]"></a></xsl:if>
					<xsl:if test="active='6'"><a class="bottonimg img6" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Откл]"></a> <xsl:if test="denied_reason!=''"> (по причине: <xsl:value-of select="denied_reason" />)</xsl:if></xsl:if>
					<xsl:if test="active='5'"><a class="bottonimg img5" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Активировать пользователем]"></a></xsl:if>
					<xsl:if test="active='4'"><a class="bottonimg img4" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Удалено пользователем]"></a></xsl:if>
					<xsl:if test="active='3'"><a class="bottonimg img3" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Редакт. пользователем]"></a></xsl:if>
					<xsl:if test="active='2'"><a class="bottonimg img2" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Дезактивировано пользователем]"></a></xsl:if>
					<xsl:if test="active='1'"><a class="bottonimg img1" href="?{../req}{../cl}_id={id}&amp;_type=dis#{id}" onclick="return load_href(this)" title="[Вкл]"></a></xsl:if>
					<xsl:if test="active='0'"><a class="bottonimg img0" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Новоё]"></a></xsl:if>
				 </xsl:if>
				 <xsl:if test="active/@type='bool'">
					<xsl:if test="active='1'">
						<a class="bottonimg img1" href="?{../req}{../cl}_id={id}&amp;_type=dis#{id}" onclick="return load_href(this)" title="[Вкл]">
							<xsl:if test="@act=0"><xsl:attribute name="onclick">return false</xsl:attribute></xsl:if>
						</a></xsl:if>
					<xsl:if test="active!='1'">
						<a class="bottonimg img0" href="?{../req}{../cl}_id={id}&amp;_type=act#{id}" onclick="return load_href(this)" title="[Новоё]">
							<xsl:if test="@act=0"><xsl:attribute name="onclick">return false</xsl:attribute></xsl:if>
						</a></xsl:if>
				 </xsl:if>

				<xsl:if test="@edit='1'">
					<a class="bottonimg imgedit" href="?{../req}{../cl}_id={id}&amp;_type=edit#{id}" onclick="return load_href(this)" title="[редактировать]"></a>
				</xsl:if>

				<xsl:if test="@del='1'">
					<a class="bottonimg imgdel" href="?{../req}{../cl}_id={id}&amp;_type=del" onclick="return hrefConfirm(this,'del')" title="[удалить]"></a>
				</xsl:if>

				<xsl:if test="count(istree)>0">
					<br/><a href="?{../req}{../cl}_id={id}" onclick="return load_href(this)"><xsl:value-of select="istree"/> (<xsl:value-of select="istree/@cnt"/>)</a>
				</xsl:if>
				<xsl:for-each select="child">
					<br/><a href="?{../../req}{../../cl}_id={../id}&amp;{../../cl}_ch={@name}" onclick="return load_href(this)"><xsl:value-of select="."/> (<xsl:value-of select="@cnt"/>)</a>
				</xsl:for-each>
			</td>
			  <!--<td class="ic">
				<xsl:if test="@del='1' or @act='1'">
					<xsl:choose>
						<xsl:when test="spirr=1">
							<input type="checkbox" name="spirr" id="spirr{id}" value="1" checked="1" style="margin:0;" onclick="set_mspirr({id}, this.checked, 'spirr');"/>
						</xsl:when>
						<xsl:otherwise>
							<input type="checkbox" name="spirr" id="spirr{id}" value="1" style="margin:0;" onclick="set_mspirr({id}, this.checked, 'spirr');"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			  </td>-->
			</tr>
		 </xsl:for-each>
	</table>
	</xsl:template>

	<xsl:template match="pagenum">
		<div class="pagenumcnt"><xsl:value-of select="@cnt"/>&#160;:&#160;&#160;</div>
		<xsl:if test="count(link)>0">
			<div class="pagenum">
			  <xsl:for-each select="link">
				<xsl:choose>
				<xsl:when test="@href=''">
					<xsl:value-of select="."/>
				</xsl:when>
				<xsl:when test="@href='select_page'">
					<b>[<xsl:value-of select="."/>]</b>
				</xsl:when>
				<xsl:otherwise>
					<a href="{@href}" onclick="return load_href(this)"><xsl:value-of select="."/></a>
				</xsl:otherwise>
				</xsl:choose>
			 </xsl:for-each>
			 &#160;
		  </div>
		  <div class="ppagenum"></div>
		</xsl:if>
		<select class="mopselect" onchange="JSWin({'href':'js.php?_view=pagenum&amp;_modul={@modul}&amp;mop='+this.value})">
			<xsl:for-each select="mop">
				<option value="{.}">
					<xsl:if test="@sel=1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					<xsl:value-of select="."/>
				</option>
			</xsl:for-each>
		</select>
	</xsl:template>

</xsl:stylesheet>