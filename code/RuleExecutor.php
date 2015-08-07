<?php

interface INodeComparable {
	public function Equals($right);
	public function GreaterThan($right);
	public function EqualsOrGreaterThan($right);
	public function LessThan($right);
	public function EqualsOrLessThan($right);
	public function StartsWith($right);
	public function EndsWith($right);
	public function Contains($right);
}

interface IExtendFunctionSupportable {
	public function length();
	public function lower();
	public function upper();
}

interface IRuleNodeFactory {
	// create left node
	public function CreateNode($rawValue);

	// create right node
	public function ConvertRightValue($right);
}

abstract class RuleNode extends Object implements INodeComparable, IExtendFunctionSupportable {
	protected $RawValue;

	public function __construct($rawValue) {
		$this->RawValue = $rawValue;
	}

	public static function CreateNode($rawValue) {
		if (is_null($rawValue)) {
			$rawValue = "NULL";
		}
		if (is_bool($rawValue)) {
			$rawValue = $rawValue ? "True" : "False";
		}
		if (is_string($rawValue) || is_numeric($rawValue)) {
			return StringRuleNode::create($rawValue);
		}
		//  else if (is_array($rawValue)) {
		// 	return ArrayRuleNode::create($rawValue);
		// }
		//  else if ($rawValue instanceof DateTime) {
		// 	return DateTimeRuleNode::create($rawValue);
		// }
		return null;
	}

	public abstract function getNodeType();

	public function getRawValue() {
		return $this->RawValue;
	}

	/* operators */
	public function Equals($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - GreaterThan.');
	}

	public function GreaterThan($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - GreaterThan.');
	}

	public function EqualsOrGreaterThan($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - EqualsOrGreaterThan.');
	}

	public function LessThan($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - LessThan.');
	}

	public function EqualsOrLessThan($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - EqualsOrLessThan.');
	}

	public function StartsWith($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - StartsWith.');
	}

	public function EndsWith($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - EndsWith.');
	}

	public function Contains($right) {
		throw new Exception($this->getNodeType() . ' does not support this operation - GreaterThan.');
	}

	/* additional functions can be accessed after a dot */
	public function length() {
		throw new Exception($this->getNodeType() . ' does not support this method - length.');
	}

	public function lower() {
		throw new Exception($this->getNodeType() . ' does not support this method - length.');
	}

	public function upper() {
		throw new Exception($this->getNodeType() . ' does not support this method - length.');
	}
}

class StringRuleNode extends RuleNode {
	public function getNodeType() {
		return 'String';
	}

	public function Equals($right) {
		$strRight = (string) $right;
		// r($this->RawValue);
		// r($right);
		// r((string) $right);
		// r($this->RawValue == (string) $right);
		// if ($this->RawValue == 1 && strtolower($strRight) == 'true') {
		// 	r("return for true");
		// 	return true;
		// } else if ($this->RawValue == 0 && strtolower($strRight) == 'false') {
		// 	r("return for false");
		// 	return true;
		// }
		return $this->RawValue == (string) $right;
	}

	public function GreaterThan($right) {
		$l = floatval($this->RawValue);
		$r = floatval($right);
		return $l > $r;
	}

	public function EqualsOrGreaterThan($right) {
		$l = floatval($this->RawValue);
		$r = floatval($right);
		return $l >= $r;
	}

	public function LessThan($right) {
		$l = floatval($this->RawValue);
		$r = floatval($right);
		return $l < $r;
	}

	public function EqualsOrLessThan($right) {
		$l = floatval($this->RawValue);
		$r = floatval($right);
		return $l <= $r;
	}

	public function StartsWith($right) {
		return substr($this->RawValue, 0, strlen($right)) == $right;
	}

	public function EndsWith($right) {
		return substr($this->RawValue, -strlen($right)) == $right;
	}

	public function Contains($right) {
		return strpos($this->RawValue, $right) !== false;
	}

	/* additional functions can be accessed after a dot */
	public function length() {
		return strlen($this->RawValue);
	}

	public function lower() {
		return strtolower($this->RawValue);
	}

	public function upper() {
		return strtoupper($this->RawValue);
	}
}

