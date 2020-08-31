<?php
#parse("PHP File Header.php")

#if (${NAMESPACE})

namespace ${NAMESPACE};
#end

use Windwalker\Core\Console\CoreCommand;

#parse("PHP Class Doc Comment.php")
class ${NAME} extends CoreCommand
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected ${DS}name = '$name';

    /**
     * Property description.
     *
     * @var  string
     */
    protected ${DS}description = '$description';

    /**
     * The usage to tell user how to use this command.
     *
     * @var string
     */
    protected ${DS}usage = '%s <cmd><command></cmd> <option>[option]</option>';

    /**
     * The manual about this command.
     *
     * @var  string
     */
    protected ${DS}help;

    /**
     * Initialise command.
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
    }

    /**
     * Execute this command.
     *
     * @return int|bool
     */
    protected function doExecute()
    {
        return parent::doExecute();
    }
}
