<?php
/**
 * Umami plugin for Craft CMS 3.x
 *
 * Statistics and chart widgets for Umami
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace stenvdb\umami;

use craft\helpers\UrlHelper;
use craft\web\View;
use stenvdb\umami\services\Reports as ReportsService;
use stenvdb\umami\services\Reports;
use stenvdb\umami\services\Tags as TagsService;
use stenvdb\umami\models\Settings;
use stenvdb\umami\services\Tags;
use stenvdb\umami\variables\UmamiVariable;
use stenvdb\umami\widgets\Statistics as StatisticsWidget;
use stenvdb\umami\widgets\Report as ReportWidget;
use stenvdb\umami\widgets\TopPages as TopPagesWidget;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Sten Van den Bergh
 * @package   Umami
 * @since     1.0.0
 *
 * @property  ReportsService $reports
 * @property  TagsService $tags
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Umami extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Umami::$plugin
     *
     * @var Umami
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';
    public $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        self::setComponents([
            'reports' => Reports::class,
            'tags' => Tags::class
        ]);

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('umami', UmamiVariable::class);
            }
        );

        // Register our widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = StatisticsWidget::class;
                $event->types[] = ReportWidget::class;
                $event->types[] = TopPagesWidget::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        Craft::info(
            Craft::t(
                'umami',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

        Event::on(Plugins::class, Plugins::EVENT_AFTER_LOAD_PLUGINS, function() {
            $request = Craft::$app->getRequest();

            if ($request->getIsSiteRequest() && !$request->getIsCpRequest()) {
                $this->handleTrackingScript();
            }
        });
    }

    public function getSettingsResponse()
    {
        return Craft::$app->controller->renderTemplate('umami/settings', [
            'settings' => $this->getSettings(),
            'fullPageForm' => true,
            'crumbs' => [
                ['label' => 'Settings', 'url' => UrlHelper::cpUrl('settings')],
                ['label' => 'Plugins', 'url' => UrlHelper::cpUrl('settings/plugins')]
            ]
        ]);
    }

    // Protected Methods
    // =========================================================================

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function handleTrackingScript()
    {
        Event::on(View::class, View::EVENT_BEGIN_BODY, function() {
            if (self::getSettings()->injectTracking) {
                self::$plugin->tags->inject();
            }
        });
    }
}
