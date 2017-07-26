<?php

namespace Dravencms\FrontModule\Components\HtmlSnippet\HtmlSnippet\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetTranslationRepository;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Salamek\Cms\ICmsActionOption;
use Salamek\Tempnam\Tempnam;

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

    /** @var Cache */
    private $cache;

    /** @var Tempnam */
    private $tempnam;

    public function __construct(
        ICmsActionOption $cmsActionOption,
        HtmlSnippetRepository $htmlSnippetRepository,
        HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository,
        CurrentLocale $currentLocale,
        IStorage $storage,
        Tempnam $tempnam
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->htmlSnippetTranslationRepository = $htmlSnippetTranslationRepository;
        $this->currentLocale = $currentLocale;
        $this->tempnam = $tempnam;
        $this->cache = new Cache($storage, __CLASS__);
    }
    
    public function render()
    {
        $template = $this->template;
        $htmlSnippet = $this->htmlSnippetRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));
        $htmlSnippetTranslation = $this->htmlSnippetTranslationRepository->getTranslation($htmlSnippet, $this->currentLocale);

        $template->htmlSnippet = $htmlSnippet;
        $template->htmlSnippetTranslation = $htmlSnippetTranslation;

        $key = __CLASS__.$htmlSnippetTranslation->getId();

        $tempFile = $this->tempnam->load($key, $htmlSnippetTranslation->getUpdatedAt());


        if ($tempFile === null)
        {
            $temp = file_get_contents(__DIR__ . '/detail.latte');

            $temp = strtr($temp, ['<!--HTML-SNIPPET-->' => $htmlSnippetTranslation->getHtml()]);
            $tempFile = $this->tempnam->save($key, $temp, $htmlSnippetTranslation->getUpdatedAt());
        }

        $template->setFile($tempFile);
        $template->render();
    }
}
