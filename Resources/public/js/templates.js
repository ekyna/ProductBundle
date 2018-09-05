define("ekyna-product/templates", ["twig"], function(Twig) {
var templates = {};
templates["sale_item_option_group.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<div class=\"form-group"},{"type":"raw","value":"\" data-id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\" data-type=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"type","match":["type"]}]},{"type":"raw","value":"\">\n    <label class=\"control-label col-sm-3"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]}],"output":[{"type":"raw","value":" required"}]}},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"label","match":["label"]}]},{"type":"raw","value":"</label>\n    <div class=\"col-sm-9\">\n        <div class=\"input-group\">\n            <select name=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"parent","match":["parent"]}]},{"type":"raw","value":"[option_group_"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"][choice]\" class=\"sale-item-option form-control"},{"type":"raw","value":"\""},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]}],"output":[{"type":"raw","value":" required=\"required\""}]}},{"type":"raw","value":"\">\n                "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]},{"type":"Twig.expression.type.operator.unary","value":"not","precidence":3,"associativity":"rightToLeft","operator":"not"}],"output":[{"type":"raw","value":"<option selected=\"selected\" value>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"placeholder","match":["placeholder"]}]},{"type":"raw","value":"</option>"}]}},{"type":"raw","value":"                "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"option","expression":[{"type":"Twig.expression.type.variable","value":"options","match":["options"]}],"output":[{"type":"raw","value":"<option value=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"id"}]},{"type":"raw","value":"\" data-config='"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"config"},{"type":"Twig.expression.type.filter","value":"json_encode","match":["|json_encode","json_encode"]},{"type":"Twig.expression.type.filter","value":"escape","match":["|escape","escape"]}]},{"type":"raw","value":"'>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"label"}]},{"type":"raw","value":"</option>"}]}},{"type":"raw","value":"            </select>\n            <span class=\"input-group-addon sale-item-info\">&nbsp;</span>\n        </div>\n    </div>\n</div>\n"}] });
templates["sale_item_pricing.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<div class=\"form-group sale-item-pricing\">\n    <label class=\"control-label col-sm-4\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"label","match":["label"]}]},{"type":"raw","value":"</label>\n    <div class=\"col-sm-8\">\n        <span class=\"form-control\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"price","match":["price"]}]},{"type":"raw","value":"</span>\n    </div>\n</div>\n"}] });
templates["inventory_line.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<tr data-id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\">\n    <td>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"brand","match":["brand"]}]},{"type":"raw","value":"</td>\n    <td><a href=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"url","match":["url"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"designation","match":["designation"]}]},{"type":"raw","value":"</a></td>\n    <td>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"reference","match":["reference"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"net_price","match":["net_price"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"weight","match":["weight"]}]},{"type":"raw","value":"</td>\n    <td>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"geocode","match":["geocode"]}]},{"type":"raw","value":"</td>\n    <td><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_mode_theme","match":["stock_mode_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_mode_label","match":["stock_mode_label"]}]},{"type":"raw","value":"</span></td>\n    <td><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_state_theme","match":["stock_state_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_state_label","match":["stock_state_label"]}]},{"type":"raw","value":"</span></td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_floor","match":["stock_floor"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"replenishment","match":["replenishment"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"in_stock","match":["in_stock"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"available_stock","match":["available_stock"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"virtual_stock","match":["virtual_stock"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"eda","match":["eda"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"ordered","match":["ordered"]}]},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"pending","match":["pending"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":" (+"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"pending","match":["pending"]}]},{"type":"raw","value":")"}]}},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"received","match":["received"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"adjusted","match":["adjusted"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"sold_theme","match":["sold_theme"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":" bg-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"sold_theme","match":["sold_theme"]}]}]}},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"sold","match":["sold"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"shipped","match":["shipped"]}]},{"type":"raw","value":"</td>\n    <td class=\"text-right\">\n        "},{"type":"raw","value":"\n        <div class=\"btn-group btn-group-xs\">\n            <a href=\"javascript: void(0)\" class=\"btn btn-default quick-edit\" title=\"Modifier\">\n                <i class=\"fa fa-pencil\"></i>\n            </a>\n            <a href=\"javascript: void(0)\" class=\"btn btn-default print-label\" title=\"Étiquette\">\n                <i class=\"fa fa-barcode\"></i>\n            </a>\n            <a href=\"javascript: void(0)\" class=\"btn btn-default stock-units\" title=\"Unités de stock\">\n                <i class=\"fa fa-tasks\"></i>\n            </a>\n            <a href=\"javascript: void(0)\" class=\"btn btn-default treatment\" title=\"Commandes client\">\n                <i class=\"fa fa-user\"></i>\n            </a>\n            <a href=\"javascript: void(0)\" class=\"btn btn-default resupply\" title=\"Commandes fournisseur\">\n                <i class=\"fa fa-truck\"></i>\n            </a>\n        </div>\n    </td>\n</tr>\n"}] });
return templates;
});
