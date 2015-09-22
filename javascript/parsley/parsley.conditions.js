(function($) {
	$.fn.conditionalParsley = function(options) {
		var container = this;
		container.find(".required").removeClass("required");
		var re = new RuleEvaluator(container, [ArrayNodeFactory, DateNodeFactory, TimeNodeFactory], SilverStripeElementValueExtractor);
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
