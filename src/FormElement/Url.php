<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement;

use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\MinLengthMaxLengthMixin;
use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\MinLengthMaxLengthTrait;
use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\PatternMixin;
use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\PatternTrait;
use Zend\Filter\StripNewlines;
use Zend\Validator\Regex;

class Url extends AbstractFormElement
{
    /**
     * @inheritdoc
     */
    protected function attachDefaultFilters()
    {
        $this->attachFilterByName(StripNewlines::class);
    }

    /**
     * @inheritdoc
     */
    protected function attachDefaultValidators()
    {
        $this->attachValidatorByName('uri');

        MinLengthMaxLengthMixin::parse($this->element, $this->input);
        PatternMixin::parse($this->element, $this->input);
    }
}
