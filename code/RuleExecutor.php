<?php

class RuleExecutor extends Object {
	public function Execute($statement, $fields) {
		r("orig statement {$statement}");
		if (strlen($statement) < 1) {
			return true;
		}
		$regex     = '/(?:\s*([^ =>!<$~^()]+)\s*((?:==|!=|\>=|\<=|\>|\<))\s*([^ =()]+)\s*(&&|\|\|){0,1}){1,}?/';
		$knownOpts = array('==');
		preg_match_all($regex, $statement, $matches);
		$mres            = $matches ? $matches : array();
		$lenMatchedRules = sizeof($mres[0]);
		$lefts           = $mres[1];
		r($mres);
		$funcMap = array();
		for ($i = 0; $i < sizeof($lefts); $i++) {
			$parts    = preg_split('/\./', $lefts[$i]);
			$realLeft = $parts[0];
			r("Field {$realLeft} exist");
			$f = $fields->dataFieldByName($realLeft);
			if ($f == null) {
				throw new Exception("Field {$realLeft} does not exist");
			}
			$opt   = $mres[2][$i];
			$left  = strtolower((string) $f->Value());
			$right = strtolower($mres[3][$i]);
			r($opt);
			$func = function () use ($opt, $left, $right, $parts) {
				if (sizeof($parts) == 2) {
					switch (strtolower($parts[1])) {
						case "length":
							$left = strlen($left);
							break;
						default:
							$left = $left;
					}
				}
				$left  = strtolower($left);
				$right = strtolower($right);
				r("{$left} {$opt} {$right}");
				switch ($opt) {
					case "==":
						return $left == $right;
					case ">":
						return floatval($left) > floatval($right);
					case "<":
						return floatval($left) < floatval($right);
					case ">=":
						return floatval($left) >= floatval($right);
					case "<=":
						return floatval($left) <= floatval($right);
					case "!=":
						return $left != $right;
					case "^=":
						return substr($left, 0, strlen($right)) == $right;
					case "$=":
						return substr($left, -strlen($right)) == $right;
					default:
						throw new Exception("Not supported operation {$opt}.");
				}
			};

			$logicOpt = $mres[4][$i];
			array_push($funcMap, array(
				'Key'   => trim(substr($mres[0][$i], 0, strlen($mres[0][$i]) - strlen($logicOpt)), " "),
				'Alias' => "\$Func{$i}",
				'Func'  => $func,
			));
		}
		r($funcMap);
		for ($i = 0; $i < sizeof($funcMap); $i++) {
			$statement = str_replace($funcMap[$i]['Key'], "{$funcMap[$i]['Alias']}()", $statement);
		}
		r("statement used to create function $statement");
		$funcs = array_map(function ($val) {
			return $val['Func'];
		}, $funcMap);
		r($funcs);
		r(implode(',', array_map(function ($v) {
			return $v['Alias'];
		}, $funcMap)));
		$stateFunc = create_function(implode(',', array_map(function ($v) {
			return $v['Alias'];
		}, $funcMap)), 'return ' . $statement . ';');
		r($stateFunc);
		return call_user_func_array($stateFunc, $funcs);
	}
}