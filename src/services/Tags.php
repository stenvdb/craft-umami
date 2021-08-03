<?php
/**
 * Umami plugin for Craft CMS 3.x
 *
 * Statistics and chart widgets for Umami
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace stenvdb\umami\services;

use craft\web\View;
use stenvdb\umami\Umami;

use Craft;
use craft\base\Component;

/**
 * Tags Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Sten Van den Bergh
 * @package   Umami
 * @since     1.0.0
 */
class Tags extends Component
{
    // Public Methods
    // =========================================================================

    public function inject()
    {
        $settings = Umami::$plugin->getSettings();

        $baseUri = $settings->getBaseUri();
        $websiteId = $settings->getWebsiteId();

        Craft::$app->getView()->registerJsFile("https://$baseUri/umami.js", ['data-website-id' => $websiteId, 'async' => true, 'defer' => true]);
    }
}
