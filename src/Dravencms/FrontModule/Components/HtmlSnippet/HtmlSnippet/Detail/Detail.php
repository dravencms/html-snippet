<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\HtmlSnippet\HtmlSnippet\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
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

    /** @var ILocale */
    private $currentLocale;

    /** @var HtmlSnippetTranslationRepository */
    private $htmlSnippetTranslationRepository;

    /** @var Cache */
    private $cache;

    /** @var Tempnam */
    private $tempnam;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param HtmlSnippetRepository $htmlSnippetRepository
     * @param HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param IStorage $storage
     * @param Tempnam $tempnam
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        HtmlSnippetRepository $htmlSnippetRepository,
        HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver,
        IStorage $storage,
        Tempnam $tempnam
    )
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->htmlSnippetTranslationRepository = $htmlSnippetTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->tempnam = $tempnam;
        $this->cache = new Cache($storage, __CLASS__);
    }

    public function render(): void
    {
        $template = $this->template;
        $htmlSnippet = $this->htmlSnippetRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));
        $htmlSnippetTranslation = $this->htmlSnippetTranslationRepository->getTranslation($htmlSnippet, $this->currentLocale);

        $template->htmlSnippet = $htmlSnippet;
        $template->htmlSnippetTranslation = $htmlSnippetTranslation;

        $key = __CLASS__ . $htmlSnippetTranslation->getId();

        $tempFile = $this->tempnam->load($key, $htmlSnippetTranslation->getUpdatedAt());
        $invalidateLatteCache = false;   
        if ($tempFile === null) {
            $temp = file_get_contents(__DIR__ . '/detail.latte');

            $temp = strtr($temp, ['<!--HTML-SNIPPET-->' => $htmlSnippetTranslation->getHtml()]);
            $tempFile = $this->tempnam->save($key, $temp, $htmlSnippetTranslation->getUpdatedAt());
            $invalidateLatteCache = true;
        }

        $template->setFile($tempFile);
        if ($invalidateLatteCache) {
            $latteTmpFile = $template->getLatte()->getCacheFile($template->getFile());
            if (is_file($latteTmpFile)) unlink($latteTmpFile);
        }
        $template->render();
    }
}
