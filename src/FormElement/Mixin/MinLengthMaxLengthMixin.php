<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement\Mixin;

use DOMElement;
use Zend\InputFilter\InputInterface;
use Zend\Validator\StringLength;

class MinLengthMaxLengthMixin
{
    public static function parse(DOMElement $element, InputInterface $input)
    {
        if ($element->hasAttribute('minlength') || $element->hasAttribute('maxlength')) {
            $input->getValidatorChain()->attach(new StringLength([
                'min' => $element->getAttribute('minlength') ?: 0,
                'max' => $element->getAttribute('maxlength') ?: null,
            ]));
        }
    }
}
