define(["jquery"],function(a){"use strict";return a.fn.configurableSlotWidget=function(b){var c=a.extend({auto:!0,speed:500,timeout:4e3,pager:!1,nav:!1,random:!1,pause:!1,pauseControls:!0,prevText:"Previous",nextText:"Next",maxwidth:"",navContainer:"",manualControls:"",namespace:"conf-slot",before:a.noop,after:a.noop},b),d=0;return this.each(function(){d++;var e,f=a(this),g=0,h=0,i=f.find(".choices > ul").children(),j=i.length,k=parseFloat(c.speed),l=parseFloat(c.maxwidth),m=c.namespace,n=m+d,o=n+"_on",p=n+"_s",q={"float":"left",position:"relative",opacity:1,zIndex:2},r={"float":"none",position:"absolute",opacity:0,zIndex:1},s=function(){var a=document.body||document.documentElement,b=a.style,c="transition";if("string"==typeof b[c])return!0;e=["Moz","Webkit","Khtml","O","ms"],c=c.charAt(0).toUpperCase()+c.substr(1);var d;for(d=0;d<e.length;d++)if("string"==typeof b[e[d]+c])return!0;return!1}(),t=function(a){var b=i.eq(a),c=b.find("input[type=radio]"),d=c.data("config"),e=f.find("input[type=number]"),g=f.find(".choice-info");g.fadeOut(k/2,function(){var a=d.min_quantity||1,b=d.max_quantity||1;c.prop("checked",!0);var h=e.prop("min",a).prop("max",b).val();h<a?e.val(a):h>b&&e.val(b),f.find(".choice-title").text(d.title),f.find(".choice-description").html(d.description),f.find(".choice-price").html(d.price+"&nbsp&euro;"),g.fadeIn(k/2)})},u=function(b){t(b),c.before(b),s?(i.removeClass(o).css(r).eq(b).addClass(o).css(q),g=b,setTimeout(function(){c.after(b)},k)):i.stop().fadeOut(k,function(){a(this).removeClass(o).css(r).css("opacity",1)}).eq(b).fadeIn(k,function(){a(this).addClass(o).css(q),c.after(b),g=b})};if(i.each(function(b){this.id=p+b;var c=a(this),d=c.find("input[type=radio]");c.find("img").prop("src",d.data("config").image),d.is(":checked")&&(h=b)}),f.addClass(m+" "+n),b&&b.maxwidth&&f.css("max-width",l),i.hide().css(r).eq(0).addClass(o).css(q).show(),s&&i.show().css({"-webkit-transition":"opacity "+k+"ms ease-in-out","-moz-transition":"opacity "+k+"ms ease-in-out","-o-transition":"opacity "+k+"ms ease-in-out",transition:"opacity "+k+"ms ease-in-out"}),"undefined"==typeof document.body.style.maxWidth&&c.maxwidth){var v=function(){f.css("width","100%"),f.width()>l&&f.css("width",l)};v(),a(window).bind("resize",function(){v()})}var w=f.find("a"),x=w.filter(".prev");w.bind("click",function(b){b.preventDefault();var c=a("."+o);if(!c.queue("fx").length){var d=i.index(c),e=d-1,f=d+1<j?g+1:0;u(a(this)[0]===x[0]?e:f)}}),u(h)}),this},{init:function(a){a.configurableSlotWidget()}}});