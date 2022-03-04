<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\HtmlSnippet\HtmlSnippet\Detail;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetTranslationRepository;
use Dravencms\Structure\ICmsActionOption;
use Latte\Loaders\StringLoader;

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

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param HtmlSnippetRepository $htmlSnippetRepository
     * @param HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        HtmlSnippetRepository $htmlSnippetRepository,
        HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->htmlSnippetTranslationRepository = $htmlSnippetTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }

    public function render(): void
    {
        $htmlSnippet = $this->htmlSnippetRepository->getOneByIdAndActive($this->cmsActionOption->getParameter('id'));
        $htmlSnippetTranslation = $this->htmlSnippetTranslationRepository->getTranslation($htmlSnippet, $this->currentLocale);

        $template = $this->template;
        $template->getLatte()->setLoader(new StringLoader([
            'html.snippet' => $htmlSnippetTranslation->getHtml(),
            'html.snippet.container' => file_get_contents(__DIR__.'/detail.latte')
        ]));
       
        $template->htmlSnippet = $htmlSnippet;
        $template->htmlSnippetTranslation = $htmlSnippetTranslation;

        $template->setFile('html.snippet.container');
        $template->render();
    }
}
