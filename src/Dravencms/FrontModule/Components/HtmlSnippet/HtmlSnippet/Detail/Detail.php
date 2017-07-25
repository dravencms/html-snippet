<?php

namespace Dravencms\FrontModule\Components\HtmlSnippet\HtmlSnippet\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetTranslationRepository;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
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

    /** @var Cache */
    private $cache;

    public function __construct(
        ICmsActionOption $cmsActionOption,
        HtmlSnippetRepository $htmlSnippetRepository,
        HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository,
        CurrentLocale $currentLocale,
        IStorage $storage
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->htmlSnippetTranslationRepository = $htmlSnippetTranslationRepository;
        $this->currentLocale = $currentLocale;
        $this->cache = new Cache($storage, __CLASS__);
    }
    
    public function render()
    {
        $template = $this->template;
        $htmlSnippet = $this->htmlSnippetRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));
        $htmlSnippetTranslation = $this->htmlSnippetTranslationRepository->getTranslation($htmlSnippet, $this->currentLocale);

        $template->htmlSnippet = $htmlSnippet;
        $template->htmlSnippetTranslation = $htmlSnippetTranslation;

        $tempFile = tempnam(sys_get_temp_dir(), __CLASS__.$htmlSnippetTranslation->getId());
        
        $updateDate = $this->cache->load($tempFile);

        if ($updateDate === null || $updateDate != $htmlSnippetTranslation->getUpdatedAt())
        {
            $this->cache->save($tempFile, $htmlSnippetTranslation->getUpdatedAt());

            $temp = file_get_contents(__DIR__ . '/detail.latte');

            $temp = strtr($temp, ['<!--HTML-SNIPPET-->' => $htmlSnippetTranslation->getHtml()]);

            file_put_contents($tempFile, $temp);
        }
        
        $template->setFile($tempFile);
        $template->render();
    }
}
