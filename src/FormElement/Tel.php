<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement;

use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\MinLengthMaxLengthMixin;
use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\PatternMixin;
use Zend\Filter\StripNewlines;

class Tel extends AbstractFormElement
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
        $this->attachValidatorByName('phonenumber', [
            'country' => $this->element->getAttribute('data-country'),
        ]);

        MinLengthMaxLengthMixin::parse($this->element, $this->input);
        PatternMixin::parse($this->element, $this->input);
    }
}
