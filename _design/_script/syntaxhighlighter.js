function path_syntaxhighlighter()
{
  var args = arguments,
      result = []
      ;
       
  for(var i = 0; i < args.length; i++)
      result.push(args[i].replace('@', '/_design/_script/syntaxhighlighter/scripts/'));
       
  return result
};

$(function(){
	data = path_syntaxhighlighter(
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
	$.include('/_design/_script/syntaxhighlighter/scripts/shCore.js');
	$.includeCSS('/_design/_script/syntaxhighlighter/styles/shCore.css');
	$.includeCSS('/_design/_script/syntaxhighlighter/styles/shCoreDefault.css');
	$.include('/_design/_script/syntaxhighlighter/scripts/shAutoloader.js',function() {
		SyntaxHighlighter.autoloader.apply(null, data);
		SyntaxHighlighter.all();
	});
});