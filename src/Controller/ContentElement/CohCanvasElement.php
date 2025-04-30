<?php

namespace PbdKn\ContaoCohCanvasBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Contao\System;
use Contao\BackendTemplate;
use Contao\StringUtil;

#[AsContentElement(CohCanvasElement::TYPE, category: 'COH-Canvas-FE', template: 'ce_coh_canvas')]
//#[AsContentElement(CohCanvasElement::TYPE, category: 'COH-Canvas-FE')]
class CohCanvasElement extends AbstractContentElementController
{
    public const TYPE = 'coh_canvas';

    public function __construct(private readonly Environment $twig) {}

    protected function getResponse($template, ContentModel $model, Request $request): Response
    {
        $scope = System::getContainer()
            ->get('request_stack')
            ?->getCurrentRequest()
            ?->attributes
            ?->get('_scope');

        if ('backend' === $scope) {
            $wildcard = new BackendTemplate('be_wildcard');

            $headline = StringUtil::deserialize($model->headline);

            $wildcard->wildcard = '### COH CANVAS ###';
            $wildcard->title = $headline['value'] ?? 'kein Titel';
            $wildcard->id = $model->id;
            $wildcard->link = $headline['value'] ?? 'kein Link';
            $wildcard->href = 'contao?do=themes&table=tl_content&id=' . $model->id;

            return new Response($wildcard->parse());
        }
        // Frontend: Template dynamisch setzen, wenn Auswahl im DCA existiert
        if ($model->coh_canvas_template) {
            $template->setName($model->coh_canvas_template);
        }

        // Jetzt normale Daten setzen
        $template->chartId = 'chart_' . $model->id;
        $template->ajaxUrl = '/_ajax/coh-canvas-data/' . $model->id;
        return $template->getResponse();
    }    
}
