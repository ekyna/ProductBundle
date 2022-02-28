define(["jquery","jquery-ui/ui/widget","select2","ekyna-commerce/form/price"],function(o){"use strict";return o.widget("ekyna_product.optionType",{_create:function(){if(this.$mode=this.element.find('div.option-mode input[type="radio"]'),this.$price=this.element.find("div.commerce-price"),this.$productWrapper=this.element.find("> .option-product"),this.$dataWrapper=this.element.find("> .option-data"),this.$product=this.$productWrapper.find("select.resource-search"),this.$cascade=this.$productWrapper.find("input.product-cascade"),this.$taxGroup=this.$dataWrapper.find(".tax-group-choice"),2!==this.$mode.length||1!==this.$price.length||1!==this.$product.length||1!==this.$cascade.length||1!==this.$taxGroup.length)throw"Missing product option type fields";this._on(this.$mode,{change:this._onModeChange}),this._onModeChange()},_destroy:function(){this._off(this.$mode,"change"),this._off(this.$product,"change"),this._off(this.$taxGroup,"change"),this.$mode=void 0,this.$product=void 0,this.$taxGroup=void 0},_onModeChange:function(){this.$dataWrapper.hide(),this.$productWrapper.hide(),this._off(this.$taxGroup,"change"),this._off(this.$product,"change");var t=this.$mode.filter(":checked").val();"product"===t?(this._on(this.$product,{change:this._onProductChange}),this._onProductChange(),this.$productWrapper.show()):"data"===t&&(this._on(this.$taxGroup,{change:this._onTaxGroupChange}),this._onTaxGroupChange(),this.$dataWrapper.show())},_onProductChange:function(){var t=null,e=this.$product.select2("data");e&&e[0]&&(e[0].tax_group?t=e[0].tax_group:(e=o(e[0].element).data("entity"))&&e.tax_group&&(t=e.tax_group)),this.$price.priceType("option","taxes",this._getTaxes(t))},_onTaxGroupChange:function(){this.$price.priceType("option","taxes",this._getTaxes(this.$taxGroup.val()))},_getTaxes:function(t){if(!t)return[];var i=[],t=this.$taxGroup.find('option[value="'+t+'"]').data("taxes");return t&&o.each(t,function(t,e){i.push(e.rate)}),i},save:function(){var t=this.$mode.filter(":checked").val();"data"===t?(this.$product.val(void 0).find("option:selected").prop("selected",!1),this.$cascade.prop("checked",!1)):"product"===t&&this.$dataWrapper.find("input, select").val(void 0)}}),{init:function(t){t.optionType()},save:function(t){t.data("ekyna_product.optionType")&&t.optionType("save")},destroy:function(t){t.data("ekyna_product.optionType")&&t.optionType("destroy")}}});