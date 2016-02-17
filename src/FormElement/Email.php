<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement;

use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\MinLengthMaxLengthMixin;
use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\PatternMixin;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;

class Email extends AbstractFormElement
{
    /**
     * @inheritdoc
     */
    protected function attachDefaultFilters()
    {
        $this->attachFilterByName(StripNewlines::class);
        $this->attachFilterByName(StringTrim::class);
    }

    /**
     * @inheritdoc
     */
    protected function attachDefaultValidators()
    {
        $this->attachValidatorByName('emailaddress', [
            'useMxCheck' => filter_var(
                $this->element->getAttribute('data-validator-use-mx-check'),
                FILTER_VALIDATE_BOOLEAN
            ),
        ]);

        MinLengthMaxLengthMixin::parse($this->element, $this->input);
        PatternMixin::parse($this->element, $this->input);
    }
}
