<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" indent="no" omit-xml-declaration = "yes" standalone = "yes" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
	<xsl:template match="loadform/form">
			<xsl:call-template name="formelem" />
	</xsl:template>

	<xsl:template match="form">
		<form name="{@name}" method="{@method}" enctype="multipart/form-data" action="{@action}" onsubmit="{@onsubmit}">
			<xsl:call-template name="formelem" />
		</form>

	</xsl:template>	

	<xsl:template name="formelem">
		<div class="form">
			<xsl:for-each select="field">
			<xsl:choose>

				<xsl:when test="@type='submit'">
				  <div class="tdc">
					<xsl:choose>
						<xsl:when test="@confirm!=''">
							<input type="{@type}" name="{@name}" value="{value}"  class="sbmt" onclick="if(!confirm('{@confirm}')) return false;"/>
						</xsl:when>
						<xsl:otherwise>
							<input type="{@type}" name="{@name}" value="{value}"  class="sbmt"/>
						</xsl:otherwise>
					</xsl:choose>
					</div>
				</xsl:when>

				<xsl:when test="@type='info'">
				  <div class="forminfo"><xsl:value-of disable-output-escaping="yes" select="caption"/></div>
				</xsl:when>

				<xsl:when test="@type='hidden' and @id!=''"><input type="{@type}" name="{@name}" value="{value}" id="{@id}"/></xsl:when>
				<xsl:when test="@type='hidden'"><input type="{@type}" name="{@name}" value="{value}" id="i_{@name}"/></xsl:when>

				
				<xsl:when test="@type='radio'">
				  <div id="tr_{@name}" style="{@style}">
						<div class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
							<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
						</div>
						<div class="td2">
								<xsl:if test="count(item)=0"><div colspan="2"><font color="red">Нет элементов для отображения</font></div></xsl:if>
								<xsl:for-each select="item">
									<div><xsl:value-of disable-output-escaping="yes" select="title"/></div>
									<div>
											<xsl:if test="value=../value"><input type="{../@type}" name="{../@name}" value="{value}" class="radio" checked="true"/></xsl:if>
											<xsl:if test="value!=../value"><input type="{../@type}" name="{../@name}" value="{value}" class="radio"/></xsl:if>
									</div>
								</xsl:for-each>
								<xsl:if test="@old_foto!=''"><div>Текущая фотография:<img src="{@old_foto}" height="40"/></div></xsl:if>
						</div>
				  </div>	
				</xsl:when>

				<xsl:when test="@type='checkbox'">
				  <div id="tr_{@name}" style="{@style}">
						<div class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
							<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
						</div>
						<div class="td2">
							<xsl:choose>
								<xsl:when test="count(item)>0">
									<xsl:for-each select="item">
											<div><xsl:value-of disable-output-escaping="yes" select="title"/></div>
											<div><input type="{../@type}" name="{../@name}" value="{value}" class="radio">
													<xsl:if test="@sel=1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
													</input>
											</div>
									</xsl:for-each>
								</xsl:when>

								<xsl:otherwise>
									 <input type="{@type}" name="{@name}" value="1">
										<xsl:if test="value=1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
									 </input>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:if test="comment!=''">
								<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
							</xsl:if>
						</div>
				  </div>	
				</xsl:when>

				<xsl:when test="@type='list'">
				  <div id="tr_{@name}" style="{@style}">
					<div class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
					</div>
					<div class="td2">
					 <xsl:choose>
					  <xsl:when test="@size>1">
							<select size="@size" name="{@name}" class="small" id="{@name}" onchange="{@onchange}">
								<xsl:apply-templates select="value/item">
									<xsl:with-param name="g"><xsl:value-of select="value/item/@group"/></xsl:with-param>
								</xsl:apply-templates>
							</select>
					  </xsl:when>
					  <xsl:when test="@multiple='1'">
							<select multiple="multiple" size="10" name="{@name}[]" class="small" onchange="{@onchange}">
								<xsl:apply-templates select="value/item"/>
							</select>
					  </xsl:when>
					  <xsl:otherwise>
							<select name="{@name}" id="{@name}" onchange="{@onchange}">
								<xsl:apply-templates select="value/item"/>
							</select>
					  </xsl:otherwise>
					 </xsl:choose>
 					 <xsl:if test="comment!=''">
						<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
					</xsl:if>
					</div>
				  </div>	
				</xsl:when>

				<xsl:when test="@type='date'">
				  <div id="tr_{@name}">
						<div class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
						</div>
						<div class="td2">
							<xsl:for-each select="select">
							 <select name="{@name}"><xsl:apply-templates select="value/item"/></select>
							</xsl:for-each>
							<xsl:if test="comment!=''">
								<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
							</xsl:if>
						</div>
				  </div>	
				</xsl:when>

				<xsl:when test="@type='int'">
				  <div id="tr_{@name}" style="padding-top:3px;{@style}">
						<div class="td1"></div>
						<div class="td2">
							 <input type="text" name="{@name}" value="{value}" onkeydown='return checkInt(event)' maxlength='{@max}'/>
							 &#160;<xsl:value-of disable-output-escaping="yes" select="caption"/>&#160;
							 <input type="text" name="{@name}_to" value="{@value_to}" onkeydown='return checkInt(event)' maxlength='{@max}' style="text-align:right;"/>
						</div>
				  </div>				
				</xsl:when>

				<xsl:when test="@type='text'">
				  <div id="tr_{@name}" style="{@style}">
					<div class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
					</div>
					<div class="td2">
						<input type="text" name="{@name}" value="{value}" maxlength='{@max}' class="textinput"/>
						<xsl:if test="comment!=''">
							<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
						</xsl:if>
					</div>
				  </div>	
				</xsl:when>
				<xsl:otherwise>
				  <!---
					<div id="tr_{@name}" style="{@style}">
					<div class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
					</div>
					<div class="td2">
						<xsl:choose>
							<xsl:when test="@readonly='1'">
								<input type="{@type}" name="{@name}" value="{value}" readonly="true" class="ronly"/>
							</xsl:when>
							<xsl:otherwise>
								<input type="{@type}" name="{@name}" value="{value}" maxlength="{@max}"/>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				  </div>
					-->
				</xsl:otherwise>

			</xsl:choose>
		   </xsl:for-each>
		</div>
	</xsl:template>

	<xsl:template match="item">
		<xsl:param name="v"></xsl:param>
		<xsl:choose>
			<xsl:when test="count(item)>0 and @checked=0">
				<optgroup label="{$v}{name}">
					<xsl:apply-templates select="item">
						<xsl:with-param name="v"><xsl:value-of select="$v"/>&#160;&#160;&#160;</xsl:with-param>
					</xsl:apply-templates>
				</optgroup>
			</xsl:when>
			<xsl:otherwise>

				<option value="{@id}">
					<xsl:if test="@sel='1'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
					<xsl:value-of select="$v"/>
					<xsl:if test="name=' --- '">Все</xsl:if>
					<xsl:if test="name!=' --- '"><xsl:value-of disable-output-escaping="yes" select="name"/></xsl:if>
				</option>
				<xsl:if test="count(item)>0">
					<xsl:apply-templates select="item">
						<xsl:with-param name="v"><xsl:value-of select="$v"/>&#160;&#160;&#160;</xsl:with-param>
					</xsl:apply-templates>
				</xsl:if>

			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
