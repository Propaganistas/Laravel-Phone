# Feature requests

This package is considered 95% feature-complete.

If your envisioned feature would just be forwarding results from `libphonenumber` without any added value, please resort to creating a macro.
E.g. determining the carrier from a phone number, ...

```php
PhoneNumber::macro('your_special_method', function() {
    // ...
});
```

Otherwise, feel free to open a Pull Request with a detailed explanation of why the suggested feature is worth adding to the package.