<?php

class ZenValidatorExtTest extends SapphireTest {

	private function Form() {
		$fields = FieldList::create(
			CheckboxField::create("IsTrue", 'Is it true?'),
			TextField::create("SN", "SN"),
			TextField::create("Name", "Name")
		);
		$actions   = FieldList::create(FormAction::create('submit', 'submit'));
		$validator = ZenValidator::create();

		return Form::create(Controller::curr(), 'Form', $fields, $actions, $validator);
	}

	public function testZenValdWithRule() {
		$form = $this->Form();
		$zv   = $form->getValidator();
		$zv->setConstraint('SN', Constraint_required::create(), 'IsTrue == True');
		$zv->setConstraint('Name', Constraint_required::create(), '!(IsTrue == True)');
		$data['IsTrue'] = false;
		$data['SN']     = '';
		$data['Name']   = '';
		$zv->php($data);
		$errors = $zv->getErrors();
		$this->assertTrue(sizeof($errors) == 1 && $errors[0]['fieldName'] == 'Name');
	}

	public function testZenValdWithRule2() {
		$form = $this->Form();
		$zv   = $form->getValidator();
		$zv->setConstraint('SN', Constraint_required::create(), 'IsTrue == True');
		$zv->setConstraint('Name', Constraint_required::create(), '!(IsTrue == True)');

		$data['IsTrue'] = true;
		$data['SN']     = '';
		$data['Name']   = '';
		$zv->php($data);
		$errors = $zv->getErrors();
		$this->assertTrue(sizeof($errors) == 1 && $errors[0]['fieldName'] == 'SN');
	}

	public function testZenValdWithRule3() {
		$form = $this->Form();
		$zv   = $form->getValidator();
		$zv->setConstraint('SN', Constraint_required::create(), 'IsTrue == True');
		$zv->setConstraint('Name', Constraint_required::create(), '!(IsTrue == True)');

		// $data['IsTrue'] = '';
		$data['SN']   = '';
		$data['Name'] = '';
		$zv->php($data);
		$errors = $zv->getErrors();
		$this->assertTrue(sizeof($errors) == 1 && $errors[0]['fieldName'] == 'Name');
	}
}