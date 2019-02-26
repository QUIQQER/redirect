<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;


/**
 * Class with various helper-methods for the redirect module
 *
 * All this session stuff is needed in order to skip adding redirects for children.
 * This way it's not necessary to overwrite system's site delete methods
 *
 * @package redirect\src\QUI\Redirect
 */
class Database
{
    /**
     * @var string - The name of the id column (see database.xml)
     */
    const COLUMN_ID = "id";

    /**
     * @var string - The name of the source url column (see database.xml)
     */
    const COLUMN_SOURCE_URL = "source_url";

    /**
     * @var string - The name of the target url column (see database.xml)
     */
    const COLUMN_TARGET_URL = "target_url";

    /**
     * Returns the name of the redirect table
     *
     * @return string
     */
    public static function getTableName()
    {
        return \QUI::getDBTableName('redirects');
    }
}