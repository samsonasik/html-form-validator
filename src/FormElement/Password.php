<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement;

use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\MinLengthMaxLengthMixin;
use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\PatternMixin;
use Zend\Filter\StripNewlines;

class Password extends AbstractFormElement
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
        MinLengthMaxLengthMixin::parse($this->element, $this->input);
        PatternMixin::parse($this->element, $this->input);
    }
}
