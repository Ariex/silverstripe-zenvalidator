<?php

class DateTimeRuleNode extends StringRuleNode {
	public function getNodeType() {
		return "DateTime";
	}

	public function Equals($right) {
		if ($this->RawValue == null && $right == null) {
			return true;
		}
		if ($this->RawValue == null || $right == null) {
			return false;
		}
		$v1 = $this->RawValue->getTimestamp();
		$v2 = $right->getTimestamp();
		return $v1 == $v2;
	}

	public function GreaterThan($right) {
		if ($this->RawValue == null || $right == null) {
			throw new Exception("GreaterThan does not support null compare");
		}
		$v1 = $this->RawValue->getTimestamp();
		$v2 = $right->getTimestamp();
		return $v1 > $v2;
	}

	public function EqualsOrGreaterThan($right) {
		if ($this->RawValue == null || $right == null) {
			throw new Exception("EqualsOrGreaterThan does not support null compare");
		}
		$v1 = $this->RawValue->getTimestamp();
		$v2 = $right->getTimestamp();
		return $v1 >= $v2;
	}

	public function LessThan($right) {
		if ($this->RawValue == null || $right == null) {
			throw new Exception("LessThan does not support null compare");
		}
		$v1 = $this->RawValue->getTimestamp();
		$v2 = $right->getTimestamp();
		return $v1 < $v2;
	}

	public function EqualsOrLessThan($right) {
		if ($this->RawValue == null || $right == null) {
			throw new Exception("EqualsOrLessThan does not support null compare");
		}
		$v1 = $this->RawValue->getTimestamp();
		$v2 = $right->getTimestamp();
		return $v1 <= $v2;
	}
}

class DateTimeNodeFactory extends Object implements IRuleNodeFactory {
	public function CreateNode($rawValue) {
		if ($rawValue instanceof DateTime) {
			return DateTimeRuleNode::create($rawValue);
		}
		return null;
	}

	public function ConvertRightValue($right) {
		$res = null;
		if ($right != null && strtolower($right) != "null") {
			$res = new DateTime($right);
			if (!($res instanceof DateTime)) {
				throw new Exception("{right} is not a valid datetime string (Y-m-d or H:i:s).");
			}
		}
		return $res;
	}
}