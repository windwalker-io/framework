<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form;

/**
 * Field Definition Interface
 *
 * @since  2.0
 */
interface FieldDefinitionInterface
{
    /**
     * Define the form fields.
     *
     * @param Form $form The Windwalker form object.
     *
     * @return  void
     */
    public function define(Form $form);
}
