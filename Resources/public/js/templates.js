define("ekyna-product/templates", ["twig"], function(Twig) {
var templates = {};
templates["sale_item_option_group.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<div class=\"form-group form-group-sm\" data-id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\" data-type=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"type","match":["type"]}]},{"type":"raw","value":"\">\n    <label class=\"control-label col-xs-3"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]}],"output":[{"type":"raw","value":" required"}]}},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"label","match":["label"]}]},{"type":"raw","value":"</label>\n    <div class=\"col-xs-9\">\n        <select name=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"parent","match":["parent"]}]},{"type":"raw","value":"[option_group_"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"][choice]\"\n                "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]}],"output":[{"type":"raw","value":" required=\"required\""}]}},{"type":"raw","value":" class=\"sale-item-option form-control\">\n            "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"required","match":["required"]},{"type":"Twig.expression.type.operator.unary","value":"not","precidence":3,"associativity":"rightToLeft","operator":"not"}],"output":[{"type":"raw","value":"<option disabled=\"disabled\" selected=\"selected\" value=\"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"placeholder","match":["placeholder"]}]},{"type":"raw","value":"</option>"}]}},{"type":"raw","value":"            "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"option","expression":[{"type":"Twig.expression.type.variable","value":"options","match":["options"]}],"output":[{"type":"raw","value":"<option value=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"id"}]},{"type":"raw","value":"\" data-price=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"price"}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"option","match":["option"]},{"type":"Twig.expression.type.key.period","key":"label"}]},{"type":"raw","value":"</option>"}]}},{"type":"raw","value":"        </select>\n    </div>\n</div>\n"}] });
templates["sale_item_pricing.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<div class=\"form-group form-group-sm sale-item-pricing\">\n    <label class=\"control-label col-sm-6\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"total"}]},{"type":"raw","value":"</label>\n    <div class=\"col-sm-6\">\n    "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"detailed","match":["detailed"]}],"output":[{"type":"raw","value":"        <div class=\"input-group input-group-sm\">\n            <span class=\"form-control\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"totalPrice","match":["totalPrice"]}]},{"type":"raw","value":"</span>\n            <div class=\"input-group-btn dropup\">\n                <button type=\"button\" class=\"btn btn-default dropdown-toggle\"\n                        data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">\n                    <span class=\"fa fa-info\"></span>\n                </button>\n                <ul class=\"dropdown-menu dropdown-menu-right\">\n                    <li>\n                        "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.number","value":0,"match":["0",null]},{"type":"Twig.expression.type.variable","value":"rules","match":["rules"]},{"type":"Twig.expression.type.key.period","key":"length"},{"type":"Twig.expression.type.operator.binary","value":"<","precidence":8,"associativity":"leftToRight","operator":"<"}],"output":[{"type":"raw","value":"                        <p>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"rule_table"}]},{"type":"raw","value":"</p>\n                        <table class=\"table\">\n                            <thead>\n                            <tr>\n                                <th>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"quantity"}]},{"type":"raw","value":"</th>\n                                <th>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"discount"}]},{"type":"raw","value":"</th>\n                                <th>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"unit_price"}]},{"type":"raw","value":"</th>\n                            </tr>\n                            </thead>\n                            <tbody>\n                            "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"rule","expression":[{"type":"Twig.expression.type.variable","value":"rules","match":["rules"]}],"output":[{"type":"raw","value":"                                <tr data-quantity=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rule","match":["rule"]},{"type":"Twig.expression.type.key.period","key":"quantity"}]},{"type":"raw","value":"\" data-percent=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rule","match":["rule"]},{"type":"Twig.expression.type.key.period","key":"percent"}]},{"type":"raw","value":"\"\n                                    "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"rule","match":["rule"]},{"type":"Twig.expression.type.key.period","key":"active"},{"type":"Twig.expression.type.bool","value":true},{"type":"Twig.expression.type.operator.binary","value":"==","precidence":9,"associativity":"leftToRight","operator":"=="}],"output":[{"type":"raw","value":"class=\"success\""}]}},{"type":"raw","value":">\n                                    <td class=\"text\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rule","match":["rule"]},{"type":"Twig.expression.type.key.period","key":"label"}]},{"type":"raw","value":"</td>\n                                    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rule","match":["rule"]},{"type":"Twig.expression.type.key.period","key":"f_percent"}]},{"type":"raw","value":"</td>\n                                    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rule","match":["rule"]},{"type":"Twig.expression.type.key.period","key":"f_price"}]},{"type":"raw","value":"</td>\n                                </tr>\n                            "}]}},{"type":"raw","value":"                            </tbody>\n                        </table>\n                        "}]}},{"type":"raw","value":"                        "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.number","value":0,"match":["0",null]},{"type":"Twig.expression.type.variable","value":"lines","match":["lines"]},{"type":"Twig.expression.type.key.period","key":"length"},{"type":"Twig.expression.type.operator.binary","value":"<","precidence":8,"associativity":"leftToRight","operator":"<"}],"output":[{"type":"raw","value":"                        <p>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"trans","match":["trans"]},{"type":"Twig.expression.type.key.period","key":"price_table"}]},{"type":"raw","value":"</p>\n                        <table class=\"table\">\n                            <tbody>\n                            "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"line","expression":[{"type":"Twig.expression.type.variable","value":"lines","match":["lines"]}],"output":[{"type":"raw","value":"                                <tr"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"line","match":["line"]},{"type":"Twig.expression.type.key.period","key":"class"}],"output":[{"type":"raw","value":" class=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"line","match":["line"]},{"type":"Twig.expression.type.key.period","key":"class"}]},{"type":"raw","value":"\""}]}},{"type":"raw","value":">\n                                    <td class=\"text\" width=\"80%\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"line","match":["line"]},{"type":"Twig.expression.type.key.period","key":"label"}]},{"type":"raw","value":"</td>\n                                    <td class=\"number\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"line","match":["line"]},{"type":"Twig.expression.type.key.period","key":"price"}]},{"type":"raw","value":"</td>\n                                </tr>\n                            "}]}},{"type":"raw","value":"                            </tbody>\n                        </table>\n                        "}]}},{"type":"raw","value":"                    </li>\n                </ul>\n            </div>\n        </div>\n    "}]}},{"type":"logic","token":{"type":"Twig.logic.type.else","match":["else"],"output":[{"type":"raw","value":"        <span class=\"form-control\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"totalPrice","match":["totalPrice"]}]},{"type":"raw","value":"</span>\n    "}]}},{"type":"raw","value":"    </div>\n</div>\n\n"}] });
return templates;
});
