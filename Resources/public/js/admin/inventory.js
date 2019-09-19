define(["jquery","routing","ekyna-dispatcher","ekyna-product/templates","ekyna-modal","ekyna-admin/barcode-scanner"],function(a,b,c,d,e,f){function g(){for(var a=v.serializeArray(),b={},c=0;c<a.length;c++)a[c].value&&(b[a[c].name]=a[c].value);return b}function h(){v.find("table input").val(null).attr("value",null),v.find("table select").val(null).find("option").prop("selected",!1)}function i(){o&&o.abort(),r.empty(),x=!1,y=-1,j()}function j(){o&&o.abort(),x||(w=!0,u.detach(),t.appendTo(r),y++,o=a.ajax({url:b.generate("ekyna_product_inventory_admin_products",{page:y}),method:"GET",dataType:"json",data:g()}),o.done(n),o.always(function(){w=!1}))}function k(){var a=p.height()-v.outerHeight()-q.outerHeight()-s.outerHeight();0>a&&(a=0),r.height(a)}function l(c,d,e,f,g){if(f=f||"GET",c.preventDefault(),c.stopPropagation(),w)return!1;if(w=!0,e=e||{},!0===e){var h=a(c.currentTarget).parents("tr").eq(0).data("id");if(!h)return console.log("Undefined product id."),!1;e={productId:h}}var i=a.ajax({url:b.generate(d,e),method:f});i.done(g||n),i.always(function(){w=!1})}function m(c,d,f,g){if(c.preventDefault(),c.stopPropagation(),w)return!1;if(w=!0,f=f||{},g=g||n,!0===f){var h=a(c.currentTarget).parents("tr").eq(0).data("id");if(!h)return console.log("Undefined product id."),!1;f={productId:h}}try{var i=new e;i.load({url:b.generate(d,f),method:"GET"}),a(i).on("ekyna.modal.response",function(a){w=!1,"json"===a.contentType&&(a.preventDefault(),g(a.content),a.modal.close())}),a(i).on("ekyna.modal.load_fail",function(){w=!1})}catch(j){console.log(j),w=!1}return!1}function n(b){return b.hasOwnProperty("products")&&0!==b.products.length?(b.update||t.detach(),a.each(b.products,function(b,c){var e=a(d["@EkynaProduct/Js/inventory_line.html.twig"].render(c)),f=r.find("tr[data-id="+c.id+"]");1===f.length?f.replaceWith(e):e.appendTo(r)}),b.update?void 0:30>b.products.length?void(x=!0):void t.appendTo(r)):(0===y&&u.appendTo(r),void(x=!0))}var o,p=a(window),q=a("#inventory thead"),r=a("#inventory tbody"),s=a("#inventory tfoot"),t=a("#inventory_wait").detach(),u=a("#inventory_none").detach(),v=a('form[name="inventory"]'),w=!1,x=!1,y=-1;f.init({}),f.addListener(function(c){o&&o.abort(),r.empty(),u.detach(),t.appendTo(r),x=!1,w=!0,y=0,o=a.ajax({url:b.generate("ekyna_product_inventory_admin_products",{page:y}),method:"GET",dataType:"json",data:{referenceCode:c}}),o.done(n),o.always(function(){w=!1})}),r.on("click","a.bookmark",function(b){var c=a(b.currentTarget).closest("a"),d=c.hasClass("fa-bookmark-o"),e=d?"ekyna_product_product_bookmark_admin_add":"ekyna_product_product_bookmark_admin_remove";return l(b,e,!0,d?"POST":"DELETE",function(){d?c.removeClass("fa-bookmark-o").addClass("fa-bookmark"):c.removeClass("fa-bookmark").addClass("fa-bookmark-o")})}),r.on("click","a.quick-edit",function(a){return m(a,"ekyna_product_inventory_admin_quick_edit",!0)}),r.on("click","a.print-label",function(c){var d=a(c.currentTarget).parents("tr").eq(0).data("id");if(!d)return console.log("Undefined product id."),!1;var e=b.generate("ekyna_product_product_admin_label",{format:"large",id:[d]}),f=window.open(e,"_blank");f.focus()}),v.on("submit",function(a){return a.preventDefault(),a.stopPropagation(),i(),!1}),v.on("reset",function(){h(),i()}),r.on("click","a.quick-edit",function(a){return m(a,"ekyna_product_inventory_admin_quick_edit",!0)}),r.on("click","a.stock-units",function(a){return m(a,"ekyna_product_inventory_admin_stock_units",!0)}),r.on("click","a.treatment",function(a){return m(a,"ekyna_product_inventory_admin_customer_orders",!0)}),r.on("click","a.resupply",function(a){return m(a,"ekyna_product_inventory_admin_resupply",!0)}),a('button[name="batch_submit"]').on("click",function(b){var c=[];return a("#inventory").serializeArray().forEach(function(a){c.push(a.value)}),1>=c.length?(alert("Please select some products"),!1):m(b,"ekyna_product_inventory_admin_batch_edit",{id:c})}),q.on("click","th.sort a",function(b){b.preventDefault(),b.stopPropagation();var c=a(b.currentTarget),d="none";return c.hasClass("none")?d="asc":c.hasClass("asc")&&(d="desc"),q.find("th.sort a").removeClass("asc desc").addClass("none"),"none"!==d?(c.removeClass("none").addClass(d),v.find('input[name="inventory[sortBy]"]').val(c.data("by")),v.find('input[name="inventory[sortDir]"]').val(d)):(v.find('input[name="inventory[sortBy]"]').val(null),v.find('input[name="inventory[sortDir]"]').val(null)),v.trigger("submit"),!1}),c.on("ekyna_commerce.stock_units.change",function(){i()}),i(),p.on("resize",k),k(),r.on("scroll",function(){w||x||t.offset().top<r.height()+v.outerHeight()+q.outerHeight()&&j()})});