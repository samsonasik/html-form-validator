# html-form-validator

[![Build Status](https://travis-ci.org/xtreamwayz/html-form-validator.svg?branch=master)](https://travis-ci.org/xtreamwayz/html-form-validator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/xtreamwayz/html-form-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/xtreamwayz/html-form-validator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/xtreamwayz/html-form-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/xtreamwayz/html-form-validator/?branch=master)

**NOTE: This is a proof of concept. Use at your own risk!**

---

As challenged by a [tweet](https://twitter.com/Ocramius/status/680817040429592576), this should extract validation
rules and filters from a html form and validate submitted user data against it.

It's pretty crazy what you have to do to get a form build. Create a lot of php classes for elements, validation,
etc. So why not build a html form and use the standard element attributes to extract the validation rules and filters.
Together with some powerful html compliant data attributes you can create forms, validation and filters in one place.

Currently There are a few pr's open before this works with zend-service manager:
- [zend-validator #51](https://github.com/zendframework/zend-validator/pull/51)
  [source](https://github.com/weierophinney/zend-validator/tree/feature/50)
- [zend-inputfilter #86](https://github.com/zendframework/zend-inputfilter/pull/86)
  [source](https://github.com/gianarb/zend-inputfilter/tree/feature/zf3-improvement)
- [zend-inputfilter #95](https://github.com/zendframework/zend-inputfilter/pull/95)
  [source](https://github.com/kynx/zend-inputfilter/tree/sm-v2-v3-compat)

## Installation

```bash
$ composer require xtreamwayz/html-form-validator
```

## How does it work?

1. **Load the html form into the FormFactory**

    ```php
    $form = FormFactory::fromHtml($htmlForm, $defaultValues);
    ```

    - The FormFactory automatically creates default validators and filters for all input elements.
    - The FormFactory extracts additional custom validation rules and filters from the form.
    - The FormFactory optionally injects default data into the form input elements.

2. **Validate the form against submitted data**

    ```php
    $result = $form->validate($_POST);
    ```

    Under the hood it uses [zend-inputfilter](https://github.com/zendframework/zend-inputfilter) which makes all its
    [validators](http://framework.zend.com/manual/current/en/modules/zend.validator.set.html) and
    [filters](http://framework.zend.com/manual/current/en/modules/zend.filter.set.html) available to you.

3. **Render the form**

    ```php
    echo $form->asString($validationResult);
    ```

    Before rendering, the FormFactory removes any data validation attributes used to instantiate custom validation
    (e.g. `data-validators`, `data-filters`). This also removes possible sensitive data that was used to setup
    the validators.

    The `$validationResult` is optional and triggers the following tasks:
    - The FormFactory injects filtered submitted data into the input elements.
    - The FormFactory adds error messages next to the input elements.
    - The FormFactory sets the `aria-invalid="true"` attribute for invalid input elements.
    - The FormFactory adds the bootstrap `has-danger` css class to the parent element.

## Documentation

Documentation is available in the [wiki](https://github.com/xtreamwayz/html-form-validator/wiki).
Pull requests for documentation can be made against the source files in [docs/wiki](docs/wiki).

## Examples

Examples can be found in the [wiki](https://github.com/xtreamwayz/html-form-validator/wiki) and
[test/Fixtures](https://github.com/xtreamwayz/html-form-validator/tree/master/test/Fixtures) dir.

```php
// Basic contact form

$htmlForm = <<<'HTML'
<form action="{{ path() }}" method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-control-label" for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Your name" required
                       data-reuse-submitted-value="true" data-filters="striptags|stringtrim"
                       class="form-control" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-control-label" for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="Your email address" required
                       data-reuse-submitted-value="true" data-filters="striptags|stringtrim"
                       class="form-control" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-control-label" for="subject">Subject</label>
        <input type="text" id="subject" name="subject" placeholder="Subject" required
               data-reuse-submitted-value="true" data-filters="striptags|stringtrim"
               class="form-control" />
    </div>

    <div class="form-group">
        <label class="form-control-label" for="body">Message</label>
        <textarea id="body" name="body" rows="5" required
                  data-reuse-submitted-value="true" data-filters="stringtrim"
                  class="form-control" placeholder="Message"></textarea>
    </div>

    <input type="hidden" name="token" value="{{ csrf-token }}"
           data-validators="identical{token:{{ csrf-token }}}" required />

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
HTML;

// Create form validator from a twig rendered form template
$form = FormFactory::fromHtml($template->render($htmlForm, [
    'csrf-token' => '123456'
]));

$_POST['name'] = 'John Doe';
$_POST['email'] = 'john.doe@example.com';
$_POST['subject'] = 'Subject of message';
$_POST['body'] = 'ow are you doing.';

// Validate form and return form validation result object
$result = $form->validate($_POST);

// Inject error messages and filtered values from the result object
echo $form->asString($result);
```
