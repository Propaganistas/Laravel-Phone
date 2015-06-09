<?php namespace Propaganistas\LaravelPhone;

use Propaganistas\LaravelPhone\PhoneValidatorTrait;

class PhoneValidator {

	// The validator class used in unit tests needs to be extended from
    // Illuminate\Validation\Validator while the class for production doesn't.
	// So extract the validation methods into a trait and let's create a stub class for each case.
	use PhoneValidatorTrait;

}
