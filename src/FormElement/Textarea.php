<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement;

use Xtreamwayz\HTMLFormValidator\FormElement\Mixin\MinLengthMaxLengthMixin;

class Textarea extends AbstractFormElement
{
    /**
     * @inheritdoc
     */
    protected function attachDefaultFilters()
    {
    }

    /**
     * @inheritdoc
     */
    protected function attachDefaultValidators()
    {
        MinLengthMaxLengthMixin::parse($this->element, $this->input);
    }
}
