<?php

class RuleExecutorTest extends SapphireTest {

	protected $fields = null;
	protected $re     = null;

	protected function True($statement, $message = null) {
		$this->assertTrue($this->re->Execute($statement, $this->fields), $message || $statement);
	}
	protected function False($statement, $message = null) {
		$this->assertFalse($this->re->Execute($statement, $this->fields), $message || $statement);
	}

	private function Fields() {
		return FieldList::create(
			TextField::create('Title'),
			TextField::create('Subtitle'),
			CheckboxField::create("IsTrue"),
			CheckboxSetField::create('CheckboxSet', 'Options', array(
				'Opt1' => 'Opt1',
				'Opt2' => 'Opt2',
				'Opt3' => 'Opt3',
				'Opt4' => 'Opt4',
				'Opt5' => 'Opt5',
			)),
			DropdownField::create('DDP', 'Choose an item', array(
				'ITEM1' => 'Item 1',
				'ITEM2' => 'Item 2',
				'ITEM3' => 'Item 3',
				'ITEM4' => 'Item 4',
				'ITEM5' => 'Item 5',
			))->setEmptyString('(Select one)'),
			GroupedDropdownField::create('DDP2', 'Choose an item', array(
				'Group1' => array(
					'ITEM1' => 'Item 1',
					'ITEM2' => 'Item 2',
					'ITEM3' => 'Item 3',
					'ITEM4' => 'Item 4',
					'ITEM5' => 'Item 5',
				),
				'Group2' => array(
					'ITEM6' => 'Item 6',
					'ITEM7' => 'Item 7',
					'ITEM8' => 'Item 8',
					'ITEM9' => 'Item 9',
					'ITEM0' => 'Item 0',
				),
			))->setEmptyString('(Select one)'),
			ListboxField::create('lbf', 'Choose items', array(
				'ITEM1' => 'Item 1',
				'ITEM2' => 'Item 2',
				'ITEM3' => 'Item 3',
				'ITEM4' => 'Item 4',
				'ITEM5' => 'Item 5',
			), 'ITEM3'),
			LookupField::create('lf', 'Look up field', array(
				'ITEM1' => 'Item 1',
				'ITEM2' => 'Item 2',
				'ITEM3' => 'Item 3',
				'ITEM4' => 'Item 4',
				'ITEM5' => 'Item 5',
			), 'ITEM3')->setEmptyString('(Select one)'),
			NullableField::create(TextField::create("nf", "Field 1", "abc")),
			NullableField::create(CheckBoxField::create("nf2", "Field 1", "abc")),
			NumericField::create("nf3", "NumericField", 10),
			NumericField_Readonly::create('NFR1', 'NumericField_Readonly'),
			OptionsetField::create(
				$name = "op",
				$title = "OptionsetField",
				$source = array(
					"1" => "Option 1",
					"2" => "Option 2",
					"3" => "Option 3",
					"4" => "Option 4",
					"5" => "Option 5",
				),
				$value = "3"
			),
			ReadonlyField::create('rf1', 'read only field', 'value'),
			TextareaField::create(
				$name = "description",
				$title = "Description",
				$value = "This is the default description"
			),
			DateField::create('datefield', 'Date Field')->setConfig('showcalendar', true),
			TimeField::create('timeField', 'Time Field', '12am')
		);
	}

	/*
	setup for each test case
	 */
	public function setUp() {
		$this->fields = $this->Fields();
		$this->re     = RuleExecutor::create();
	}

	///// start test different data fields
	public function testTextFieldValue() {
		$this->False("Title == Doctor", '$left should has no value, $right should be "Doctor".');
		$this->fields->dataFieldByName("Title")->setValue("Doctor");
		$this->True("Title == Doctor", 'both $left and $right should be "Doctor".');
		$this->False("Title == doctor", '$left should "Doctor", $right should be "doctor", "==" compares with case sensitive.');
	}

	public function testCheckBoxField() {
		$this->False("IsTrue == True");
		$this->fields->dataFieldByName("IsTrue")->setValue(false);
		$this->True("IsTrue == False");
		$this->fields->dataFieldByName('IsTrue')->setValue(true);
		$this->False("IsTrue == False");
	}

	public function testCheckBoxSetField() {
		$this->False('CheckboxSet == Opt3', '$left has no selected value, $right is "Opt3"');
		$this->fields->dataFieldByName('CheckboxSet')->setValue('Opt3');
		$this->True('CheckboxSet == Opt3', '$left has set to "Opt3"');
	}

	public function testCheckBoxSetFieldWithMultipleValue() {
		$this->False('CheckboxSet == Opt3', '$left has no selected value, $right is "Opt3"');
		$this->fields->dataFieldByName('CheckboxSet')->setValue(array('Opt3', 'Opt2'));
		$this->True('CheckboxSet == Opt3', '$left has set to "Opt3" and "Opt2"');
		$this->True('CheckboxSet == Opt2', '$left has set to "Opt3" and "Opt2"');
		$this->False('CheckboxSet == Opt1', '$left has set to "Opt3" and "Opt2"');
	}

