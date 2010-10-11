<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" encoding="UTF-8" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template match="empty">NON
</xsl:template>

<xsl:template match="form">
	<form name="form_f_{@name}" method="post" enctype="multipart/form-data" action="{@action}">
		<xsl:if test="id!=''"><xsl:attribute name="id"><xsl:value-of select="@name"/>_<xsl:value-of select="@id"/></xsl:attribute></xsl:if>
		<xsl:call-template name="filterelem" />
	</form>
</xsl:template>	

<xsl:template name="filterelem">
	<div class="filter">
		<xsl:for-each select="field">
		<xsl:choose>
			<xsl:when test="@type='submit'">
					<input type="{@type}" name="f_{@name}" value="{value}" class="f_{@name}"/>
			</xsl:when>
			
			<xsl:when test="@type='radio'">
				<div id="row_f_{@name}" style="{@style}" class="cont2">
					<div class="cll1">
						<xsl:value-of disable-output-escaping="yes" select="caption"/>
					</div>
					<div class="cll2_2">
							
							<xsl:for-each select="item">
								<div>
								<input type="checkbox" name="f_{../@name}[]" value="{value}" class="ch_filter">
									<xsl:if test="@sel='1'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
								</input>
								
								<xsl:value-of disable-output-escaping="yes" select="name"/></div>
							</xsl:for-each>

					</div>
				</div>	
			</xsl:when>		

			<xsl:when test="@type='checkbox'">
			  <div id="row_f_{@name}" style="{@style}" class="cont3">
				<div class="cll1">
					<xsl:value-of disable-output-escaping="yes" select="caption"/>
				</div>
				<div class="cll2_2">
				  <xsl:choose>
					<xsl:when test="count(item)>0">
						<table border="0" cellpadding="0" cellspacing="1">
							<xsl:for-each select="item">
								<tr>
									<td><xsl:value-of disable-output-escaping="yes" select="title"/></td>
									<td>
										<input type="{../@type}" name="f_{../@name}" value="{value}" class="radio">
											<xsl:if test="@sel=1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
										</input>
									</td>
								</tr>
							</xsl:for-each>
						</table>
					</xsl:when>

					<xsl:otherwise>
						<select name="f_{@name}">
							<option value="">
								<xsl:if test="value!=0 and value!=1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								--- </option>
							<option value="0">
								<xsl:if test="value=0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								Выкл(0)</option>
							<option value="1">
								<xsl:if test="value=1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								Вкл(1)</option>
						</select>
					</xsl:otherwise>
				  </xsl:choose>
				</div>
			  </div>	
			</xsl:when>
			
			<xsl:when test="@type='list'">
			  <div id="row_f_{@name}" style="{@style}" class="cont">
				<div class="cll1">
					<xsl:value-of disable-output-escaping="yes" select="caption"/>
				</div>
				<div class="cll2">
					<select id="f_{@name}" name="f_{@name}">
						<xsl:if test="@readonly='1'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if>
						<option value=""> --- </option>
						<xsl:apply-templates select="value/item"/>
					</select>
				</div>
			  </div>	
			</xsl:when>

			<xsl:when test="@type='ajaxlist'">
			  <div id="row_f_{@name}" style="{@style}" class="cont">
				<div class="cll1">
					<xsl:value-of disable-output-escaping="yes" select="caption"/>
				</div>
				<div class="cll2" style="position:relative;">
					<div class="ajaxlist">
						<span style="{@labelstyle}"><xsl:value-of disable-output-escaping="yes" select="@valuedef"/></span>
						<input type="text" name="f_{@name}_2" value="{@value_2}" onfocus="show_hide_label(this,'{@name}',1)" onblur="show_hide_label(this,'{@name}',0)" onkeyup="ajaxlist(this,'{@name}')" class="{@csscheck}" style="width:180px;"/>
						<div id="ajaxlist_{@name}" style="display:none;">не найдено</div>
						<input type="hidden" name="f_{@name}" value="{value}"/>
					</div>
				</div>
			  </div>	
			</xsl:when>

			<xsl:when test="@type='int'">
			  <div id="row_f_{@name}" style="{@style}" class="cont">
				<div class="cll1">
					<xsl:value-of disable-output-escaping="yes" select="caption"/>
				</div>
				<div class="cll2_3">
					<input type="text" name="f_{@name}" id="f_{@name}" value="{value}" onkeydown='return checkInt(event)' maxlength='{@max}'/> - 
					<input type="text" name="f_{@name}_2" id="f_{@name}_2" value="{@value_2}" onkeydown='return checkInt(event)' maxlength='{@max}'/>
				</div>
				<div class="exc">
					<input type="checkbox" name="exc_{@name}" value="exc">
						<xsl:if test="@exc=1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
					</input>
				</div>
			  </div>				
			</xsl:when>


			<xsl:when test="@type='file'">
			  <div id="row_f_{@name}" style="{@style}" class="cont3">
				<div class="cll1">
					<xsl:value-of disable-output-escaping="yes" select="caption"/>
				</div>
				<div class="cll2_2">
					<select name="f_{@name}">
						<option value="">
							<xsl:if test="value!='!1' and value!='!0'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							--- </option>
						<option value="!0">
							<xsl:if test="value='!0'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							Есть файл</option>
						<option value="!1">
							<xsl:if test="value='!1'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							Нету файла</option>
					</select>
				</div>
			  </div>	
			</xsl:when>

			<xsl:otherwise>
			  <div id="row_f_{@name}" style="{@style}" class="cont">
				<div class="cll1">
					<xsl:value-of disable-output-escaping="yes" select="caption"/>
				</div>
				<div class="cll2">
					<input type="{@type}" name="f_{@name}" id="f_{@name}" value="{value}" maxlength="{@max}"/>
				</div>
				<div class="exc"><input type="checkbox" name="exc_{@name}" value="exc"></input></div>
			  </div>				
			</xsl:otherwise>
			
		</xsl:choose>
		</xsl:for-each>
		<div class="clk"></div>
	</div>
</xsl:template>

<xsl:template match="item">
	<xsl:param name="v"></xsl:param>
	<xsl:choose>
		<xsl:when test="count(item)>0 and @checked=0">
			<optgroup label="{$v}{name}"></optgroup>
				<xsl:apply-templates select="item">
					<xsl:with-param name="v">&#160;&#160;&#160;<xsl:value-of select="$v"/></xsl:with-param>
				</xsl:apply-templates>
		</xsl:when>
		<xsl:otherwise>
			<option value="{@id}">
				<xsl:if test="@sel='1'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
				<xsl:value-of select="$v"/><xsl:value-of disable-output-escaping="yes" select="name"/>
			</option>
			<xsl:if test="count(item)>0">
				<xsl:apply-templates select="item">
					<xsl:with-param name="v"><xsl:value-of select="$v"/>&#160;-&#160;-</xsl:with-param>
				</xsl:apply-templates>
			</xsl:if>

		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
	
</xsl:stylesheet>