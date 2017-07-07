<?php

namespace Dravencms\AdminModule\HtmlSnippetModule;

use Dravencms\AdminModule\Components\HtmlSnippet\HtmlSnippetForm\HtmlSnippetFormFactory;
use Dravencms\AdminModule\Components\HtmlSnippet\HtmlSnippetGrid\HtmlSnippetGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Flash;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;

/**
 * Description of HtmlSnippetPresenter
 *
 * @author Adam Schubert
 */
class HtmlSnippetPresenter extends SecuredPresenter
{

    /** @var HtmlSnippetRepository @inject */
    public $htmlSnippetRepository;

    /** @var HtmlSnippetGridFactory @inject */
    public $htmlSnippetGridFactory;

    /** @var HtmlSnippetFormFactory @inject */
    public $htmlSnippetFormFactory;

    /** @var HtmlSnippet|null */
    private $htmlSnippet = null;

    /**
     * @isAllowed(htmlSnippet,edit)
     */
    public function actionDefault()
    {
        $this->template->h1 = 'Html snippets';
    }

    /**
     * @isAllowed(htmlSnippet,edit)
     * @param $id
     */
    public function actionEdit($id = null)
    {
        if ($id) {
            $htmlSnippet = $this->htmlSnippetRepository->getOneById($id);

            if (!$htmlSnippet) {
                $this->error();
            }

            $this->htmlSnippet = $htmlSnippet;

            $this->template->h1 = sprintf('Edit html snippet „%s“', $htmlSnippet->getIdentifier());
        } else {
            $this->template->h1 = 'New html snippet';
        }
    }

    /**
     * @return \Dravencms\AdminModule\Components\HtmlSnippet\HtmlSnippetForm\HtmlSnippetForm
     */
    protected function createComponentHtmlSnippetForm()
    {
        $control = $this->htmlSnippetFormFactory->create($this->htmlSnippet);
        $control->onSuccess[] = function(){
            $this->flashMessage('Html snippet has been successfully saved', Flash::SUCCESS);
            $this->redirect('HtmlSnippet:');
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\HtmlSnippet\HtmlSnippetGrid\HtmlSnippetGrid
     */
    public function createComponentHtmlSnippetGrid()
    {
        $control = $this->htmlSnippetGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Html snippet has been successfully deleted', Flash::SUCCESS);
            $this->redirect('HtmlSnippet:');
        };
        return $control;
    }
}