	public function testDropDownField() {
		$this->False("DDP == ITEM1");
		$this->fields->dataFieldByName("DDP")->setValue("ITEM1");
		$this->True("DDP == ITEM1");
	}

	public function testGroupedDropdownField() {
		$this->False("DDP2 == ITEM1");
		$this->fields->dataFieldByName("DDP2")->setValue("ITEM1");
		$this->True("DDP2 == ITEM1");
	}

	public function testListboxField() {
		$this->False("lbf == ITEM1");
		$this->fields->dataFieldByName("lbf")->setValue("ITEM1");
		$this->True("lbf == ITEM1");
	}

	public function testLookupField() {
		$this->False("lf == ITEM1");
		$this->fields->dataFieldByName("lf")->setValue("ITEM1");
		$this->True("lf == ITEM1");
	}

	public function testNullableField() {
		$this->False("nf == abcdefg");
		$this->fields->dataFieldByName("nf")->setValue("abcdefg");
		$this->True("nf == abcdefg");
		$this->fields->dataFieldByName("nf")->setValue(12345);
		$this->True("nf > 12344");
		$this->fields->dataFieldByName("nf")->setValue(null);
		$this->True("nf == NULL");
	}

	public function testNullableFieldWithCheckBoxField() {
		$this->False("nf2 == null");
		$this->fields->dataFieldByName("nf2")->setValue(true);
		$this->True("nf2 == True");
		$this->fields->dataFieldByName("nf2")->setValue(false);
		$this->True("nf2 == False");
		$this->fields->dataFieldByName("nf2")->setValue(null);
		$this->True("nf2 == NULL");
	}

	public function testNumericField() {
		$this->False("nf3 == 1");
		$this->True("nf3 >= 5");
		$this->fields->dataFieldByName("nf3")->setValue(3);
		$this->False("nf3 >= 5");
		$this->True("nf3 <= 3");
	}

	public function testOptionsetField() {
		$this->False("op == 1");
		$this->True("op == 3");
		$this->fields->dataFieldByName("op")->setValue(2);
		$this->False("op == 3");
		$this->True("op == 2");
	}

	public function testTextareaField() {
		$this->False("description == abcdefg");
		$this->fields->dataFieldByName("description")->setValue("abcdefg");
		$this->True("description == abcdefg");
		$this->True("description.upper == ABCDEFG");
	}

	public function testDateField() {
		$this->False("datefield == 2015-08-22");
		$this->fields->dataFieldByName("datefield")->setValue("Aug 22, 2015");
		$this->True("datefield == 2015-08-22");
	}

	/// test build-in extension functions
	public function testLengthFunction() {
		$this->False("Title.length > 3");
		$this->fields->dataFieldByName("Title")->setValue("Doctor");
		$this->True("Title.length > 3");
	}

	public function testLowerFunction() {
		$this->False("Title.lower == doctor", '$left has no value, $right is "doctor".');
		$this->fields->dataFieldByName("Title")->setValue("Doctor");
		$this->True("Title.lower == doctor", '$left should be lower case of "Doctor", $right is "doctor".');
	}

	public function testUpperFunction() {
		$this->False("Title.upper == DOCTOR", '$left has no value, $right is "DOCTOR".');
		$this->fields->dataFieldByName("Title")->setValue("Doctor");
		$this->True("Title.upper == DOCTOR", '$left should be upper case of "Doctor", $right is "DOCTOR".');
	}

	///// complex rule test cases
	public function testCheckBoxSetFieldWithComplexConditions() {
		$this->fields->dataFieldByName('CheckboxSet')->setValue(array('Opt3', 'Opt2'));
		$this->True('CheckboxSet == Opt3 || CheckboxSet == Opt1', 'CheckboxSet == Opt3 || CheckboxSet == Opt1');
		$this->True('CheckboxSet == Opt3 && CheckboxSet == Opt2', 'CheckboxSet == Opt3 && CheckboxSet == Opt2');
		$this->False('(CheckboxSet == Opt3 || CheckboxSet == Opt2) && CheckboxSet == Opt1', '(CheckboxSet == Opt3 || CheckboxSet == Opt2) && CheckboxSet == Opt1');
	}

	public function testComplexRule1() {
		$this->fields->dataFieldByName('CheckboxSet')->setValue(array('Opt3', 'Opt2'));
		$this->fields->dataFieldByName('IsTrue')->setValue(true);

		$this->True('IsTrue == False && Title.length == 0 || CheckboxSet == Opt2');
		$this->True('IsTrue == True && Title.length >= 0 || CheckboxSet == Opt2');
		$this->True('IsTrue == True && Title.length >= 0 || CheckboxSet == Opt1');
		$this->False('IsTrue == True && Title.length > 0 || CheckboxSet == Opt1');
		// r($this->fields->dataFieldByName('IsTrue')->Value());
		// r($this->re->Execute('IsTrue == False', $this->fields));
		$this->False('IsTrue == False && Title.length >= 0 || CheckboxSet == Opt1');
	}
}