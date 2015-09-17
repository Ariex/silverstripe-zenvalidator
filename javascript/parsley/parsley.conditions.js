window.RuleEvaluator = window.RuleEvaluator || function($, $form) {
	var $container = $form;
	var reg = /(?:\s*([^ =>!<$~^()]+)\s*((?:==|!=|\>=|\<=|\>|\<))\s*([^=()&|]+)\s*(&&|\|\|){0,1}){1,}?/g;
	this.Evaluate = function(rule) {
		var paramFuncs = [];
		var paramFuncNames = [];
		var callback = null;
		var ms = rule.match(reg);
		var res = rule.slice();
		for (var i = 0; i < ms.length; i++) {
			var ma = ms[i];
			var left = ma[1].split(/\./);
			var opt = ma[2];
			var right = ma[3];

			var selector = "#" + left[0];
			var elem = $(selector);
			if (elem.length < 1) {
				res = res.replace(ma, "true");
				continue;
			}
			var func = null;
			if (elem.is(".field.checkbox")) {
				// single check box
				func = (function($elem, expectedValue) {
					return function() {
						return $elem.find("input[type='checkbox']").prop("checked") == expectedValue;
					};
				})(elem, right == "True");
			} else if (elem.is(".field.checkboxset")) {
				func = (function($elem, expectVal) {
					return function() {
						var vals = $elem.find("input[type='checkbox']:checked").map(function() {
							return $(this).val();
						}).get();
						return vals.indexOf(expectVal) > -1;
					};
				})(elem, right);
			} else if(elem.is(".field.text")) {
				// normal input fields or textarea or select
				func = (function($elem, expectVal) {
					return function() {
						return $elem.find("input").val() == expectVal;
					};
				})(elem, right);
			}else if(elem.is(".field.optionset")){
				func = (function ($elem, expectVal) {
					return function () {
						return $elem.find("input[type='radio']:checked").val() == expectVal;
					};
				})(elem, right);
			}else if(elem.is(".field.dropdown")){
				func = (function ($elem, expectVal) {
					return function () {
						return $elem.find("select").val() == expectVal;
					};
				})(elem, right);
			}else if(elem.is(".field.textarea")){
				func = (function($elem, expectVal) {
					return function() {
						return $elem.find("textarea").val() == expectVal;
					};
				})(elem, right);
			}
			res = res.replace(ma, left[0] + "()");
			paramFuncs.push(func);
			paramFuncNames.push(left[0]);
		}
		var f = Function(paramFuncNames, "return " + res);
		return f.apply(this, paramFuncs);
	}
};

(function($) {
	$.fn.conditionalParsley = function(options) {
		var container = this;
		container.find(".required").removeClass("required");
		var re = new RuleEvaluator($, container);
		var returnObj = {
			Validate: function() {
				// pre-process elements according to conditions and current status of each form element
				var condAttrReg = /^data-parsley.*?-condition$/g;
				container.find("*").each(function() {
					var that = $(this);
					var attrs = this.attributes;
					var condAttr = "";
					for (var i = 0, l = attrs.length; i < l; i++) {
						var k = attrs[i].name;
						if (condAttrReg.test(k)) {
							condAttr = k;
							break;
						}
					}
					if (condAttr.length < 1) {
						return;
					}
					// evaluate conditions
					var evalRes = re.Evaluate(that.attr(condAttr));
					if(evalRes){
						that.attr("data-parsley-required", "true");
					}else{
						that.attr("data-parsley-required", "false");
					}
				});

				// perform parsley validation
				if(container.parsley().validate()){
					return true;
				}else{
					return false;
				}
			}
		};

		container.on("submit.Parsley", function() {
			return returnObj.Validate();
		});
		return returnObj;
	};
})(jQuery);
