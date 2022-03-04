<?php declare(strict_types = 1);
/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\HtmlSnippet\HtmlSnippetForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippetTranslation;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetTranslationRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Dravencms\Components\BaseForm\Form;

/**
 * Description of HtmlSnippetForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class HtmlSnippetForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var HtmlSnippetRepository */
    private $htmlSnippetRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var HtmlSnippetTranslationRepository */
    private $htmlSnippetTranslationRepository;
    
    /** @var User */
    private $user;

    /** @var HtmlSnippet|null */
    private $htmlSnippet = null;

    /** @var array */
    public $onSuccess = [];

    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        User $user,
        HtmlSnippetRepository $htmlSnippetRepository,
        HtmlSnippetTranslationRepository $htmlSnippetTranslationRepository,
        LocaleRepository $localeRepository,
        HtmlSnippet $htmlSnippet = null
    ) {
        $this->htmlSnippet = $htmlSnippet;
        $this->user = $user;
        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->htmlSnippetTranslationRepository = $htmlSnippetTranslationRepository;
        $this->localeRepository = $localeRepository;

        if ($this->htmlSnippet) {
            $defaults = [
                'identifier' => $this->htmlSnippet->getIdentifier(),
                'isActive' => $this->htmlSnippet->isActive(),
                'isShowName' => $this->htmlSnippet->isShowName()
            ];

            foreach ($this->htmlSnippet->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaults[$translation->getLocale()->getLanguageCode()]['html'] = $translation->getHtml();
            }
        } else {
            $defaults = [
                'isActive' => true,
                'isShowName' => false
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter article name.')
                ->addRule(Form::MAX_LENGTH, 'Article name is too long.', 255);

            $container->addTextArea('html');
        }

        $form->addText('identifier')
            ->setRequired('Please fill in an identifier');

        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowName');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->htmlSnippetTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->htmlSnippet)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->user->isAllowed('htmlSnippet', 'edit')) {
            $form->addError('Nemáte oprávění editovat htmlSnippet.');
        }
    }

    /**
     * @param Form $form
     * @return void
     */
    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        if ($this->htmlSnippet) {
            $htmlSnippet = $this->htmlSnippet;
            $htmlSnippet->setIdentifier($values->identifier);
            $htmlSnippet->setIsActive($values->isActive);
            $htmlSnippet->setIsShowName($values->isShowName);
        } else {
            $htmlSnippet = new HtmlSnippet($values->identifier, $values->isActive, $values->isShowName);
        }

        $this->entityManager->persist($htmlSnippet);

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($htmlSnippetTranslation = $this->htmlSnippetTranslationRepository->getTranslation($htmlSnippet, $activeLocale))
            {
                $htmlSnippetTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $htmlSnippetTranslation->setHtml($values->{$activeLocale->getLanguageCode()}->html);
            }
            else
            {
                $htmlSnippetTranslation = new HtmlSnippetTranslation(
                    $htmlSnippet,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name,
                    $values->{$activeLocale->getLanguageCode()}->html
                );
            }
            $this->entityManager->persist($htmlSnippetTranslation);
        }
        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/HtmlSnippetForm.latte');
        $template->render();
    }
}