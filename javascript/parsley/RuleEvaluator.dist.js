"use strict";

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var RuleEvaluator = (function () {
    function RuleEvaluator(form, nodeFactories, elemValueExtractor) {
        _classCallCheck(this, RuleEvaluator);

        this.container = form;
        this.reg = /(?:\s*([^ =>!<$~^()]+)\s*((?:==|\!=|\>=|\<=|\>|\<|\$=|\^=|\@=))\s*([^=()&|]+)\s*(&&|\|\|){0,1}){1,}?/g;
        this.optMap = {
            "==": "Equals",
            ">": "GreaterThan",
            ">=": "EqualsOrGreaterThan",
            "<": "LessThan",
            "<=": "EqualsOrLessThan",
            "!=": "Equals",
            "^=": "StartsWith",
            "$=": "EndsWith",
            "@=": "Contains"
        };
        this.nodeFacs = nodeFactories;
        this.elemValueExtractor = elemValueExtractor;
    }

    _createClass(RuleEvaluator, [{
        key: "Evaluate",
        value: function Evaluate(rule) {
            var that = this;
            var paramFuncs = [];
            var paramFuncNames = [];
            var callback = null;
            var ma = that.reg.exec(rule);
            var res = rule.slice();
            var counter = 0;
            while (ma != null) {
                var funcName = "func" + counter++;
                var left = ma[1].split(/\./);
                var opt = ma[2];
                var right = ma[3];

                var selector = "#" + left[0];
                var elem = that.container.find(selector);
                if (elem.length < 1) {
                    res = res.replace(ma[0], "false") + (ma.length == 5 ? " " + ma[4] + " " : "");
                    ma = that.reg.exec(rule);
                    continue;
                }
                var func = null;
                var leftV = that.elemValueExtractor.GetValue(elem);

                func = (function (leftExtFunc, operator, lv, expectedValue) {
                    return function () {
                        var leftElem = RuleNode.CreateNode(lv, that.nodeFacs);
                        if (typeof leftExtFunc === "string" && typeof leftElem[leftExtFunc] === "function") {
                            leftElem = RuleNode.CreateNode(leftElem[leftExtFunc](), that.nodeFacs);
                        }
                        //switch opts, call method on leftElem
                        if (typeof leftElem[that.optMap[operator]] === "function") {
                            return operator === "!=" ? !leftElem[that.optMap[operator]](expectedValue) : leftElem[that.optMap[operator]](expectedValue);
                        }
                    };
                })(left.length > 1 ? left[1] : null, opt, leftV, right.trim());

                res = res.replace(ma[0], funcName + "()" + (typeof ma[4] === "string" ? " " + ma[4] + " " : ""));
                paramFuncs.push(func);
                paramFuncNames.push(funcName);
                ma = that.reg.exec(rule);
            }
            var f = Function(paramFuncNames, "return " + res);
            return f.apply(that, paramFuncs);
        }
    }]);

    return RuleEvaluator;
})();
"use strict";

var _get = function get(_x, _x2, _x3) { var _again = true; _function: while (_again) { var object = _x, property = _x2, receiver = _x3; desc = parent = getter = undefined; _again = false; if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { _x = parent; _x2 = property; _x3 = receiver; _again = true; continue _function; } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } } };

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var RuleNode = (function () {
	function RuleNode(rawValue) {
		_classCallCheck(this, RuleNode);

		this.RawValue = rawValue;
	}

	_createClass(RuleNode, [{
		key: "getNodeType",
		value: function getNodeType() {}
	}, {
		key: "getRawValue",
		value: function getRawValue() {
			return this.RawValue;
		}
	}, {
		key: "Equals",
		value: function Equals(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - GreaterThan.');
		}
	}, {
		key: "GreaterThan",
		value: function GreaterThan(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - GreaterThan.');
		}
	}, {
		key: "EqualsOrGreaterThan",
		value: function EqualsOrGreaterThan(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - EqualsOrGreaterThan.');
		}
	}, {
		key: "LessThan",
		value: function LessThan(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - LessThan.');
		}
	}, {
		key: "EqualsOrLessThan",
		value: function EqualsOrLessThan(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - EqualsOrLessThan.');
		}
	}, {
		key: "StartsWith",
		value: function StartsWith(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - StartsWith.');
		}
	}, {
		key: "EndsWith",
		value: function EndsWith(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - EndsWith.');
		}
	}, {
		key: "Contains",
		value: function Contains(right) {
			throw new Exception(this.getNodeType() + ' does not support this operation - GreaterThan.');
		}

		/* additional functions can be accessed after a dot */
	}, {
		key: "length",
		value: function length() {
			throw new Exception(this.getNodeType() + ' does not support this method - length.');
		}
	}, {
		key: "lower",
		value: function lower() {
			throw new Exception(this.getNodeType() + ' does not support this method - length.');
		}
	}, {
		key: "upper",
		value: function upper() {
			throw new Exception(this.getNodeType() + ' does not support this method - length.');
		}
	}], [{
		key: "CreateNode",
		value: function CreateNode(rawValue, nodeFactories) {
			if (rawValue == null) {
				rawValue = "NULL";
			}
			if (typeof rawValue === "boolean") {
				rawValue = rawValue ? "True" : "False";
			}
			if (typeof nodeFactories == "object") {
				for (var i = 0; i < nodeFactories.length; i++) {
					try {
						var node = nodeFactories[i].CreateNode(rawValue);
						if (node != null) {
							return node;
						}
					} catch (e) {}
				}
			}
			if (typeof rawValue === "string" || typeof rawValue === "number") {
				return new StringRuleNode(rawValue);
			}
			return null;
		}
	}]);

	return RuleNode;
})();