class RuleExecutor extends Object {
	public function Execute($statement, $fields, ...$nodeFactories) {
		// r("orig statement {$statement}");
		if (strlen($statement) < 1) {
			return true;
		}
		$regex     = '/(?:\s*([^ =>!<$~^()]+)\s*((?:==|!=|\>=|\<=|\>|\<))\s*([^=()&|]+)\s*(&&|\|\|){0,1}){1,}?/';
		$knownOpts = array('==');
		preg_match_all($regex, $statement, $matches);
		$mres            = $matches ? $matches : array();
		$lenMatchedRules = sizeof($mres[0]);
		$lefts           = $mres[1];
		// r($mres);
		$funcMap = array();
		for ($i = 0; $i < sizeof($lefts); $i++) {
			$parts    = preg_split('/\./', $lefts[$i]);
			$realLeft = $parts[0];
			// r("Field {$realLeft} exist");
			$f = $fields->dataFieldByName($realLeft);
			if ($f == null) {
				throw new Exception("Field {$realLeft} does not exist");
			}
			$opt = $mres[2][$i];
			$val = $f->Value();
			if ($f instanceof CheckBoxField) {
				$val = (bool) $val;
			}
			if ($f instanceof DateField || $f instanceof TimeField) {
				$val = new DateTime($f->dataValue());
				if (!($val instanceof DateTime)) {
					$val = null;
				}
			}
			// r($val);
			// r(is_null($val));
			// if (!is_string($val)) {
			// 	return true;
			// }

			// different type of controls have different type of value
			// for textboxfield it is just a string, bot checkboxsetfield, it is an array
			$left        = RuleNode::CreateNode($val);
			$currFactory = null;
			if ($left == null && $nodeFactories != null) {
				foreach ($nodeFactories as $factory) {
					if (!($factory instanceof IRuleNodeFactory)) {
						continue;
					}
					$left = $factory->CreateNode($val);
					if ($left != null) {
						$currFactory = $factory;
						break;
					}
				}
			}
			// r($left);
			if ($left == null) {
				throw new Exception('Unsupported value at left.');
			}
			// $left  = strtolower((string) $val);
			$right = trim($mres[3][$i], ' ');
			// if ($val instanceof DateTime && strtolower($right) != "null") {
			// 	$right = new DateTime($right);
			// 	if (!($right instanceof DateTime)) {
			// 		throw new Exception("{right} is not a valid datetime string (Y-m-d).");
			// 	}
			// }
			// r($currFactory);
			if ($currFactory != null) {
				$right = $currFactory->ConvertRightValue($right);
			}
			// r($opt);
			$func = function () use ($opt, $left, $right, $parts) {
				if (sizeof($parts) == 2) {
					if (!($left instanceof IExtendFunctionSupportable)) {
						throw new Exception('$Left does not support extension method');
					}
					if (method_exists($left, strtolower($parts[1]))) {
						$left = RuleNode::CreateNode(call_user_func(array($left, strtolower($parts[1]))));
					}
				}
				// $left  = strtolower($left);
				// $right = strtolower($right);
				// r("{$left} {$opt} {$right}");
				if (!($left instanceof INodeComparable)) {
					throw new Exception('$Left is not a supported type.');
				}
				// r($right);
				switch ($opt) {
					case "==":
						return $left->Equals($right);
					case ">":
						return $left->GreaterThan($right);
					case "<":
						return $left->LessThan($right);
					case ">=":
						return $left->EqualsOrGreaterThan($right);
					case "<=":
						return $left->EqualsOrLessThan($right);
					case "!=":
						return !$left->Equals($right);
					case "^=":
						return $left->StartsWith($right);
					case "$=":
						return $left->EndsWith($right);
					case "@=":
						return $left->Contains($right);
					default:
						throw new Exception("Not supported operation {$opt}.");
				}
			};

			$logicOpt = $mres[4][$i];
			// r(trim(substr($mres[0][$i], 0, strlen($mres[0][$i]) - strlen($logicOpt)), " "));
			array_push($funcMap, array(
				'Key'   => trim(substr($mres[0][$i], 0, strlen($mres[0][$i]) - strlen($logicOpt)), " "),
				'Alias' => "\$Func{$i}",
				'Func'  => $func,
			));
		}
		// r($funcMap);
		for ($i = 0; $i < sizeof($funcMap); $i++) {
			$statement = str_replace($funcMap[$i]['Key'], "{$funcMap[$i]['Alias']}()", $statement);
		}
		// r("statement used to create function $statement");
		$funcs = array_map(function ($val) {
			return $val['Func'];
		}, $funcMap);
		// r($funcs);
		// r($funcs[0]->__invoke());
		// r($funcs[1]->__invoke());
		// r(implode(',', array_map(function ($v) {
		// 	return $v['Alias'];
		// }, $funcMap)), 'return ' . $statement . ';');
		$stateFunc = create_function(implode(',', array_map(function ($v) {
			return $v['Alias'];
		}, $funcMap)), 'return ' . $statement . ';');
		// r($stateFunc);
		return call_user_func_array($stateFunc, $funcs);
	}
}