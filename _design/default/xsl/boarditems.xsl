<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

	<xsl:template match="main">
			<xsl:if test="count(item)=0"><div class="divform">	<div class="messages"><div class="error">Ссылка не верна. Вероятно данное объявление было удалено с сайта пользователем.</div></div></div></xsl:if>
			<ul class="boardlist">
			<xsl:for-each select="item">
				<li>
					<div class="name">
						[<xsl:value-of select="datea"/>]&#160;
						<xsl:value-of select="tname"/><xsl:for-each select="rname"> / <xsl:value-of select="."/></xsl:for-each>
						<xsl:if test="name!=''"><xsl:value-of select="name"/></xsl:if>
						<xsl:if test="moder='1'">&#160;&#160;<i><a href="/_wep/index.php?_view=list&amp;_modul=board&amp;board_id={id}&amp;_type=edit" target="_blank">Редактировать</a></i></xsl:if>
					</div>
					<xsl:if test="count(param)>0">
						<table class="boardparam">
						<tbody>
							<xsl:for-each select="param">
								<tr>
								<td class="tdn"><xsl:value-of select="@name"/></td>
								<td class="tdt"><xsl:value-of disable-output-escaping="yes" select="."/>&#160;<xsl:value-of select="@edi"/></td>
								</tr>
							</xsl:for-each>
						</tbody>
						</table>
					</xsl:if>
					<div class="text"><xsl:value-of disable-output-escaping="yes" select="text"/></div>
					<noindex>
		<xsl:if test="contact!=''"><div class="contact"><xsl:value-of disable-output-escaping="yes" select="contact"/></div></xsl:if>
		<xsl:if test="phone!=''"><div class="phone"><xsl:value-of disable-output-escaping="yes" select="phone"/></div></xsl:if>
		<xsl:if test="email!=''"><div class="email"><a href="/mail_{id}.html" target="_blank">Написать письмо</a></div></xsl:if>
					</noindex>
					<div class="imagebox">
					<xsl:for-each select="image">
						<xsl:if test=".!=''">
							<a href="/{.}" rel="fancy{../id}" title="{../name}" class="fancyimg"><img src="/{@s}" alt="{../name}"/></a>
						</xsl:if>
					</xsl:for-each>
					</div>
					<xsl:if test="count(nomination)>0">
						<div class="nomination"><span class="nomination-name">Номинация объявления за</span><br/>
							<xsl:for-each select="nomination">
								<xsl:if test="@sel=1">
									<span class="nomination-item navi1"><xsl:value-of disable-output-escaping="yes" select="."/> [<xsl:value-of select="@value"/>]</span>
								</xsl:if>
								<xsl:if test="@sel=0">
									<span class="nomination-item navi0" onclick="JSWin({{'insertObj':this,'data':'_view=boardvote&amp;_id={../id}&amp;_type={@type}','href':'/_js.php'}})"><xsl:value-of disable-output-escaping="yes" select="."/> [<xsl:value-of select="@value"/>]</span>
								</xsl:if>
							</xsl:for-each>
						</div>
					</xsl:if>
				</li>
			</xsl:for-each>
			</ul>
	</xsl:template>
</xsl:stylesheet>