var StringRuleNode = (function (_RuleNode) {
	_inherits(StringRuleNode, _RuleNode);

	function StringRuleNode() {
		_classCallCheck(this, StringRuleNode);

		_get(Object.getPrototypeOf(StringRuleNode.prototype), "constructor", this).apply(this, arguments);
	}

	_createClass(StringRuleNode, [{
		key: "getNodeType",
		value: function getNodeType() {
			return 'String';
		}
	}, {
		key: "Equals",
		value: function Equals(right) {
			var strRight = "" + right;
			return this.RawValue == strRight;
		}
	}, {
		key: "GreaterThan",
		value: function GreaterThan(right) {
			var l = parseFloat(this.RawValue);
			var r = parseFloat(right);
			return l > r;
		}
	}, {
		key: "EqualsOrGreaterThan",
		value: function EqualsOrGreaterThan(right) {
			var l = parseFloat(this.RawValue);
			var r = parseFloat(right);
			return l >= r;
		}
	}, {
		key: "LessThan",
		value: function LessThan(right) {
			var l = parseFloat(this.RawValue);
			var r = parseFloat(right);
			return l < r;
		}
	}, {
		key: "EqualsOrLessThan",
		value: function EqualsOrLessThan(right) {
			var l = parseFloat(this.RawValue);
			var r = parseFloat(right);
			return l <= r;
		}
	}, {
		key: "StartsWith",
		value: function StartsWith(right) {
			return this.RawValue.slice(0, right.length) == right;
		}
	}, {
		key: "EndsWith",
		value: function EndsWith(right) {
			return this.RawValue.slice(-1 * right.length) == right;
		}
	}, {
		key: "Contains",
		value: function Contains(right) {
			return this.RawValue.includes(right);
		}

		/* additional functions can be accessed after a dot */
	}, {
		key: "length",
		value: function length() {
			return this.RawValue.length;
		}
	}, {
		key: "lower",
		value: function lower() {
			return this.RawValue.toLowerCase();
		}
	}, {
		key: "upper",
		value: function upper() {
			return this.RawValue.toUpperCase();
		}
	}]);

	return StringRuleNode;
})(RuleNode);

var ArrayRuleNode = (function (_RuleNode2) {
	_inherits(ArrayRuleNode, _RuleNode2);

	function ArrayRuleNode() {
		_classCallCheck(this, ArrayRuleNode);

		_get(Object.getPrototypeOf(ArrayRuleNode.prototype), "constructor", this).apply(this, arguments);
	}

	_createClass(ArrayRuleNode, [{
		key: "getNodeType",
		value: function getNodeType() {
			return 'Array';
		}
	}, {
		key: "Equals",
		value: function Equals(right) {
			return this.RawValue.includes(right);
		}
	}, {
		key: "Contains",
		value: function Contains(right) {
			return this.RawValue.includes(right);
		}

		/* additional functions can be accessed after a dot */
	}, {
		key: "length",
		value: function length() {
			return this.RawValue.length;
		}
	}]);

	return ArrayRuleNode;
})(RuleNode);

