<?php

namespace Xtreamwayz\HTMLFormValidator\FormElement;

use InvalidArgumentException;
use Zend\I18n\Validator\IsFloat as IsFloatValidator;
use Zend\I18n\Validator\IsInt as IsIntValidator;
use Zend\Validator\GreaterThan as GreaterThanValidator;
use Zend\Validator\LessThan as LessThanValidator;
use Zend\Validator\Step as StepValidator;

class Number extends BaseFormElement
{
    protected function getValidators()
    {
        $validators = [];

        // HTML5 always transmits values in the format "1000.01", without a
        // thousand separator. The prior use of the i18n Float validator
        // allowed the thousand separator, which resulted in wrong numbers
        // when casting to float.
        //$validators[] = new RegexValidator('(^-?\d*(\.\d+)?$)');

        $step = ($this->node->hasAttribute('step')) ? $this->node->getAttribute('step') : 1;

        if (is_numeric($step) && (int) $step == $step) {
            $validators[] = [
                'name' => IsIntValidator::class,
            ];
        } elseif (is_numeric($step) && (float) $step == $step) {
            $validators[] = [
                'name'    => IsFloatValidator::class,
                'options' => [
                    'locale' => 'en',
                ],
            ];
        } elseif ($step != 'any') {
            throw new InvalidArgumentException('Number step must be an int, float or the text "any"');
        }

        if ($this->node->hasAttribute('min')) {
            $validators[] = [
                'name'    => GreaterThanValidator::class,
                'options' => [
                    'min'       => $this->node->getAttribute('min'),
                    'inclusive' => true,
                ],
            ];
        }

        if ($this->node->hasAttribute('max')) {
            $validators[] = [
                'name'    => LessThanValidator::class,
                'options' => [
                    'max'       => $this->node->getAttribute('max'),
                    'inclusive' => true,
                ],
            ];
        }

        if (!$this->node->hasAttribute('step')
            || 'any' !== $this->node->getAttribute('step')
        ) {
            $validators[] = [
                'name'    => StepValidator::class,
                'options' => [
                    'baseValue' => $this->node->getAttribute('min') ?: 0,
                    'step'      => $step,
                ],
            ];
        }

        return $validators;
    }
}
