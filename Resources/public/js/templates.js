define("ekyna-product/templates", ["twig"], function(Twig) {
var templates = {};
templates["@EkynaProduct/Js/sale_item_option_group.html.twig"] = Twig.twig({ id: "@EkynaProduct/Js/sale_item_option_group.html.twig", data: [{"type":"raw","value":"<div class=\"form-group"},{"type":"raw","value":"\" data-id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\" data-position=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"position","match":["position"]}]},{"type":"raw","value":"\">\n    <label class=\"control-label col-sm-3"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]}],"output":[{"type":"raw","value":" required"}]}},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"label","match":["label"]}]},{"type":"raw","value":"</label>\n    <div class=\"col-sm-9\">\n        <div class=\"input-group\">\n            <select name=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"parent","match":["parent"]}]},{"type":"raw","value":"[option_group_"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"][choice]\" class=\"sale-item-option form-control"},{"type":"raw","value":"\""},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]}],"output":[{"type":"raw","value":" required=\"required\""}]}},{"type":"raw","value":">\n                "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]},{"type":"Twig.expression.type.operator.unary","value":"not","precidence":3,"associativity":"rightToLeft","operator":"not"}],"output":[{"type":"raw","value":"<option selected=\"selected\" value>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"placeholder","match":["placeholder"]}]},{"type":"raw","value":"</option>"}]}},{"type":"raw","value":"                "},{"type":"logic","token":{"type":"Twig.logic.type.for","keyVar":null,"valueVar":"option","expression":[{"type":"Twig.expression.type.variable","value":"options","match":["options"]}],"output":[{"type":"raw","value":"<option value=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"id"}]},{"type":"raw","value":"\" data-config='"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"config"},{"type":"Twig.expression.type.filter","value":"json_encode","match":["|json_encode","json_encode"]},{"type":"Twig.expression.type.filter","value":"escape","match":["|escape","escape"]}]},{"type":"raw","value":"'>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"label"}]},{"type":"raw","value":"</option>"}]}},{"type":"raw","value":"            </select>\n            <span class=\"input-group-addon sale-item-info\">&nbsp;</span>\n        </div>\n    </div>\n</div>\n"}] });
templates["@EkynaProduct/Js/sale_item_offers.html.twig"] = Twig.twig({ id: "@EkynaProduct/Js/sale_item_offers.html.twig", data: [{"type":"raw","value":"<table class=\"table table-condensed table-striped table-alt-head "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"class","match":["class"]}]},{"type":"raw","value":"\">\n    <thead>\n        <tr>\n            <th>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"quantity"}]},{"type":"raw","value":"</th>\n            <th class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"discount"}]},{"type":"raw","value":"</th>\n            <th class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"unit_price"}]},{"type":"raw","value":"</th>\n        </tr>\n    </thead>\n    <tbody>\n    "},{"type":"logic","token":{"type":"Twig.logic.type.for","keyVar":null,"valueVar":"offer","expression":[{"type":"Twig.expression.type.variable","value":"offers","match":["offers"]}],"output":[{"type":"raw","value":"        <tr"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"quantity","match":["quantity"]},{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"min"},{"type":"Twig.expression.type.operator.binary","value":">=","precidence":8,"associativity":"leftToRight","operator":">="},{"type":"Twig.expression.type.subexpression.end","value":")","match":[")"],"expression":true,"params":[{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"max"},{"type":"Twig.expression.type.test","filter":"null"},{"type":"Twig.expression.type.variable","value":"quantity","match":["quantity"]},{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"max"},{"type":"Twig.expression.type.operator.binary","value":"<=","precidence":8,"associativity":"leftToRight","operator":"<="},{"type":"Twig.expression.type.operator.binary","value":"or","precidence":14,"associativity":"leftToRight","operator":"or"}]},{"type":"Twig.expression.type.operator.binary","value":"and","precidence":13,"associativity":"leftToRight","operator":"and"}],"output":[{"type":"raw","value":" class=\"success\""}]}},{"type":"raw","value":">\n            <td>\n            "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"max"},{"type":"Twig.expression.type.test","filter":"null"}],"output":[{"type":"raw","value":"                "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"from"},{"type":"Twig.expression.type.filter","value":"replace","match":["|replace","replace"],"params":[{"type":"Twig.expression.type.parameter.start","value":"(","match":["("]},{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"%min%"},{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"min"},{"type":"Twig.expression.type.object.end","value":"}","match":["}"]},{"type":"Twig.expression.type.parameter.end","value":")","match":[")"],"expression":false}]}]},{"type":"raw","value":"\n            "}]}},{"type":"logic","token":{"type":"Twig.logic.type.else","match":["else"],"output":[{"type":"raw","value":"                "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"range"},{"type":"Twig.expression.type.filter","value":"replace","match":["|replace","replace"],"params":[{"type":"Twig.expression.type.parameter.start","value":"(","match":["("]},{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"%min%"},{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"min"},{"type":"Twig.expression.type.comma"},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"%max%"},{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"max"},{"type":"Twig.expression.type.object.end","value":"}","match":["}"]},{"type":"Twig.expression.type.parameter.end","value":")","match":[")"],"expression":false}]}]},{"type":"raw","value":"\n            "}]}},{"type":"raw","value":"            </td>\n            <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"percent"}]},{"type":"raw","value":"%</td>\n            <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"offer","match":["offer"]},{"type":"Twig.expression.type.key.period","key":"price"}]},{"type":"raw","value":"</td>\n        </tr>\n    "}]}},{"type":"raw","value":"    </tbody>\n</table>"}] });
templates["@EkynaProduct/Js/inventory_line.html.twig"] = Twig.twig({ id: "@EkynaProduct/Js/inventory_line.html.twig", data: [{"type":"raw","value":"<tr data-id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\" data-summary='"},{"type":"output","stack":[{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"route"},{"type":"Twig.expression.type.string","value":"ekyna_product_product_admin_summary"},{"type":"Twig.expression.type.comma"},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"parameters"},{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"productId"},{"type":"Twig.expression.type.variable","value":"id","match":["id"]},{"type":"Twig.expression.type.object.end","value":"}","match":["}"]},{"type":"Twig.expression.type.object.end","value":"}","match":["}"]},{"type":"Twig.expression.type.filter","value":"json_encode","match":["|json_encode","json_encode"]}]},{"type":"raw","value":"'>\n    <td class=\"input\"><input name=\"id[]\" type=\"checkbox\" value=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\"></td>\n    <td class=\"input\">\n        <a href=\"javascript: void(0)\" class=\"fa fa-bookmark"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"bookmark","match":["bookmark"]},{"type":"Twig.expression.type.operator.unary","value":"not","precidence":3,"associativity":"rightToLeft","operator":"not"}],"output":[{"type":"raw","value":"-o"}]}},{"type":"raw","value":" bookmark\" title=\"Bookmark\"></a>\n    </td>\n    <td class=\"text\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"brand","match":["brand"]}]},{"type":"raw","value":"</td>\n    <td class=\"designation\"><a href=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"url","match":["url"]}]},{"type":"raw","value":"\" target=\"_blank\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"designation","match":["designation"]}]},{"type":"raw","value":"</a></td>\n    <td class=\"text2\" data-clipboard-copy=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"reference","match":["reference"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"reference","match":["reference"]}]},{"type":"raw","value":"</td>\n    <td class=\"text text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"net_price","match":["net_price"]}]},{"type":"raw","value":"</td>\n    <td class=\"text text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"weight","match":["weight"]}]},{"type":"raw","value":"</td>\n    <td class=\"text2\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"geocode","match":["geocode"]}]},{"type":"raw","value":"</td>\n    <td class=\"boolean\"><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"visible_theme","match":["visible_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"visible_label","match":["visible_label"]}]},{"type":"raw","value":"</span></td>\n    <td class=\"boolean\"><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"quote_only_theme","match":["quote_only_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"quote_only_label","match":["quote_only_label"]}]},{"type":"raw","value":"</span></td>\n    <td class=\"boolean\"><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"end_of_life_theme","match":["end_of_life_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"end_of_life_label","match":["end_of_life_label"]}]},{"type":"raw","value":"</span></td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_floor","match":["stock_floor"]}]},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"replenishment","match":["replenishment"]}]},{"type":"raw","value":"</td>\n    <td class=\"state\"><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_mode_theme","match":["stock_mode_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_mode_label","match":["stock_mode_label"]}]},{"type":"raw","value":"</span></td>\n    <td class=\"state\"><span class=\"label label-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_state_theme","match":["stock_state_theme"]}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_state_label","match":["stock_state_label"]}]},{"type":"raw","value":"</span></td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"in_stock","match":["in_stock"]}]},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"available_stock","match":["available_stock"]}]},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"virtual_stock","match":["virtual_stock"]}]},{"type":"raw","value":"</td>\n    <td class=\"text text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"eda","match":["eda"]}]},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"ordered","match":["ordered"]}]},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"pending","match":["pending"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":" (+"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"pending","match":["pending"]}]},{"type":"raw","value":")"}]}},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"received","match":["received"]}]},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"adjusted","match":["adjusted"]}]},{"type":"raw","value":"</td>\n    <td class=\"number"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"sold_theme","match":["sold_theme"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":" bg-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"sold_theme","match":["sold_theme"]}]}]}},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"sold","match":["sold"]}]},{"type":"raw","value":"</td>\n    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"shipped","match":["shipped"]}]},{"type":"raw","value":"</td>\n    <td class=\"action\">\n        "},{"type":"raw","value":"\n        <a href=\"javascript: void(0)\" class=\"btn btn-xs btn-default quick-edit\" title=\"Edit\">\n            <i class=\"fa fa-pencil\"></i>\n        </a>\n        <a href=\"javascript: void(0)\" class=\"btn btn-xs btn-default print-label\" title=\"Print label\">\n            <i class=\"fa fa-barcode\"></i>\n        </a>\n        <a href=\"javascript: void(0)\" class=\"btn btn-xs btn-default stock-units\" title=\"Stock units\">\n            <i class=\"fa fa-tasks\"></i>\n        </a>\n        <a href=\"javascript: void(0)\" class=\"btn btn-xs btn-default treatment\" title=\"Customer orders\">\n            <i class=\"fa fa-user\"></i>\n        </a>\n        <a href=\"javascript: void(0)\" class=\"btn btn-xs btn-default resupply\" title=\"Supplier orders\">\n            <i class=\"fa fa-truck\"></i>\n        </a>\n    </td>\n</tr>\n"}] });
return templates;
});