var ArrayNodeFactory = (function () {
	function ArrayNodeFactory() {
		_classCallCheck(this, ArrayNodeFactory);
	}

	_createClass(ArrayNodeFactory, null, [{
		key: "CreateNode",
		value: function CreateNode(rawValue) {
			if (Array.isArray(rawValue)) {
				return new ArrayRuleNode(rawValue);
			}
			return null;
		}
	}, {
		key: "ConvertRightValue",
		value: function ConvertRightValue(right) {
			return right;
		}
	}]);

	return ArrayNodeFactory;
})();

var DateRuleNode = (function (_StringRuleNode) {
	_inherits(DateRuleNode, _StringRuleNode);

	function DateRuleNode() {
		_classCallCheck(this, DateRuleNode);

		_get(Object.getPrototypeOf(DateRuleNode.prototype), "constructor", this).apply(this, arguments);
	}

	_createClass(DateRuleNode, [{
		key: "getNodeType",
		value: function getNodeType() {
			return "Date";
		}
	}, {
		key: "Equals",
		value: function Equals(right) {
			if (this.RawValue == null && right == null) {
				return true;
			}
			if (this.RawValue == null || right == null) {
				return false;
			}
			return this.RawValue - DateNodeFactory.ConvertRightValue(right) == 0;
		}
	}, {
		key: "GreaterThan",
		value: function GreaterThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("GreaterThan does not support null compare");
			}
			return this.RawValue - DateNodeFactory.ConvertRightValue(right) > 0;
		}
	}, {
		key: "EqualsOrGreaterThan",
		value: function EqualsOrGreaterThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("EqualsOrGreaterThan does not support null compare");
			}
			return this.RawValue - DateNodeFactory.ConvertRightValue(right) >= 0;
		}
	}, {
		key: "LessThan",
		value: function LessThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("LessThan does not support null compare");
			}
			return this.RawValue - DateNodeFactory.ConvertRightValue(right) < 0;
		}
	}, {
		key: "EqualsOrLessThan",
		value: function EqualsOrLessThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("EqualsOrLessThan does not support null compare");
			}
			return this.RawValue - DateNodeFactory.ConvertRightValue(right) <= 0;
		}
	}]);

	return DateRuleNode;
})(StringRuleNode);

var DateNodeFactory = (function () {
	function DateNodeFactory() {
		_classCallCheck(this, DateNodeFactory);
	}

	_createClass(DateNodeFactory, null, [{
		key: "CreateNode",
		value: function CreateNode(rawValue) {
			if (typeof rawValue === "string" && /^\d{4}\-\d{2}\-\d{2}$/g.test(rawValue)) {
				return new DateRuleNode(new Date(rawValue));
			} else if (rawValue instanceof Date) {
				return new DateRuleNode(rawValue);
			}
			return null;
		}
	}, {
		key: "ConvertRightValue",
		value: function ConvertRightValue(right) {
			var res = right;
			if (typeof right === "string" && /^\d{4}\-\d{2}\-\d{2}$/g.test(right)) {
				res = new Date(right + " 00:00:00");
			}
			if (!(res instanceof Date)) {
				throw new Exception("Value is not a valid Date object or datetime string (e.g. 2015-02-21).");
			}
			return res;
		}
	}]);

	return DateNodeFactory;
})();

var TimeRuleNode = (function (_StringRuleNode2) {
	_inherits(TimeRuleNode, _StringRuleNode2);

	function TimeRuleNode() {
		_classCallCheck(this, TimeRuleNode);

		_get(Object.getPrototypeOf(TimeRuleNode.prototype), "constructor", this).apply(this, arguments);
	}

	_createClass(TimeRuleNode, [{
		key: "getNodeType",
		value: function getNodeType() {
			return "Time";
		}
	}, {
		key: "Equals",
		value: function Equals(right) {
			if (this.RawValue == null && right == null) {
				return true;
			}
			if (this.RawValue == null || right == null) {
				return false;
			}
			return this.RawValue - TimeNodeFactory.ConvertRightValue(right) == 0;
		}
	}, {
		key: "GreaterThan",
		value: function GreaterThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("GreaterThan does not support null compare");
			}
			return this.RawValue - TimeNodeFactory.ConvertRightValue(right) > 0;
		}
	}, {
		key: "EqualsOrGreaterThan",
		value: function EqualsOrGreaterThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("EqualsOrGreaterThan does not support null compare");
			}
			return this.RawValue - TimeNodeFactory.ConvertRightValue(right) >= 0;
		}
	}, {
		key: "LessThan",
		value: function LessThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("LessThan does not support null compare");
			}
			return this.RawValue - TimeNodeFactory.ConvertRightValue(right) < 0;
		}
	}, {
		key: "EqualsOrLessThan",
		value: function EqualsOrLessThan(right) {
			if (this.RawValue == null || right == null) {
				throw new Exception("EqualsOrLessThan does not support null compare");
			}
			return this.RawValue - TimeNodeFactory.ConvertRightValue(right) <= 0;
		}
	}]);

	return TimeRuleNode;
})(StringRuleNode);

