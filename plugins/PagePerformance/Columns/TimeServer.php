<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class TimeServer extends ActionDimension
{
    protected $columnName = 'time_server';
    protected $columnType = 'MEDIUMINT(10) UNSIGNED NULL';
    protected $type = self::TYPE_DURATION_MS;
    protected $nameSingular = 'PagePerformance_ColumnTimeServer';

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        if (!($action instanceof ActionPageview)) {
            return false;
        }

        $serverTime = $request->getParam($this->getRequestParam());

        if ($serverTime === -1) {
            return false;
        }

        return $serverTime;
    }

    public function getRequestParam()
    {
        return 'pf_srv';
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric1 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_SUM);
        $metric1->setName('sum_time_server');
        $metricsList->addMetric($metric1);

        $metric2 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MAX);
        $metric2->setName('max_time_server');
        $metricsList->addMetric($metric2);

        $metric3 = $dimensionMetricFactory->createMetric('sum(if(%s is null, 0, 1))');
        $metric3->setName('pageviews_with_time_server');
        $metric3->setTranslatedName(Piwik::translate('PagePerformance_ColumnViewsWithServerTime'));
        $metricsList->addMetric($metric3);

        $metric4 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MIN);
        $metric4->setName('min_time_server');
        $metricsList->addMetric($metric4);

        $metric = $dimensionMetricFactory->createComputedMetric($metric1->getName(), $metric3->getName(), ComputedMetric::AGGREGATION_AVG);
        $metric->setName('avg_time_server');
        $metric->setTranslatedName(Piwik::translate('PagePerformance_ColumnAverageServerTime'));
        $metric->setDocumentation(Piwik::translate('PagePerformance_ColumnAverageServerTimeDocumentation'));
        $metricsList->addMetric($metric);
    }
}
