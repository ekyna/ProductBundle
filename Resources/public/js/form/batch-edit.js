define(["jquery","jquery-ui/widget"],function(a){"use strict";return a.widget("ekyna_product.batchEdit",{_create:function(){this.$togglers=this.element.find("input[type=checkbox][data-toggle-field]"),this._on(this.$togglers,{change:this._onTogglerChange}),this._onTogglerChange()},_destroy:function(){this._off(this.$togglers,"change"),this.$togglers=void 0},_onTogglerChange:function(){var b=this.element,c=b.attr("name");this.$togglers.each(function(){var d='[name="'+c+"["+a(this).data("toggle-field")+']"]',e=b.find(d),f=!a(this).is(":checked");e.prop("disabled",f)})},save:function(){}}),{init:function(a){a.batchEdit()},save:function(a){a.data("ekyna_product.batchEdit")&&a.batchEdit("save")},destroy:function(a){a.data("ekyna_product.batchEdit")&&a.batchEdit("destroy")}}});