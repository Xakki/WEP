<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:import href="_design/default/xsl/pagenum.xsl"/>
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template match="main">
	<xsl:if test="count(item)>0">
	<div class="fotocheckbox">Загружать фото 
		<input type="checkbox" onclick="checkLoadFoto(this)">
			<xsl:if test="imcookie='1'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
		</input>
	</div>
		<div class="boardcount">Всего объявлений <xsl:value-of select="cnt"/></div>
		<xsl:apply-templates select="pagenum"/>
		<ul class="boardlist">
		<xsl:for-each select="item">
			
			<li>
				<xsl:if test="count(image)>0">
					<div class="imagebox">
						<xsl:for-each select="image">
							<xsl:if test="../../imcookie='1'">
								<a href="/{.}" rel="fancy{../id}" title="Фото#{position()}" class="fancyimg"><img src="/{@s}" alt="{../name}"/></a>
							</xsl:if>
							<xsl:if test="../../imcookie!='1'">
								<a href="/{.}" rel="fancy{../id}" title="Фото#{position()}" class="to-load-img">Фото#<xsl:value-of select="position()"/></a>
							</xsl:if>
						</xsl:for-each>
					</div>
				</xsl:if>
				<div class="name">
					<a href="{domen}{rubpath}/{path}_{id}.html" target="_blank">[<xsl:value-of select="datea"/>]&#160;<xsl:value-of select="tname"/>
					<xsl:for-each select="rname">&#160;/&#160;<xsl:value-of select="."/></xsl:for-each>
					<xsl:if test="name!=''"><xsl:value-of select="name"/></xsl:if></a>
					<xsl:if test="moder='1'">&#160;&#160;<i><a href="/_wep/index.php?_view=list&amp;_modul=board&amp;board_id={id}&amp;_type=edit" target="_blank">Редактировать</a></i></xsl:if>
				</div>
				<div class="text" style="display:block;"><xsl:value-of select="text"/></div><div style="clear:right;"></div>
				<div><div class="spoiler-head folded clickable" onclick="clickSpoilers(this)">Контакты</div><div class="spoiler-body" style="display: none;">
					<noindex>
						<xsl:if test="contact!=''"><div class="contact"><xsl:value-of disable-output-escaping="yes" select="contact"/></div></xsl:if>
						<xsl:if test="phone!=''"><div class="phone"><xsl:value-of disable-output-escaping="yes" select="phone"/></div></xsl:if>
						<xsl:if test="email!=''"><div class="email"><a href="mail_{id}.html" target="_blank">Написать письмо</a></div></xsl:if>
					</noindex>
				</div></div>
			</li>
		</xsl:for-each>
		</ul>
		<xsl:apply-templates select="pagenum"/>
	</xsl:if>
	<xsl:if test="count(item)=0">
		<br/><br/>
		<div class="messages">
			<div class="alert">К сожалению по вашему запросу объявлений на сайте нет.</div>
			<div class="alert">Возможно, обнулив все  <b><a href="{pg}">Параметры</a></b> поиска или выбрав <a href="city.html" onclick="return JSWin({{'href':'_js.php?_view=city'}})">другой город(регион)</a>, вы найдете то что искали. </div>
			<div class="alert">Вы также можете <a href="subscribe.html{req}" style="color:green;">подписаться на объявления по данному разделу</a> и получать на E-mail необходимые Вам объявления. Услуга доступна только зарегистрированным пользователям.</div>
		</div>
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
