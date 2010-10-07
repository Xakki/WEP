<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="form">
		<form name="form_{@name}" method="post" enctype="multipart/form-data" action="{@action}" onsubmit="return preSubmitAJAX(this);">
			<xsl:if test="id!=''"><xsl:attribute name="id"><xsl:value-of select="@name"/>_<xsl:value-of select="@id"/></xsl:attribute></xsl:if>
			<xsl:call-template name="formelem" />
		</form>
     

	</xsl:template>	

	<xsl:template name="formelem">
		<table cellpadding="0" cellspacing="0" class="form">
		<tbody>
			<xsl:for-each select="field">
			<xsl:choose>

				<xsl:when test="@type='submit'">
				  <tr>
					<td colspan="2" class="tdc">
					<xsl:choose>
						<xsl:when test="@confirm!=''">
							<input type="{@type}" name="{@name}" value="{value}"  class="sbmt" onclick="if(!confirm('{@confirm}')) return false;"/>
						</xsl:when>
						<xsl:otherwise>
							<input type="{@type}" name="{@name}" value="{value}"  class="sbmt" onclick="{@onclick}"/>
						</xsl:otherwise>
					</xsl:choose>
					</td>
				  </tr>
				</xsl:when>

				<xsl:when test="@type='infoinput'">
					
				  <tr id="tr_{@name}" style="{@style}"><td colspan="2" class="infoinput">
						<input type="hidden" name="{@name}" value="{value}"/>
						<xsl:value-of disable-output-escaping="yes" select="caption"/></td></tr>
				</xsl:when>

				<xsl:when test="@type='info'">
				  <tr id="tr_{@name}" style="{@style}"><td colspan="2" class="forminfo"><xsl:value-of disable-output-escaping="yes" select="caption"/></td></tr>
				</xsl:when>

				<xsl:when test="@type='hidden' and @id!=''"><input type="{@type}" name="{@name}" value="{value}" id="{@id}"/></xsl:when>
				<xsl:when test="@type='hidden'"><input type="{@type}" name="{@name}" value="{value}" id="{@name}"/></xsl:when>

				<xsl:when test="@type='textarea'">
				  <tr id="tr_{@name}" style="{@style}">
				   <xsl:choose>
				    <xsl:when test="@colspan=2">
							<td colspan="2" class="tdc">
								<nobr><xsl:value-of disable-output-escaping="yes" select="caption"/>
									<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if></nobr><br/>
								<textarea style="height:250px" name="{@name}" onkeyup="textareaChange(this,{@max})" rows="10" cols="10">
									<xsl:if test="@readonly='1'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if>
									<xsl:value-of disable-output-escaping="yes" select="value"/></textarea>
								<xsl:if test="comment!=''">
									<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
								</xsl:if>
							</td>
				    </xsl:when>
				    <xsl:otherwise>
							<td class="td1">
								<xsl:value-of disable-output-escaping="yes" select="caption"/>
								<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
							</td>
							<td class="td2">
								<textarea name="{@name}" onkeyup="textareaChange(this,{@max})" rows="10" cols="10">
									<xsl:if test="@readonly='1'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if>
									<xsl:value-of disable-output-escaping="yes" select="value"/></textarea>
								<xsl:if test="comment!=''">
									<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
								</xsl:if>
							</td>
				   </xsl:otherwise>
				  </xsl:choose>
				  </tr>
				</xsl:when>
				
				<xsl:when test="@type='radio'">
				  <tr id="tr_{@name}" style="{@style}">
						<td class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
							<xsl:if test="@min>0">
							 <span style="color:#F00">*</span>
							</xsl:if>
						</td>
						<td class="td2">
							<table border="0" cellpadding="0" cellspacing="1">
								<xsl:if test="count(item)=0"><td colspan="2"><font color="red">Нет элементов для отображения</font></td></xsl:if>
								<xsl:for-each select="item">
									<tr>
									<td><xsl:value-of disable-output-escaping="yes" select="title"/></td>
									<td>
											<xsl:if test="value=../value"><input type="{../@type}" name="{../@name}" value="{value}" class="radio" checked="checked"/></xsl:if>
											<xsl:if test="value!=../value"><input type="{../@type}" name="{../@name}" value="{value}" class="radio"/></xsl:if>
									</td>
									</tr>
								</xsl:for-each>
								<xsl:if test="@old_foto!=''"><tr><td>Текущая фотография:</td><td><img src="{@old_foto}" height="40"/></td></tr></xsl:if>
							</table>
						</td>
				  </tr>	
				</xsl:when>

				<xsl:when test="@type='checkbox'">
				  <tr id="tr_{@name}" style="{@style}">
						<td class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
							<xsl:if test="@min>0">
							 <span style="color:#F00">*</span>
							</xsl:if>
						</td>
						<td class="td2_2">
							<xsl:choose>
								<xsl:when test="count(item)>0">
									<table border="0" cellpadding="0" cellspacing="1">
										<xsl:for-each select="item">
											<tr>
												<td><xsl:value-of disable-output-escaping="yes" select="title"/></td>
												<td>
														<xsl:if test="@sel=1"><input type="{../@type}" name="{../@name}" value="{value}" class="radio" checked="checked"/></xsl:if>
														<xsl:if test="@sel!=1"><input type="{../@type}" name="{../@name}" value="{value}" class="radio"/></xsl:if>
												</td>
											</tr>
										</xsl:for-each>
									</table>
								</xsl:when>

								<xsl:otherwise>
									 <xsl:if test="value=1"><input type="{@type}" name="{@name}" value="1" checked="checked"/></xsl:if>
									 <xsl:if test="value!=1"><input type="{@type}" name="{@name}" value="1"/></xsl:if>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:if test="comment!=''">
								<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
							</xsl:if>
						</td>
				  </tr>	
				</xsl:when>

				<xsl:when test="@type='list' and (@readonly!=1 or not(@readonly))">
				  <tr id="tr_{@name}" style="{@style}">
					<td class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
					</td>
					<td class="td2">
					 <xsl:choose>
					  <xsl:when test="@size>1">
							<select size="@size" name="{@name}" class="small" id="{@name}" onchange="{@onchange}">
								<xsl:if test="@readonly='1'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if>
								<xsl:apply-templates select="value/item">
									<xsl:with-param name="g"><xsl:value-of select="value/item/@group"/></xsl:with-param>
								</xsl:apply-templates>
							</select>
					  </xsl:when>
					  <xsl:when test="@multiple='1'">
							<select multiple="multiple" size="10" name="{@name}[]" class="small" onchange="{@onchange}">
								<xsl:if test="@readonly='1'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if>
								<xsl:apply-templates select="value/item"/>
							</select>
					  </xsl:when>
					  <xsl:otherwise>
							<select name="{@name}" id="{@name}" onchange="{@onchange}">
								<xsl:if test="@readonly='1'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if>
								<xsl:apply-templates select="value/item"/>
							</select>
					  </xsl:otherwise>
					 </xsl:choose>
					 <xsl:if test="comment!=''">
 						<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
					 </xsl:if>
					</td>
				  </tr>	
				</xsl:when>

				<xsl:when test="@type='date'">
				  <tr id="tr_{@name}">
						<td class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
							<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
						</td>
						<td class="td2">
						<xsl:choose>
							<xsl:when test="@readonly='1'">
								<input type="text" name="{@name}" value="{value}" readonly="readonly" class="ronly"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="select">
								 <select name="{@name}"><xsl:apply-templates select="value/item"/></select>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="comment!=''">
							<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
						</xsl:if>
						</td>
				  </tr>	
				</xsl:when>

				<xsl:when test="@type='captha'">
				  <tr id="tr_{@name}">
					<td class="td1">
						<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
					</td>
					<td class="td2" style="white-space: nowrap;">
						<div class="left"><input type="text" name="{@name}" maxlength="5" size="10" class="secret"/></div>
						<div class="secret"><img src="{@src}" class="i_secret" id='captha' alt="CARTHA"/></div>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='file'">
				  <tr id="tr_{@name}" style="{@style}">
						<td class="td1">
							<xsl:value-of disable-output-escaping="yes" select="caption"/>
							<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
					</td>
					<td class="td2">
						<input type="file" name="{@name}" size="39"/>
						<xsl:if test="comment!=''"><div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div></xsl:if>

						<xsl:choose>
							<xsl:when test="@nocom=1"></xsl:when>
							<xsl:when test="value!='' and @att_type='img'">
								<a href="/{value}" target="_blank">
									<img src="/{@thumb}" alt='img' class="attach">
										<xsl:if test="@width"><xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute></xsl:if>
										<xsl:if test="@height"><xsl:attribute name="height"><xsl:value-of select="@height"/></xsl:attribute></xsl:if>
									</img>
								</a>
							</xsl:when>
							<xsl:when test="value!='' and @att_type='swf'">
								<object type="application/x-shockwave-flash" data="/{value}" height="50" width="200"><param name="movie" value="/{value}" /><param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="scale" value="exactfit" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /></object>
							</xsl:when>
							<xsl:when test="value!=''">
								<span style="color:green"><a href="/{value}" target="_blank"><xsl:value-of select="@caption"/> загружен(a)</a></span><br/>
							</xsl:when>
							<xsl:otherwise>
								<!--<span class="err"><xsl:value-of select="@caption"/> отсутствует</span><br/>-->
							</xsl:otherwise>
						</xsl:choose>

						<xsl:if test="@del=1 and value!=''"><div style="color:red;float:right;white-space: nowrap;">Удалить?&#160;<input type="checkbox" name="{@name}_del" class="del" value="1" onclick="$('#tr_{@name} td.td2 input[name={@name}],#tr_{@name} td.td2 div.dscr').slideToggle('normal')"/></div></xsl:if>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='ckedit'">
				  <tr id="tr_{@name}">
					<td colspan="2" class="tdc">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if><br/>
						<textarea name="{@name}" rows="10" cols="80" maxlength="{@max}"><xsl:value-of disable-output-escaping="yes" select="value"/></textarea>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='fckedit' and comment='old'">
				  <tr id="tr_{@name}">
					<td colspan="2" class="tdc">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if><br/>
					   <xsl:value-of disable-output-escaping="yes" select="value"/>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='fckedit'">
				  <tr><td colspan="2" class="infotd" onclick="fckOpen('{@name}')">
					<xsl:value-of disable-output-escaping="yes" select="caption"/><xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
					</td></tr>
				  <tr id="tr_{@name}" style="display:none;">
					<script type="text/javascript">var FCKTEXT_<xsl:value-of select="@name"/>='<xsl:value-of disable-output-escaping="yes" select="value"/>';</script>
					<td colspan="2" class="tdc"></td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='int'">
				  <tr id="tr_{@name}" style="{@style}">
					<td class="td1">
						<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if></td>
						<xsl:if test="@min2=1"><span style="color:#F00">**</span></xsl:if>
					<td class="td2">
						 <xsl:choose>
							 <xsl:when test="@readonly='1'"><input type="text" name="{@name}" value="{value}" readonly="readonly" class="ronly"/></xsl:when>
							 <xsl:otherwise><input type="text" name="{@name}" value="{value}" onkeydown='return checkInt(event)' maxlength='{@max}'/></xsl:otherwise>
						 </xsl:choose>
						 <xsl:if test="comment!=''">
							<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
						</xsl:if>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='password'">
				  <tr id="tr_{@name}" style="{@style}">
					<td class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
					</td>
					<td class="td2">
						<input type="password" name="{@name}" value="" maxlength='{@max}' onkeyup='checkPass("{@name}")'/>
						<div class="dscr">Введите пароль</div>
						<input type="password" name="re_{@name}" value="" maxlength='{@max}' onkeyup='checkPass("{@name}")'/>
						<div class="dscr">Чтобы избежать ошибки повторите ввод пароля</div>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:when test="@type='password2'">
				  <tr id="tr_{@name}" style="{@style}">
					<td class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
					</td>
					<td class="td2">
						<xsl:choose>
							 <xsl:when test="@readonly='1'"><input type="{@type}" name="{@name}" value="{value}" readonly="readonly" class="ronly"/></xsl:when>
							 <xsl:otherwise>
								<input type="text" id="{@name}" name="{@name}" value="{value}" maxlength="{@max}" style="width:55%;float:left;background:#E1E1A1;" readonly="readonly"/>
								<div style='width:40%;float:right;'>
									<img src='/admin/img/aprm.gif' style='width:18px;cursor:pointer;' onclick='if(confirm("Вы действительно хотите изменить пароль?")) document.{../@name}.{@name}.value = hex_md5("{@md5}"+document.{../@name}.a_{@name}.value);' alt='Сгенерировать пароль в формате MD5'/>
									<input type='text' id='a_{@name}' name='a_{@name}' value='' style='width:80%;vertical-align:top;'/>
								</div>
							</xsl:otherwise>
						 </xsl:choose>
						<xsl:if test="comment!=''">
							<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
						</xsl:if>
					</td>
				  </tr>				
				</xsl:when>

				<xsl:otherwise>
				  <tr id="tr_{@name}" style="{@style}">
					<td class="td1">
					 	<xsl:value-of disable-output-escaping="yes" select="caption"/>
						<xsl:if test="@min>0"><span style="color:#F00">*</span></xsl:if>
						<xsl:if test="@min2=1"><span style="color:#F00">**</span></xsl:if>
					</td>
					<td class="td2">
						<xsl:choose>
							<xsl:when test="@readonly='1'">
								<input type="{@type}" name="{@name}" value="{value}" readonly="readonly" class="ronly"/>
							</xsl:when>
							<xsl:otherwise>
								<input type="{@type}" name="{@name}" value="{value}" maxlength="{@max}"/>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="comment!=''">
							<div class="dscr"><xsl:value-of disable-output-escaping="yes" select="comment"/></div>
						</xsl:if>
					</td>
				  </tr>				
				</xsl:otherwise>

			</xsl:choose>
		   </xsl:for-each>
		</tbody>
		</table>
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
					<xsl:value-of select="$v"/>&#160;<xsl:value-of disable-output-escaping="yes" select="name"/>
				</option>
				<xsl:if test="count(item)>0">
					<xsl:apply-templates select="item">
						<xsl:with-param name="v"><xsl:value-of select="$v"/>&#160;--</xsl:with-param>
					</xsl:apply-templates>
				</xsl:if>

			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
