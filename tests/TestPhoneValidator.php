<?php

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;
use PhoneValidatorStub as Validator;

class PhoneValidatorTest extends PHPUnit_Framework_TestCase {

	private $translator;
	private $validator;

	public function setUp()
	{
		$this->translator = new Translator('en', new MessageSelector());
		$this->translator->addLoader('array', new ArrayLoader);
		$this->validator = new Validator($this->translator, array(), array());
	}

	public function testValidatePhoneWithDefaultCountryNoType()
	{
		// Validator with correct country value.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:BE');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertTrue($this->validator->passes());

		// Validator with wrong country value.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:NL');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with multiple country values, one correct.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:BE,NL');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertTrue($this->validator->passes());

		// Validator with multiple wrong country values.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:DE,NL');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());
	}

	public function testValidatePhoneWithCountryFieldNoType()
	{
		// Validator with correct country field supplied.
		$data = array('field' => '016123456', 'field_country' => 'BE');
		$rules = array('field' => 'phone');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertTrue($this->validator->passes());

		// Validator with wrong country field supplied.
		$data = array('field' => '016123456', 'field_country' => 'NL');
		$rules = array('field' => 'phone');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());
	}

	public function testValidatePhoneWithDefaultCountryWithType()
	{
		// Validator with correct country value, correct type.
		$data = array('field' => '0499123456');
		$rules = array('field' => 'phone:BE,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertTrue($this->validator->passes());

		// Validator with correct country value, wrong type.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:BE,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with wrong country value, correct type.
		$data = array('field' => '0499123456');
		$rules = array('field' => 'phone:NL,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with wrong country value, wrong type.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:NL,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with multiple country values, one correct, correct type.
		$data = array('field' => '0499123456');
		$rules = array('field' => 'phone:BE,NL,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertTrue($this->validator->passes());

		// Validator with multiple country values, one correct, wrong type.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:BE,NL,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with multiple country values, none correct, correct type.
		$data = array('field' => '0499123456');
		$rules = array('field' => 'phone:DE,NL,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with multiple country values, none correct, wrong type.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:DE,NL,mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());
	}

	public function testValidatePhoneWithCountryFieldWithType()
	{
		// Validator with correct country field supplied, correct type.
		$data = array('field' => '0499123456', 'field_country' => 'BE');
		$rules = array('field' => 'phone:mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertTrue($this->validator->passes());

		// Validator with correct country field supplied, correct type.
		$data = array('field' => '016123456', 'field_country' => 'BE');
		$rules = array('field' => 'phone:mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with wrong country field supplied, correct type.
		$data = array('field' => '0499123456', 'field_country' => 'NL');
		$rules = array('field' => 'phone:mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with wrong country field supplied, wrong type.
		$data = array('field' => '016123456', 'field_country' => 'NL');
		$rules = array('field' => 'phone:mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());
	}

	public function testValidatePhoneNoDefaultCountryNoCountryField()
	{
		// Validator with no country field or given country.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with no country field or given country, wrong type.
		$data = array('field' => '016123456');
		$rules = array('field' => 'phone:mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());

		// Validator with no country field or given country, correct type.
		$data = array('field' => '0499123456');
		$rules = array('field' => 'phone:mobile');
		$this->validator->setData($data);
		$this->validator->setRules($rules);
		$this->assertFalse($this->validator->passes());
	}

	public function testPhoneFormatHelperFunction()
	{
		// Test landline number without format parameter.
		$phone = '016123456';
		$country = 'BE';
		$actual = phone_format($phone, $country);
		$expected = '+32 16 12 34 56';
		$this->assertEquals($expected, $actual);

		// Test landline number with format parameter.
		$phone = '016123456';
		$country = 'BE';
		$format = \libphonenumber\PhoneNumberFormat::NATIONAL;
		$actual = phone_format($phone, $country, $format);
		$expected = '016 12 34 56';
		$this->assertEquals($expected, $actual);

		// Test mobile number without format parameter.
		$phone = '0499123456';
		$country = 'BE';
		$actual = phone_format($phone, $country);
		$expected = '+32 499 12 34 56';
		$this->assertEquals($expected, $actual);

		// Test mobile number with format parameter.
		$phone = '0499123456';
		$country = 'BE';
		$format = \libphonenumber\PhoneNumberFormat::NATIONAL;
		$actual = phone_format($phone, $country, $format);
		$expected = '0499 12 34 56';
		$this->assertEquals($expected, $actual);
	}

}