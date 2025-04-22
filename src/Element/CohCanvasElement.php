<?php

namespace PbdKn\ContaoCohCanvasBundle\Element;

use Contao\ContentElement;

class CohCanvasElement extends ContentElement
{
    protected $strTemplate = 'ce_coh_canvas';

    protected function compile(): void
    {
        $this->Template->chartId = 'chart_' . $this->id;
        $this->Template->ajaxUrl = '/_ajax/coh-canvas-data/' . $this->id;
    }
}
