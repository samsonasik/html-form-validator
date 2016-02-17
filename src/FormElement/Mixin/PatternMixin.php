<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement\Mixin;

use DOMElement;
use Zend\InputFilter\InputInterface;
use Zend\Validator\Regex;

class PatternMixin
{
    public static function parse(DOMElement $element, InputInterface $input)
    {
        if ($element->hasAttribute('pattern')) {
            $input->getValidatorChain()->attach(new Regex([
                'pattern' => sprintf('/%s/', $element->getAttribute('pattern')),
            ]));
        }
    }
}
