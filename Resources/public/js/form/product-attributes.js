define(["jquery","routing","ekyna-form","ekyna-ui"],function(a,b,c){"use strict";return a.fn.attributesWidget=function(){return this.each(function(){function d(){g&&(g.abort(),g=null);var d=parseInt(f.val())||0;return 0>=d?void e.empty():(e.loadingSpinner(),g=a.ajax({url:b.generate("ekyna_product_product_admin_attributes_form",{attributeSetId:d}),dataType:"xml"}),void g.done(function(b){e.loadingSpinner("off"),e.empty();var d=a(b).find("form");if(1===d.length){e.append(a(d.text()).children());var f=c.create(e);f.init()}}))}var e=a(this),f=e.closest("form").find(e.data("set-field")),g=null;1===f.length&&f.on("change",d)}),this},{init:function(a){a.attributesWidget()}}});