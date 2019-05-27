define(["jquery"],function(a){"use strict";function b(a){this.$element=a,this.$element.data("bundleRules",this),this.locked=!1,this.$group=this.$element.closest(".form-group"),this.init()}function c(a,b,c){this.$element=a,this.$element.data("bundleChoice",this),this.configurable=b,this.slotIndex=c,this.$product=a.find("#"+a.attr("id")+"_product"),this.$options=a.find("#"+a.attr("id")+"_excludedOptionGroups"),this.init()}function d(a,b,c){this.$element=a,this.$element.data("bundleChoices",this),this.configurable=b,this.slotIndex=c,this.init()}function e(a,b){this.$element=a,this.$element.data("bundleSlot",this),this.configurable=b,this.init()}function f(a){this.$element=a,this.$element.data("bundleSlots",this),this.configurable=a.hasClass("configurable"),this.init()}return b.prototype.init=function(){},b.prototype.save=function(){this.locked},b.prototype.destroy=function(){},b.prototype.lock=function(){this.locked||(this.locked=!0,this.$group.hide().find("select,input").prop("disabled",!0))},b.prototype.unlock=function(){this.locked&&(this.locked=!1,this.$group.show().find("select,input").prop("disabled",!1))},a.fn.bundleRules=function(){return this.each(function(){void 0===a(this).data("bundleRules")&&new b(a(this))})},c.prototype.init=function(){this.configurable&&(this.$number=a('<span class="bundle-choice-index"></span>').appendTo(this.$element),this.update()),this.$product.on("change",a.proxy(this.onProductChange,this))},c.prototype.save=function(){},c.prototype.destroy=function(){this.$product.off("change")},c.prototype.setSlotIndex=function(a){this.slotIndex=a,this.configurable&&this.update()},c.prototype.update=function(){this.configurable&&this.$number.text(this.slotIndex+"."+this.$element.index())},c.prototype.onProductChange=function(){this.$options.empty();var b=this.$options,c=this.$options.attr("id"),d=this.$options.data("name"),e=this.$product.select2("data");if(e.length){var f=e[0];if(!f.hasOwnProperty("option_groups")){if(!f.hasOwnProperty("element"))return;f=a(f.element).data("entity")}if(!f.hasOwnProperty("option_groups"))return;a.each(f.option_groups,function(e,f){a('<label for="'+c+"_"+e+'" class="checkbox-inline"><input type="checkbox" id="'+c+"_"+e+'" name="'+d+'[]" value="'+f.id+'" checked="checked">['+(f.required?"Required":"Optional")+"] "+f.name+"</label>").appendTo(b)})}},a.fn.bundleChoice=function(b,d){return this.each(function(){void 0===a(this).data("bundleChoice")&&new c(a(this),b,d)})},d.prototype.init=function(){if(this.$choices=this.$element.find("> ul > li").bundleChoice(this.configurable,this.slotIndex),this.configurable){var b=a.proxy(this.onChange,this);this.$element.on("ekyna-collection-field-added",b),this.$element.on("ekyna-collection-field-removed",b),this.$element.on("ekyna-collection-field-moved-up",b),this.$element.on("ekyna-collection-field-moved-down",b)}},d.prototype.save=function(){},d.prototype.destroy=function(){},d.prototype.setSlotIndex=function(b){this.slotIndex=b,this.configurable&&this.$choices.each(function(){a(this).data("bundleChoice").setSlotIndex(b)})},d.prototype.onChange=function(){if(this.configurable){var b=this.slotIndex;this.$choices=this.$element.find("> ul > li").bundleChoice(this.configurable,this.slotIndex).each(function(){a(this).data("bundleChoice").setSlotIndex(b)})}},a.fn.bundleChoices=function(b,c){return this.each(function(){void 0===a(this).data("bundleChoices")&&new d(a(this),b,c)})},e.prototype.init=function(){this.id=this.$element.attr("id");var b=this.$element.index();this.$choices=this.$element.find("#"+this.id+"_choices").bundleChoices(this.configurable,b),this.configurable&&(this.$number=a('<span class="bundle-slot-index"></span>').text(b).appendTo(this.$element),this.$required=this.$element.find("#"+this.id+"_required"),this.$rules=this.$element.find("#"+this.id+"_rules").bundleRules(),this.$required.on("change",a.proxy(this.onRequiredChange,this)),this.onRequiredChange())},e.prototype.save=function(){this.configurable&&this.$rules.data("bundleRules").save()},e.prototype.update=function(){if(this.configurable){var a=this.$element.index();this.$number.text(a),this.$choices.data("bundleChoices").setSlotIndex(a)}},e.prototype.destroy=function(){this.configurable&&this.$required.off("change")},e.prototype.onRequiredChange=function(){this.configurable&&(this.$required.is(":checked")?this.$rules.data("bundleRules").lock():this.$rules.data("bundleRules").unlock())},a.fn.bundleSlot=function(b){return this.each(function(){void 0===a(this).data("bundleSlot")&&new e(a(this),b)})},f.prototype.init=function(){this.$slots=this.$element.find("> ul > li").bundleSlot(this.configurable);var b=a.proxy(this.onChange,this);this.$element.on("ekyna-collection-field-added",b),this.$element.on("ekyna-collection-field-removed",b),this.$element.on("ekyna-collection-field-moved-up",b),this.$element.on("ekyna-collection-field-moved-down",b)},f.prototype.save=function(){this.$slots.each(function(){a(this).data("bundleSlot").save()})},f.prototype.destroy=function(){this.$element.off("ekyna-collection-field-added"),this.$element.off("ekyna-collection-field-removed"),this.$element.off("ekyna-collection-field-moved-up"),this.$element.off("ekyna-collection-field-moved-down"),this.$slots.each(function(){a(this).data("bundleSlot").destroy()})},f.prototype.onChange=function(){this.$slots=this.$element.find("> ul > li").bundleSlot(this.configurable).each(function(){a(this).data("bundleSlot").update()})},a.fn.bundleSlots=function(){return this.each(function(){void 0===a(this).data("bundleSlots")&&new f(a(this))})},{init:function(a){a.bundleSlots()},save:function(a){var b=a.data("bundleSlots");void 0!==b&&b.destroy()},destroy:function(a){var b=a.data("bundleSlots");void 0!==b&&b.destroy()}}});