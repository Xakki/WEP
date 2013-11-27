/**
 * JQuery Searchable DropDown Plugin
 *
 * @required jQuery 1.3.x
 * @author Sascha Wolski <hagman@gmx.de>
 * $Id: jquery.searchabledropdown.js 47 2010-04-07 08:57:05Z xhaggi $
 *
 * Based up on the AddIncSearch plugin published by Tobias Oetiker
 * http://plugins.jquery.com/project/AddIncSearch
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
(function ($) {
    var B = register("searchable");
    B.defaults = {maxListSize: 100, maxMultiMatch: 50, exactMatch: false, wildcards: true, ignoreCase: true, warnMultiMatch: "top {0} matches ...", warnNoMatch: "no matches ...", latency: 200, zIndex: "auto"};
    B.execute = function (g, h) {
        if ($.browser.msie && parseInt(jQuery.browser.version) < 7)return this;
        if (this.nodeName != "SELECT" || this.size > 1)return this;
        var j = $(this);
        var k = {index: -1, options: null};
        var l = "lang";
        var m = false;
        $.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
        if ($.browser.chrome)$.browser.safari = false;
        if ($.meta) {
            g = $.extend({}, options, j.data())
        }
        var n = $("<div/>");
        var o = $("<div/>");
        var p = $("<input/>");
        var q = $("<select/>");
        var r = $("<option>" + g.warnMultiMatch.replace(/\{0\}/g, g.maxMultiMatch) + "</option>").attr("disabled", "true");
        var t = $("<option>" + g.warnNoMatch + "</option>").attr("disabled", "true");
        var u = {option: function (a) {
            return $(q.get(0).options[a])
        }, selected: function () {
            return q.find(":selected")
        }, selectedIndex: function (a) {
            if (a > -1)q.get(0).selectedIndex = a;
            return q.get(0).selectedIndex
        }, size: function (a) {
            q.attr("size", Math.max(2, Math.min(a, 20)))
        }, reset: function () {
            if ((j.get(0).selectedIndex - 1) == j.data("index"))return;
            var a = j.get(0).selectedIndex;
            var b = j.get(0).length;
            var c = Math.floor(g.maxMultiMatch / 2);
            var d = Math.max(1, (a - c));
            var e = Math.min(b, Math.max(g.maxMultiMatch, (a + c)));
            var f = a - d;
            q.empty();
            u.size(e - d);
            for (var i = d; i < e; i++)q.append($(j.get(0).options[i]).clone().attr(l, i - 1));
            if (e > g.maxMultiMatch)q.append(r);
            q.get(0).selectedIndex = f
        }};
        draw();
        var x = false;
        o.mouseover(function () {
            x = true
        });
        o.mouseout(function () {
            x = false
        });
        q.mouseover(function () {
            x = true
        });
        q.mouseout(function () {
            x = false
        });
        p.click(function (e) {
            if (!m)enable(e, true); else disable(e, true)
        });
        p.blur(function (e) {
            if (!x && m)disable(e, true)
        });
        j.keydown(function (e) {
            if (e.keyCode != 9)enable(e, false, true)
        });
        j.click(function (e) {
            q.focus()
        });
        q.click(function (e) {
            if (u.selectedIndex() < 0)return;
            disable(e)
        });
        q.focus(function (e) {
            p.focus()
        });
        q.blur(function (e) {
            if (!x)disable(e, true)
        });
        q.mousemove(function (e) {
            if ($.browser.opera && parseFloat(jQuery.browser.version) >= 9.8)return true;
            var a = Math.floor(parseFloat(/([0-9\.]+)px/.exec(u.option(0).css("font-size"))));
            var b = 4;
            if ($.browser.opera)b = 2.5;
            if ($.browser.safari || $.browser.chrome)b = 3;
            a += Math.round(a / b);
            u.selectedIndex(Math.floor((e.pageY - q.offset().top + this.scrollTop) / a))
        });
        o.click(function (e) {
            p.click()
        });
        p.keyup(function (e) {
            if (jQuery.inArray(e.keyCode, new Array(9, 13, 16, 33, 34, 35, 36, 38, 40)) > -1)return true;
            A = $.trim(p.val().toLowerCase());
            clearSearchTimer();
            y = setTimeout(searching, g.latency)
        });
        p.keydown(function (e) {
            if (e.shiftKey || e.ctrlKey || e.altKey)return;
            switch (e.keyCode) {
                case 9:
                    disable(e);
                    moveTab(j, e.shiftKey ? -1 : 1);
                    break;
                case 13:
                    disable(e);
                    j.focus();
                    break;
                case 27:
                    disable(e, true);
                    j.focus();
                    break;
                case 33:
                    if (u.selectedIndex() - q.attr("size") > 0) {
                        u.selectedIndex(u.selectedIndex() -= q.attr("size"))
                    } else {
                        u.selectedIndex(0)
                    }
                    synchronize();
                    break;
                case 34:
                    if (u.selectedIndex() + q.attr("size") < q.get(0).options.length - 1) {
                        u.selectedIndex(u.selectedIndex() += q.attr("size"))
                    } else {
                        u.selectedIndex(q.get(0).options.length - 1)
                    }
                    synchronize();
                    break;
                case 38:
                    if (u.selectedIndex() > 0) {
                        u.selectedIndex(u.selectedIndex() - 1);
                        synchronize()
                    }
                    break;
                case 40:
                    if (u.selectedIndex() < q.get(0).options.length - 1) {
                        u.selectedIndex(u.selectedIndex() + 1);
                        synchronize()
                    }
                    break;
                default:
                    return true
            }
            return false
        });
        function draw() {
            j.css("text-decoration", "none");
            j.width(j.outerWidth());
            j.height(j.outerHeight());
            n.css("position", "relative");
            n.css("width", j.outerWidth());
            if ($.browser.msie)n.css("z-index", h);
            o.css({"position": "absolute", "top": 0, "left": 0, "width": j.outerWidth(), "height": j.outerHeight(), "background-color": "#FFFFFF", "opacity": "0.01"});
            p.attr("type", "text");
            p.hide();
            p.height(j.outerHeight());
            p.css({"position": "absolute", "top": 0, "left": 0, "margin": "0px", "padding": "0px", "outline-style": "none", "border-style": "solid", "border-bottom-style": "none", "border-color": "transparent", "background-color": "transparent"});
            var a = new Array();
            a.push("border-left-width");
            a.push("border-top-width");
            a.push("font-size");
            a.push("font-stretch");
            a.push("font-variant");
            a.push("font-weight");
            a.push("color");
            a.push("text-align");
            a.push("text-indent");
            a.push("text-shadow");
            a.push("text-transform");
            a.push("padding-left");
            a.push("padding-top");
            for (var i = 0; i < a.length; i++)p.css(a[i], j.css(a[i]));
            if ($.browser.msie && parseInt(jQuery.browser.version) < 8) {
                p.css("padding", "0px");
                p.css("padding-left", "3px");
                p.css("border-left-width", "2px");
                p.css("border-top-width", "3px")
            } else if ($.browser.chrome) {
                p.height(j.innerHeight());
                p.css("text-transform", "none");
                p.css("padding-left", parseFloatPx(p.css("padding-left")) + 3);
                p.css("padding-top", 2)
            } else if ($.browser.safari) {
                p.height(j.innerHeight());
                p.css("padding-top", 2);
                p.css("padding-left", 3);
                p.css("text-transform", "none")
            } else if ($.browser.opera) {
                p.height(j.innerHeight());
                var b = parseFloatPx(j.css("padding-left"));
                p.css("padding-left", b == 1 ? b + 1 : b);
                p.css("padding-top", 0)
            } else if ($.browser.mozilla) {
                p.css("padding-top", "0px");
                p.css("border-top", "0px");
                p.css("padding-left", parseFloatPx(j.css("padding-left")) + 3)
            } else {
                p.css("padding-left", parseFloatPx(j.css("padding-left")) + 3);
                p.css("padding-top", parseFloatPx(j.css("padding-top")) + 1)
            }
            var c = parseFloatPx(j.css("padding-left")) + parseFloatPx(j.css("padding-right")) + parseFloatPx(j.css("border-left-width")) + parseFloatPx(j.css("border-left-width")) + 23;
            p.width(j.outerWidth() - c);
            var w = j.css("width");
            var d = j.outerWidth();
            j.css("width", "auto");
            var d = d > j.outerWidth() ? d : j.outerWidth();
            j.css("width", w);
            q.hide();
            u.size(j.get(0).length);
            q.css({"position": "absolute", "top": j.outerHeight(), "left": 0, "width": d, "border": "1px solid #333", "font-weight": "normal", "padding": 0, "background-color": j.css("background-color"), "text-transform": j.css("text-transform")});
            var e = /^\d+$/.test(j.css("z-index")) ? j.css("z-index") : 1;
            if (g.zIndex && /^\d+$/.test(g.zIndex))e = g.zIndex;
            o.css("z-index", (e).toString(10));
            p.css("z-index", (e + 1).toString(10));
            q.css("z-index", (e + 2).toString(10));
            j.wrap(n);
            j.after(o);
            j.after(p);
            j.after(q)
        };
        function enable(e, s, v) {
            if (j.attr("disabled"))return false;
            j.prepend("<option />");
            if (typeof v == "undefined")m = !m;
            u.reset();
            synchronize();
            store();
            if (s)q.show();
            p.show();
            p.focus();
            p.select();
            j.get(0).selectedIndex = 0;
            if (typeof e != "undefined")e.stopPropagation()
        };
        function disable(e, a) {
            m = false;
            j.find(":first").remove();
            clearSearchTimer();
            p.hide();
            q.hide();
            if (typeof a != "undefined")restore();
            populate();
            if (typeof e != "undefined")e.stopPropagation()
        };
        function clearSearchTimer() {
            if (y != null)clearTimeout(y)
        };
        function populate() {
            if (u.selectedIndex() < 0 || u.selected().get(0).disabled)return;
            j.get(0).selectedIndex = parseInt(q.find(":selected").attr(l));
            j.change();
            j.data("index", new Number(j.get(0).selectedIndex))
        };
        function synchronize() {
            if (u.selectedIndex() > -1 && !u.selected().get(0).disabled)p.val(q.find(":selected").text()); else p.val(j.find(":selected").text())
        };
        function store() {
            k.index = u.selectedIndex();
            k.options = new Array();
            for (var i = 0; i < q.get(0).options.length; i++)k.options.push(q.get(0).options[i])
        };
        function restore() {
            q.empty();
            for (var i = 0; i < k.options.length; i++)q.append(k.options[i]);
            u.selectedIndex(k.index);
            u.size(k.options.length)
        };
        function moveTab(a, b) {
            var c = a.parents("form,body").eq(0).find("button,input[type!=hidden],textarea,select");
            var d = c.index(a);
            if (d > -1 && d + b < c.length && d + b >= 0) {
                c.eq(d + b).focus();
                return true
            }
            return false
        };
        function escapeRegExp(a) {
            var b = ["/", ".", "*", "+", "?", "|", "(", ")", "[", "]", "{", "}", "\\", "^", "$"];
            var c = new RegExp("(\\" + b.join("|\\") + ")", "g");
            return a.replace(c, "\\$1")
        };
        var y = null;
        var z;
        var A;

        function searching() {
            if (z == A) {
                y = null;
                return
            }
            var a = 0;
            z = A;
            q.hide();
            q.empty();
            var b = escapeRegExp(A);
            if (g.exactMatch)b = "^" + b;
            if (g.wildcards) {
                b = b.replace(/\\\*/g, ".*");
                b = b.replace(/\\\?/g, ".")
            }
            var c;
            if (g.ignoreCase)c = "i";
            A = new RegExp(b, c);
            for (var i = 1; i < j.get(0).length && a < g.maxMultiMatch; i++) {
                if (A.length == 0 || A.test(j.get(0).options[i].text)) {
                    var d = $(j.get(0).options[i]).clone().attr(l, i - 1);
                    if (j.data("index") == i)d.text(j.data("text"));
                    q.append(d);
                    a++
                }
            }
            if (a >= 1) {
                u.selectedIndex(0)
            } else if (a == 0) {
                q.append(t)
            }
            if (a >= g.maxMultiMatch) {
                q.append(r)
            }
            u.size(a);
            q.show();
            y = null
        };
        function parseFloatPx(a) {
            try {
                a = parseFloat(a.replace(/[\s]*px/, ""));
                if (!isNaN(a))return a
            } catch (e) {
            }
            return 0
        };
        return
    };
    function register(d) {
        var e = $[d] = {};
        $.fn[d] = function (b) {
            b = $.extend(e.defaults, b);
            var c = this.size();
            return this.each(function (a) {
                e.execute.call(this, b, c - a)
            })
        };
        return e
    }
})(jQuery);