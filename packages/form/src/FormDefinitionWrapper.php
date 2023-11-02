<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Form\Attributes\FormDefine;
use Windwalker\Utilities\Iterator\PriorityQueue;

/**
 * The FormDefinitionWrapper class.
 */
class FormDefinitionWrapper implements FieldDefinitionInterface
{
    public function __construct(protected object $definition)
    {
    }

    /**
     * @inheritDoc
     */
    public function define(Form $form): void
    {
        if ($this->definition instanceof FieldDefinitionInterface) {
            $this->definition->define($form);
            return;
        }

        $ref = new \ReflectionObject($this->definition);
        $methods = $ref->getMethods();

        $defines = new PriorityQueue();
        $i = count($methods);

        foreach ($methods as $method) {
            $attr = AttributesAccessor::getFirstAttributeInstance($method, FormDefine::class);

            if ($attr) {
                $defines->insert(
                    [$method->getName(), $attr],
                    $attr->ordering ?? $i
                );

                $methodName = $method->getName();

                $this->definition->$methodName($form);
            }

            $i--;
        }

        $register = function (Form $form, string $methodName) {
            $this->definition->$methodName($form);
        };

        /** @var FormDefine $attr */
        foreach ($defines as [$methodName, $attr]) {
            if ($attr->fieldset && $attr->ns) {
                $form->wrap($attr->fieldset, $attr->ns, $register);
                continue;
            }

            if ($attr->fieldset) {
                $form->fieldset($attr->fieldset, $register);
                continue;
            }

            if ($attr->ns) {
                $form->ns($attr->ns, $register);
            }
        }
    }
}