var TimeNodeFactory = (function () {
	function TimeNodeFactory() {
		_classCallCheck(this, TimeNodeFactory);
	}

	_createClass(TimeNodeFactory, null, [{
		key: "CreateNode",
		value: function CreateNode(rawValue) {
			if (rawValue.slice(-2).toLowerCase() == "am" || rawValue.slice(-2).toLowerCase() == "pm") {
				var parts = rawValue.slice(0, -2).split(/:/g);
				var ampm = rawValue.slice(-2).toLowerCase();
				parts[0] = parseInt(parts[0] % 12) + (ampm == "am" ? 0 : 12);
				parts[0] = parts[0] < 10 ? "0" + parts[0] : parts[0];
				rawValue = parts.join(":").trim();
			}
			if (typeof rawValue === "string" && /^\d{2}\:\d{2}\:\d{2}$/g.test(rawValue)) {
				return new TimeRuleNode(new Date("2015/09/17 " + rawValue));
			}
			return null;
		}
	}, {
		key: "ConvertRightValue",
		value: function ConvertRightValue(right) {
			var res = null;
			right = right.trim();

			if (right.slice(-2).toLowerCase() == "am" || right.slice(-2).toLowerCase() == "pm") {
				var parts = right.slice(0, -2).split(/:/g);
				var ampm = right.slice(-2).toLowerCase();
				parts[0] = parseInt(parts[0] % 12) + (ampm == "am" ? 0 : 12);
				parts[0] = parts[0] < 10 ? "0" + parts[0] : parts[0];
				right = parts.join(":").trim();
			}

			if (typeof right === "string" && /^\d{2}\:\d{2}\:\d{2}$/g.test(right)) {
				res = new Date("2015/09/17 " + right);
				if (!(res instanceof Date)) {
					throw new Exception(right + " is not a valid datetime string (15:34:23).");
				}
			}
			return res;
		}
	}]);

	return TimeNodeFactory;
})();
"use strict";

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var SilverStripeElementValueExtractor = (function () {
    function SilverStripeElementValueExtractor() {
        _classCallCheck(this, SilverStripeElementValueExtractor);
    }

    _createClass(SilverStripeElementValueExtractor, null, [{
        key: "GetValue",
        value: function GetValue($elem) {
            var leftV = null;
            if ($elem.is(".field.nullable")) {
                if ($elem.find("input[type='checkbox']").prop('checked')) {
                    leftV = null;
                } else {
                    $elem = $elem.clone().find("input[type='checkbox']").remove().end().addClass(function () {
                        var el = jQuery(this).find("input,textarea,select");
                        if (el.is("input[type='hidden']")) {
                            return el.siblings("span").attr("class");
                        } else {
                            return el.attr("class");
                        }
                    });
                }
            }

            if ($elem.is(".field.checkbox")) {
                leftV = $elem.find("input[type='checkbox']").prop("checked");
            } else if ($elem.is(".field.checkboxset")) {
                leftV = $elem.find("input[type='checkbox']:checked").map(function () {
                    return $(this).val();
                }).get();
            } else if ($elem.is(".field.optionset")) {
                leftV = $elem.find("input[type='radio']:checked").val();
            } else if ($elem.is(".field.dropdown")) {
                leftV = $elem.find("select").val();
            } else if ($elem.is(".field.textarea")) {
                leftV = $elem.find("textarea").val();
            } else if ($elem.is(".field.date")) {
                leftV = $elem.find("input.date").datepicker("getDate");
            } else if ($elem.is(".field.readonly")) {
                leftV = $elem.find(".readonly").text();
            } else if ($elem.is(".field.text")) {
                leftV = $elem.find("input[type='text']").val();
            }
            return leftV;
        }
    }]);

    return SilverStripeElementValueExtractor;
})();
//# sourceMappingURL=RuleEvaluator.dist.js.map