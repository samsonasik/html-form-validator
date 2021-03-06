<?php

namespace Xtreamwayz\HTMLFormValidator;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;

final class FormFactory implements FormFactoryInterface
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var array
     */
    private $defaultValues;

    private $errorClass = 'has-danger';

    /**
     * @var FormElement\BaseFormElement[]
     */
    private $formElements = [
        'hidden'         => FormElement\Hidden::class,
        'text'           => FormElement\Text::class,
        'search'         => FormElement\Text::class,
        'tel'            => FormElement\Tel::class,
        'url'            => FormElement\Url::class,
        'email'          => FormElement\Email::class,
        'password'       => FormElement\Password::class,
        'date'           => FormElement\Date::class,
        'month'          => FormElement\Month::class,
        'week'           => FormElement\Week::class,
        'time'           => FormElement\Time::class,
        'datetime-local' => FormElement\DateTime::class,
        'number'         => FormElement\Number::class,
        'range'          => FormElement\Range::class,
        'color'          => FormElement\Color::class,
        'checkbox'       => FormElement\Checkbox::class,
        'radio'          => FormElement\Radio::class,
        'file'           => FormElement\File::class,
        'select'         => FormElement\Select::class,
        'textarea'       => FormElement\Textarea::class,
    ];

    /**
     * @inheritdoc
     */
    public function __construct($htmlForm, array $defaultValues = [], Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->defaultValues = $defaultValues;

        // Create new doc
        $this->document = new DOMDocument('1.0', 'utf-8');

        // Ignore invalid tag errors during loading (e.g. datalist)
        libxml_use_internal_errors(true);
        // Don't add missing doctype, html and body
        $this->document->loadHTML($htmlForm, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false);

        // Inject default values (from models etc)
        $this->setData($defaultValues, true);
    }

    /**
     * @inheritdoc
     */
    public static function fromHtml($htmlForm, array $defaultValues = [])
    {
        return new self($htmlForm, $defaultValues);
    }

    /**
     * @inheritdoc
     */
    public function asString(ValidationResultInterface $result = null)
    {
        if ($result) {
            // Inject data if a result is set
            $this->setData($result->getValues());
            $this->setMessages($result->getMessages());
        }

        // Always remove form validator specific attributes before rendering the form
        // to clean it up and remove possible sensitive data
        foreach ($this->getNodeList() as $name => $node) {
            $node->removeAttribute('data-reuse-submitted-value');
            $node->removeAttribute('data-input-name');
            $node->removeAttribute('data-validators');
            $node->removeAttribute('data-filters');
        }

        $this->document->formatOutput = true;

        return $this->document->saveHTML();
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $this->inputFilter = $this->factory->createInputFilter([]);

        // Add all validators and filters to the InputFilter
        $this->buildInputFilterFromForm();

        $this->inputFilter->setData($data);
        $messages = [];

        // Do some validation
        if (!$this->inputFilter->isValid()) {
            foreach ($this->inputFilter->getInvalidInput() as $message) {
                $messages[$message->getName()] = $message->getMessages();
            }
        }

        // Return validation result
        return new ValidationResult(
            $this->inputFilter->getRawValues(),
            $this->inputFilter->getValues(),
            $messages
        );
    }

    /**
     * Build the InputFilter, validators and filters from form fields
     */
    private function buildInputFilterFromForm()
    {
        foreach ($this->getNodeList() as $name => $node) {
            if ($this->inputFilter->has($name)) {
                continue;
            }

            // Detect element type
            $type = $node->getAttribute('type');
            if ($node->tagName == 'textarea') {
                $type = 'textarea';
            } elseif ($node->tagName == 'select') {
                $type = 'select';
            }

            // Add validation
            if (isset($this->formElements[$type])) {
                $elementClass = $this->formElements[$type];
            } else {
                // Create a default validator
                $elementClass = $this->formElements['text'];
            }

            /** @var \Zend\InputFilter\InputProviderInterface $element */
            $element = new $elementClass($node, $this->document);
            $input = $this->factory->createInput($element);
            $this->inputFilter->add($input, $name);
        }
    }

    /**
     * Get form elements and create an id if needed
     */
    private function getNodeList()
    {
        $xpath = new DOMXPath($this->document);
        $nodeList = $xpath->query('//input | //textarea | //select | //div[@data-input-name]');

        /** @var DOMElement $node */
        foreach ($nodeList as $node) {
            // Set some basic vars
            $name = $node->getAttribute('name');
            if (!$name) {
                $name = $node->getAttribute('data-input-name');
            }

            if (!$name) {
                // At least a name is needed to submit a value.
                // Silently continue, might be a submit button.
                continue;
            }

            yield $name => $node;
        }
    }

    /**
     * Set values and element checked and selected states
     *
     * @param array $data
     * @param bool  $force
     */
    private function setData(array $data, $force = false)
    {
        foreach ($this->getNodeList() as $name => $node) {
            if (!isset($data[$name])) {
                // No value set for this element
                continue;
            }

            $value = $data[$name];

            $reuseSubmittedValue = filter_var(
                $node->getAttribute('data-reuse-submitted-value'),
                FILTER_VALIDATE_BOOLEAN
            );

            if (!$reuseSubmittedValue && $force === false) {
                // Don't need to set the value
                continue;
            }

            if ($node->getAttribute('type') == 'checkbox' || $node->getAttribute('type') == 'radio') {
                if ($value == $node->getAttribute('value')) {
                    $node->setAttribute('checked', 'checked');
                } else {
                    $node->removeAttribute('checked');
                }
            } elseif ($node->nodeName == 'select') {
                /** @var DOMElement $option */
                foreach ($node->getElementsByTagName('option') as $option) {
                    if ($value == $option->getAttribute('value')) {
                        $option->setAttribute('selected', 'selected');
                    } else {
                        $option->removeAttribute('selected');
                    }
                }
            } elseif ($node->nodeName == 'input') {
                // Set value for input elements
                $node->setAttribute('value', $value);
            } elseif ($node->nodeName == 'textarea') {
                $node->nodeValue = $value;
            }
        }
    }

    /**
     * Set validation messages, bootstrap style
     *
     * @param array $data
     */
    private function setMessages(array $data)
    {
        foreach ($data as $name => $errors) {
            // Not sure if this can be optimized and create the DOMXPath only once.
            // At this point the dom is constantly changing.
            $xpath = new DOMXPath($this->document);
            // Get all elements with the name
            $nodeList = $xpath->query(sprintf('//*[@name="%1$s"] | //*[@data-input-name="%1$s"]', $name));

            if ($nodeList->length == 0) {
                // No element found for this element ???
                continue;
            }

            // Get first element only
            $node = $nodeList->item(0);

            /** @var DOMElement $parent */
            $parent = $node->parentNode;
            if (strpos($parent->getAttribute('class'), $this->errorClass) === false) {
                // Set error class to parent
                $class = trim($parent->getAttribute('class') . ' ' . $this->errorClass);
                $parent->setAttribute('class', $class);
            }

            // Inject error messages
            foreach ($errors as $code => $message) {
                $div = $this->document->createElement('div');
                $div->setAttribute('class', 'text-danger');
                $div->nodeValue = $message;
                $node->parentNode->insertBefore($div, $node->nextSibling);
            }

            /** @var DOMElement $node */
            foreach ($nodeList as $node) {
                // Set aria-invalid attribute on all elements
                $node->setAttribute('aria-invalid', 'true');
            }
        }
    }
}
