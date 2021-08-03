<?php
/**
 * Umami plugin for Craft CMS 3.x
 *
 * Statistics and chart widgets for Umami
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace stenvdb\umami\variables;

use stenvdb\umami\Umami;

use Craft;

/**
 * Umami Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.umami }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Sten Van den Bergh
 * @package   Umami
 * @since     1.0.0
 */
class UmamiVariable
{
    // Public Methods
    // =========================================================================

    public function inject()
    {
        return Umami::$plugin->tags->inject();
    }
}
