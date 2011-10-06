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
	$.include('/_design/_script/syntaxhighlighter/scripts/shCore.js');
	$.include('/_design/_script/syntaxhighlighter/scripts/shAutoloader.js');
});