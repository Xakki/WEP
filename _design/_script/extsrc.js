// http://whiteposts.com/extsrc
// v.0.007
// Released as BSD license
// Author: Slava <whiteposts@gmail.com>

(function(){
    //document.write('<div id=console></div>');
    //var console = document.getElementById('console');

    var document_write = document.write;
    var buffer = ''; // catching things like d.w('<scr');d.w('ipt></sc');...
    var span = '';
    
    function dumpBuffer() {
        if(buffer && span) {
            var txt = document.createElement('span'); 
            txt.innerHTML = buffer;
            span.appendChild(txt);
            buffer = '';
        };
    };
    
    function runNextScript() {
        dumpBuffer();

        var scripts = document.getElementsByTagName('script');
        for(var i=0;i<scripts.length;i++) {
            var current_script = scripts[i];

            var asyncsrc = current_script.getAttribute('asyncsrc');
            if(asyncsrc) {
                // asyncsrc means script doesn't use document.write - 
                // we can use it all in parallel
                
                current_script.setAttribute('asyncsrc', ''); // don't run 2nd time
                var s = document.createElement('script'); 
                s.async = true;
                s.src = asyncsrc;
                document.getElementsByTagName('head')[0].appendChild(s);
            };
            
            var extsrc = current_script.getAttribute('extsrc');
            if(extsrc) {
                // extsrc means script does use document.write - we can have
                // only one definition of document.write, so load sequentially
                
                current_script.setAttribute('extsrc', ''); // don't run 2nd time
                
                //console.innerHTML += 'start '+extsrc+'<br>'; 

                span = document.createElement('span');
                current_script.parentNode.insertBefore(span, current_script);

                document.write = function(txt) {
                    buffer += txt;
                };
                
                var s = document.createElement('script'); 
                s.async = true;
                s.src = extsrc;
                
                if(isIE()) {
                    // IE
                    
                    s.onreadystatechange = function() { 
                        //console.innerHTML += 'readychange '+this.src+' '+this.readyState+'?<br>'; 

                        if(this.readyState == 'loaded' || this.readyState == 'complete') {
                            runNextScript(); 
                        };
                    };
                } else {
                    if((navigator.userAgent.indexOf("Firefox")!=-1) || ('onerror' in s)) {
                        // Firefox
                        
                        s.onload = runNextScript;
                        s.onerror = runNextScript;
                    } else {
                        // Opera
                        
                        s.onload = runNextScript;
                        s.onreadystatechange = runNextScript;
                    };
                };
                document.getElementsByTagName('head')[0].appendChild(s);
                return;            
            };
        };
        dumpBuffer();
        document.write = document_write;
    };
    
    function isIE() {
      return /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent);
    };
    
    // Below is the code by
    // Dean Edwards/Matthias Miller/John Resig
    // that just acts as window.onload, but in browser-specific way

    function init() {
        if (arguments.callee.done) return;
        arguments.callee.done = true;
        runNextScript();
    };

    /* Mozilla/Firefox/Opera 9 */
    if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", init, false);
    }

    /* Internet Explorer */
    /*@cc_on @*/
    /*@if (@_win32)
    document.write("<script id=\"__ie_onload\" defer=\"defer\" src=\"javascript:void(0)\"><\/script>");
    var script = document.getElementById("__ie_onload");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
	    init();
        }
    };
    /*@end @*/

    /* Safari */
    if (/WebKit/i.test(navigator.userAgent)) { // условие для Safari
        var _timer = setInterval(function() {
	    if (/loaded|complete/.test(document.readyState)) {
	        clearInterval(_timer);
	        init();
	    }
        }, 10);
    }

    /* rest */
    window.onload = init;

})();

