<?php

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Illuminate\Validation\Validator;
use Propaganistas\LaravelPhone\PhoneValidator;

class PhoneValidatorTest extends PHPUnit_Framework_TestCase {

	private $translator;
	private $validator;

	public function setUp()
	{
		$this->translator = new Translator('en', new MessageSelector());
		$this->translator->addLoader('array', new ArrayLoader);
		$this->validator = new Validator($this->translator, array(), array());
	}

	private function createPhoneValidator()
	{
		return new PhoneValidator();
	}

	private function performValidation($data)
	{
		$validatorData = array('field' => $data['value']);
		$validatorData['field_country'] = isset($data['country']) ? $data['country'] : null;
		$this->validator->setData($validatorData);

		$parameters = isset($data['params']) ? explode(',',$data['params']) : array();

		return $this->createPhoneValidator()
		            ->validatePhone('field', $data['value'], $parameters, $this->validator);
	}

	public function testValidatePhoneWithDefaultCountryNoType()
	{
		// Validator with correct country value.
		$this->assertTrue($this->performValidation(['value' => '016123456', 'params' => 'BE']));

		// Validator with wrong country value.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'NL']));

		// Validator with multiple country values, one correct.
		$this->assertTrue($this->performValidation(['value' => '016123456', 'params' => 'BE,NL']));

		// Validator with multiple wrong country values.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'DE,NL']));
	}

	public function testValidatePhoneWithCountryFieldNoType()
	{
		// Validator with correct country field supplied.
		$this->assertTrue($this->performValidation(['value' => '016123456', 'country' => 'BE']));

		// Validator with wrong country field supplied.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'country' => 'NL']));
	}

	public function testValidatePhoneWithDefaultCountryWithType()
	{
		// Validator with correct country value, correct type.
		$this->assertTrue($this->performValidation(['value' => '0499123456', 'params' => 'BE,mobile']));

		// Validator with correct country value, wrong type.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'BE,mobile']));

		// Validator with wrong country value, correct type.
		$this->assertFalse($this->performValidation(['value' => '0499123456', 'params' => 'NL,mobile']));

		// Validator with wrong country value, wrong type.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'NL,mobile']));

		// Validator with multiple country values, one correct, correct type.
		$this->assertTrue($this->performValidation(['value' => '0499123456', 'params' => 'BE,NL,mobile']));

		// Validator with multiple country values, one correct, wrong type.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'BE,NL,mobile']));

		// Validator with multiple country values, none correct, correct type.
		$this->assertFalse($this->performValidation(['value' => '0499123456', 'params' => 'DE,NL,mobile']));

		// Validator with multiple country values, none correct, wrong type.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'DE,NL,mobile']));
	}

	public function testValidatePhoneWithCountryFieldWithType()
	{
		// Validator with correct country field supplied, correct type.
		$this->assertTrue($this->performValidation(['value' => '0499123456', 'params' => 'mobile', 'country' => 'BE']));

		// Validator with correct country field supplied, wrong type.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'mobile', 'country' => 'BE']));

		// Validator with wrong country field supplied, correct type.
		$this->assertFalse($this->performValidation(['value' => '0499123456', 'params' => 'mobile', 'country' => 'NL']));

		// Validator with wrong country field supplied, wrong type.
		$this->assertFalse($this->performValidation(['value' => '016123456', 'params' => 'mobile', 'country' => 'NL']));
	}


	public function testValidatePhoneAutomaticDetectionFromInternationalInput()
	{
		// Validator with correct international input.
		$this->assertTrue($this->performValidation(['value' => '+3216123456', 'params' => 'AUTO']));

		// Validator with wrong international input.
		$this->assertFalse($this->performValidation(['value' => '003216123456', 'params' => 'AUTO']));

		// Validator with wrong international input.
		$this->assertFalse($this->performValidation(['value' => '+321456', 'params' => 'AUTO']));
	}

	public function testValidatePhoneNoDefaultCountryNoCountryField()
	{
		$this->setExpectedException('Propaganistas\LaravelPhone\Exceptions\NoValidCountryFoundException');

		// Validator with no country field or given country.
		$this->performValidation(['value' => '016123456']);

		// Validator with no country field or given country, wrong type.
		$this->performValidation(['value' => '016123456', 'params' => 'mobile']);

		// Validator with no country field or given country, correct type.
		$this->performValidation(['value' => '0499123456', 'params' => 'mobile']);

		// Validator with no country field or given country, correct type, faulty parameter.
		$this->performValidation(['value' => '0499123456', 'params' => 'mobile,xyz']);
	}

	public function testValidatePhoneFaultyParameters()
	{
		$this->setExpectedException('Propaganistas\LaravelPhone\Exceptions\InvalidParameterException');

		// Validator with given country, correct type, faulty parameter.
		$this->performValidation(['value' => '016123456', 'params' => 'BE,mobile,xyz']);

		// Validator with country field, correct type, faulty parameter.
		$this->performValidation(['value' => '016123456', 'params' => 'mobile,xyz', 'country' => 'BE']);
	}

	public function testPhoneFormatHelperFunction()
	{
		// Test landline number without format parameter.
		$actual = phone_format('016123456', 'BE');
		$expected = '+32 16 12 34 56';
		$this->assertEquals($expected, $actual);

		// Test landline number with format parameter.
		$actual = phone_format('016123456', 'BE', \libphonenumber\PhoneNumberFormat::NATIONAL);
		$expected = '016 12 34 56';
		$this->assertEquals($expected, $actual);

		// Test mobile number without format parameter.
		$actual = phone_format('0499123456', 'BE');
		$expected = '+32 499 12 34 56';
		$this->assertEquals($expected, $actual);

		// Test mobile number with format parameter.
		$actual = phone_format('0499123456', 'BE', \libphonenumber\PhoneNumberFormat::NATIONAL);
		$expected = '0499 12 34 56';
		$this->assertEquals($expected, $actual);
	}

}