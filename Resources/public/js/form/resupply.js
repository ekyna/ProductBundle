define(["jquery"],function(a){"use strict";return a.fn.resupplyWidget=function(){return this.each(function(){var b=a(this);b.on("change",'input[name="supplierProduct"]',function(c){b.find(".supplier-product-details").hide(),b.find(".supplier-order-details").hide();var d=a(c.currentTarget).val();a("#supplier_product_"+d+"_details").show().find('input[name="supplierOrder"]').first().prop("checked",!0).trigger("change")}),b.on("change",'input[name="supplierOrder"]',function(c){b.find(".supplier-order-details").hide();var d=a(c.currentTarget).val();a("#supplier_order_"+d+"_details").show()})}),this},{init:function(a){a.resupplyWidget()}}});