<?php

namespace PbdKn\ContaoCohCanvasBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Doctrine\DBAL\Connection;
use Contao\System;
use Contao\BackendTemplate;
use Contao\StringUtil;

#[AsContentElement(CohCanvasElement::TYPE, category: 'COH-Canvas', template: 'ce_coh_canvas')]
class CohCanvasElement extends AbstractContentElementController
{
    public const TYPE = 'coh_canvas';

    public function __construct(
        private readonly Environment $twig,
        private readonly Connection $connection
    ) {}

    protected function getResponse($template, ContentModel $model, Request $request): Response
    {
    $scope = System::getContainer()
            ->get('request_stack')
            ?->getCurrentRequest()
            ?->attributes
            ?->get('_scope');

        if ('backend' === $scope) {
            // ?? Wildcard für Backend-Vorschau
            $wildcard = new BackendTemplate('be_wildcard');

            $headline = StringUtil::deserialize($model->headline);
            $selectedSensors = StringUtil::deserialize($model->selectedSensors, true);

            $wildcard->wildcard = '### COH CANVAS ###';
            $wildcard->title = $headline['value'] ?? 'kein Titel';
            $wildcard->id = $model->id;
            $wildcard->link = 'Selektierte Sensoren: ' . implode(', ', $selectedSensors);
            $wildcard->href = 'contao?do=themes&table=tl_content&id=' . $model->id;

            return new Response($wildcard->parse());
        }
        $range = $request->query->get('range', '1d');
        $since = match ($range) {
            '1w' => strtotime('-1 week'),
            '1m' => strtotime('-1 month'),
            default => strtotime('-1 day'),
        };

        $selectedSensors = StringUtil::deserialize($model->selectedSensors, true);
        $datasets = [];
        $sensorUnits = [];  // sensorID => Einheit
        $usedAxes = [];     // axisID => ['unit' => '°C', 'color' => '#abc']
        $allTimestamps = [];

        if (!empty($selectedSensors)) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT tstamp, sensorID, sensorValue, sensorEinheit FROM tl_coh_sensorvalue
                 WHERE tstamp >= ? AND sensorID IN (?) ORDER BY tstamp ASC',
                [$since, $selectedSensors],
                [\PDO::PARAM_INT, Connection::PARAM_STR_ARRAY]
            );

            foreach ($rows as $row) {
                $timestamp = date('c', $row['tstamp']);
                $sensorId = $row['sensorID'];
                $value = (float)$row['sensorValue'];
                $einheit = trim($row['sensorEinheit']) ?: 'default';

                $sensorUnits[$sensorId] ??= $einheit;
                $axisId = 'y_' . preg_replace('/[^a-z0-9]/i', '_', $sensorUnits[$sensorId]);
                $color = $this->getSensorColor($sensorId);
                $allTimestamps[] = $timestamp;

                if (!isset($datasets[$sensorId])) {
                    $datasets[$sensorId] = [
                        'label' => $sensorId,
                        'data' => [],
                        'borderColor' => $color,
                        'fill' => false,
                        'tension' => 0.1,
                        'yAxisID' => $axisId,
                    ];

                    // Nur erste Farbe pro Achse merken
                    if (!isset($usedAxes[$axisId])) {
                        $usedAxes[$axisId] = ['unit' => $einheit, 'color' => $color];
                    }
                }

                $datasets[$sensorId]['data'][] = ['x' => $timestamp, 'y' => $value];
            }
        }

        $chartData = [
            'labels' => array_values(array_unique($allTimestamps)),
            'datasets' => array_values($datasets),
            'axes' => $usedAxes,
        ];

        $template->chartId = 'chart_' . $model->id;
        $template->chartdata = json_encode($chartData, JSON_THROW_ON_ERROR);
        $template->range = $range;
        $template->selectedSensors = $selectedSensors;

        return $template->getResponse();
    }

    private function getSensorColor(int|string $id): string
    {
        $colors = ['#60A5FA', '#F87171', '#34D399', '#FBBF24', '#A78BFA', '#F472B6'];
        $idNumeric = is_numeric($id) ? (int)$id : crc32($id);
        return $colors[$idNumeric % count($colors)];
    }
}
