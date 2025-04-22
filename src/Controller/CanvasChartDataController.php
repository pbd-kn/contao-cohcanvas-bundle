<?php

namespace PbdKn\ContaoCohCanvasBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Connection;

class CanvasChartDataController extends AbstractController
{
    #[Route('/_ajax/coh-canvas-data/{id}', name: 'coh_canvas_data', methods: ['GET'])]
    public function __invoke(Request $request, int $id, Connection $connection): JsonResponse
    {
        $range = $request->query->get('range', '1d');
        $since = match ($range) {
            '1w' => strtotime('-1 week'),
            '1m' => strtotime('-1 month'),
            default => strtotime('-1 day')
        };

        $stmt = $connection->executeQuery(
            'SELECT timestamp, value FROM tl_coh_sensordata WHERE tstamp >= ? ORDER BY tstamp ASC',
            [$since]
        );

        $rows = $stmt->fetchAllAssociative();
        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $labels[] = date('c', $row['timestamp']);
            $data[] = (float)$row['value'];
        }

        return new JsonResponse([
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Sensorwert',
                'data' => array_map(fn($ts, $val) => ['x' => $ts, 'y' => $val], $labels, $data),
                'borderColor' => '#3b82f6',
                'fill' => false,
                'tension' => 0.1,
            ]]
        ]);
    }
}
