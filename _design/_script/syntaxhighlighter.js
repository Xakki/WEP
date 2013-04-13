function path_syntaxhighlighter()
{
  var args = arguments,
      result = [],
      path = '/_vendors/SyntaxHighlighter/';
       
  for(var i = 0; i < args.length; i++)
      result.push(args[i].replace('@', path+'scripts/'));
       
  return result
};

$(function(){
	dataSL = path_syntaxhighlighter(
		'applescript            @shBrushAppleScript.js',
		'actionscript3 as3      @shBrushAS3.js',
		'bash shell             @shBrushBash.js',
		'coldfusion cf          @shBrushColdFusion.js',
		'cpp c                  @shBrushCpp.js',
		'c# c-sharp csharp      @shBrushCSharp.js',
		'css                    @shBrushCss.js',
		'delphi pascal          @shBrushDelphi.js',
		'diff patch pas         @shBrushDiff.js',
		'erl erlang             @shBrushErlang.js',
		'groovy                 @shBrushGroovy.js',
		'java                   @shBrushJava.js',
		'jfx javafx             @shBrushJavaFX.js',
		'js jscript javascript  @shBrushJScript.js',
		'perl pl                @shBrushPerl.js',
		'php                    @shBrushPhp.js',
		'text plain             @shBrushPlain.js',
		'py python              @shBrushPython.js',
		'ruby rails ror rb      @shBrushRuby.js',
		'sass scss              @shBrushSass.js',
		'scala                  @shBrushScala.js',
		'sql                    @shBrushSql.js',
		'vb vbnet               @shBrushVb.js',
		'xml xhtml xslt html    @shBrushXml.js'
	);
	wep.include(path+'scripts/shCore.js');
	wep.includeCSS(path+'styles/shCore.css');
	wep.includeCSS(path+'styles/shCoreDefault.css');
	wep.include(path+'scripts/shAutoloader.js',function() {
		SyntaxHighlighter.autoloader.apply(null, dataSL);
		SyntaxHighlighter.all();
	});
});