<?php

namespace Dravencms\FrontModule\Components\HtmlSnippet\HtmlSnippet\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetTranslationRepository;
use Salamek\Cms\ICmsActionOption;

class Detail extends BaseControl
{
    /** @var HtmlSnippetRepository */
    private $htmlSnippetRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var HtmlSnippetTranslationRepository */
    private $htmlSnippetTranslationRepository;

    public function __construct(
        ICmsActionOption $cmsActionOption,
        HtmlSnippetRepository $htmlSnippetRepository,
        HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->htmlSnippetTranslationRepository = $htmlSnippetTranslationRepository;
        $this->currentLocale = $currentLocale;
    }
    
    public function render()
    {
        $template = $this->template;
        $htmlSnippet = $this->htmlSnippetRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));
        $htmlSnippetTranslation = $this->htmlSnippetTranslationRepository->getTranslation($htmlSnippet, $this->currentLocale);

        $template->htmlSnippet = $htmlSnippet;
        $template->htmlSnippetTranslation = $htmlSnippetTranslation;
        $template->html = strtr($htmlSnippetTranslation->getHtml(), ['{$basePath}' => $template->basePath]);

        $template->setFile($this->cmsActionOption->getTemplatePath(__DIR__ . '/detail.latte'));
        $template->render();
    }
}
