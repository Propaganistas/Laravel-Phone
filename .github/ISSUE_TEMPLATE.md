Prior to filing a bug, please check the origin of the unexpected behavior you're facing. Any issues that do not follow this guide will be closed without notice.

1. First try to reproduce the bug in the demo application (https://laravel-phone.herokuapp.com). If all works well, go to step 3.
	
2. Still seeing unexpected behavior? Congratulations, you've possibly found a bug! Please go ahead and create an issue. Describe clearly what you're trying to achieve, along with some erroneous phone numbers and full validation parameters.

3. Try to reproduce the bug in libphonenumber's demo (https://giggsey.com/libphonenumber) and take note of the `isValidNumber()` and `isValidNumberForRegion()` results. If all works well, go to step 5.

4. Still seeing unexpected behavior? Please open an issue at libphonenumber (https://github.com/giggsey/libphonenumber-for-php).

5. This isn't a bug in the package nor in libphonenumber. Fix your own code.