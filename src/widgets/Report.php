<?php
/**
 * Umami plugin for Craft CMS 3.x
 *
 * Statistics and chart widgets for Umami
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace stenvdb\umami\widgets;

use stenvdb\umami\Umami;
use stenvdb\umami\assetbundles\reportwidget\ReportWidgetAsset;

use Craft;
use craft\base\Widget;

/**
 * Umami Widget
 *
 * Dashboard widgets allow you to display information in the Admin CP Dashboard.
 * Adding new types of widgets to the dashboard couldn’t be easier in Craft
 *
 * https://craftcms.com/docs/plugins/widgets
 *
 * @author    Sten Van den Bergh
 * @package   Umami
 * @since     1.0.0
 */
class Report extends Widget
{

    // Public Properties
    // =========================================================================

    public $period;

    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('umami', 'Umami Report');
    }

    public static function icon()
    {
        return Craft::getAlias("@stenvdb/umami/assetbundles/umami/dist/img/Widget.svg");
    }

    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    public function getTitle(): string
    {
        return '';
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                ['period', 'string'],
                ['period', 'default', 'value' => 'week']
            ]
        );
        return $rules;
    }

    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'umami/_components/widgets/report/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }

    public function getBodyHtml()
    {
        Craft::$app->getView()->registerAssetBundle(ReportWidgetAsset::class);

        $settings = $this->getSettings();
        $settings['id'] = $this->id;

        Craft::$app->getView()->registerJs("new window.Umami.ReportWidget(".json_encode($settings).");");

        return Craft::$app->getView()->renderTemplate(
            'umami/_components/widgets/report/body', [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
