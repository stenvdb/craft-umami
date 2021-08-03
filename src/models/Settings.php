<?php
/**
 * Umami plugin for Craft CMS 3.x
 *
 * Statistics and chart widgets for Umami
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace stenvdb\umami\models;

use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\ConfigHelper;
use stenvdb\umami\Umami;

use Craft;
use craft\base\Model;

/**
 * Umami Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Sten Van den Bergh
 * @package   Umami
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $baseUri;

    public $username;

    public $password;

    public $websiteId;

    public $injectTracking = false;

    // Public Methods
    // =========================================================================

    public function getBaseUri($siteHandle = null): string
    {
        // Cleanup the uri so we get a clean domain name
        $baseUri = rtrim(preg_replace('#^https?://#', '', Craft::parseEnv(ConfigHelper::localizedValue($this->baseUri, $siteHandle))), '/');
        return $baseUri;
    }

    public function getUsername($siteHandle = null): string
    {
        return Craft::parseEnv(ConfigHelper::localizedValue($this->username, $siteHandle));
    }

    public function getPassword($siteHandle = null): string
    {
        return Craft::parseEnv(ConfigHelper::localizedValue($this->password, $siteHandle));
    }

    public function getWebsiteId($siteHandle = null): string
    {
        return Craft::parseEnv(ConfigHelper::localizedValue($this->websiteId, $siteHandle));
    }

    public function behaviors()
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['baseUri', 'username'],
            ],
        ];
    }

    public function rules()
    {
        return [
            ['username', 'email'],
            ['baseUri', 'url']
        ];
    }
}
