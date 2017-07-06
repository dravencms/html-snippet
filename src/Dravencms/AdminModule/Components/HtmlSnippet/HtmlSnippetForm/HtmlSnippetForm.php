<?php
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
use Dravencms\File\File;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippetTranslation;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetTranslationRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

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

    /** @var ArticleRepository */
    private $articleRepository;

    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var ArticleTranslationRepository */
    private $articleTranslationRepository;

    /** @var TagTranslationRepository */
    private $tagTranslationRepository;

    /** @var Group */
    private $group;

    /** @var File */
    private $file;

    /** @var Article|null */
    private $article = null;

    /** @var array */
    public $onSuccess = [];

    public function __construct(
        Group $group,
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ArticleRepository $articleRepository,
        ArticleTranslationRepository $articleTranslationRepository,
        TagRepository $tagRepository,
        TagTranslationRepository $tagTranslationRepository,
        StructureFileRepository $structureFileRepository,
        LocaleRepository $localeRepository,
        File $file,
        Article $article = null
    ) {
        parent::__construct();

        $this->group = $group;
        $this->article = $article;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
        $this->articleTranslationRepository = $articleTranslationRepository;
        $this->tagRepository = $tagRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->tagTranslationRepository = $tagTranslationRepository;
        $this->localeRepository = $localeRepository;
        $this->file = $file;


        if ($this->article) {
            $tags = [];
            foreach ($this->article->getTags() AS $tag) {
                $tags[$tag->getId()] = $tag->getId();
            }

            $defaults = [
                'structureFile' => ($this->article->getStructureFile() ? $this->article->getStructureFile()->getId() : null),
                'position' => $this->article->getPosition(),
                'identifier' => $this->article->getIdentifier(),
                'isActive' => $this->article->isActive(),
                'isShowName' => $this->article->isShowName(),
                'isAutoDetectTags' => $this->article->isAutoDetectTags(),
                'tags' => $tags
            ];

            foreach ($this->article->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaults[$translation->getLocale()->getLanguageCode()]['lead'] = $translation->getLead();
                $defaults[$translation->getLocale()->getLanguageCode()]['subtitle'] = $translation->getSubtitle();
                $defaults[$translation->getLocale()->getLanguageCode()]['perex'] = $translation->getPerex();
                $defaults[$translation->getLocale()->getLanguageCode()]['text'] = $translation->getText();
            }
        } else {
            $defaults = [
                'isActive' => true,
                'isShowName' => true,
                'isAutoDetectTags' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter article name.')
                ->addRule(Form::MAX_LENGTH, 'Article name is too long.', 255);

            $container->addText('lead')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Article lead is too long.', 255);

            $container->addText('subtitle')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Article subtitle is too long.', 255);

            $container->addTextArea('perex');

            $container->addTextArea('text');
        }

        $form->addText('identifier')
            ->setRequired('Please fill in an identifier');


        $form->addText('structureFile');

        $form->addText('position')
            ->setDisabled((is_null($this->article)));

        $form->addMultiSelect('tags', null, $this->tagRepository->getPairs());

        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowName');
        $form->addCheckbox('isAutoDetectTags');


        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->articleTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->group, $this->article)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->presenter->isAllowed('article', 'edit')) {
            $form->addError('Nemáte oprávění editovat article.');
        }
    }

    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        if ($values->isAutoDetectTags) {
            foreach ($this->localeRepository->getActive() AS $activeLocale) {
                foreach ($this->tagTranslationRepository->getAll($activeLocale) AS $tag) {
                    if (strpos($values->{$activeLocale->getLanguageCode()}->text, $tag->getName()) !== false && !in_array($tag->getTag()->getId(), $values->tags)) {
                        $values->tags[$tag->getTag()->getId()] = $tag->getTag()->getId();
                    }
                }
            }
        }

        $tags = new ArrayCollection($this->tagRepository->getById($values->tags));

        if ($values->structureFile) {
            $structureFile = $this->structureFileRepository->getOneById($values->structureFile);
        } else {
            $structureFile = null;
        }

        if ($this->article) {
            $article = $this->article;
            $article->setIdentifier($values->identifier);
            $article->setStructureFile($structureFile);
            $article->setIsActive($values->isActive);
            $article->setIsShowName($values->isShowName);
            $article->setIsAutoDetectTags($values->isAutoDetectTags);
            $article->setPosition($values->position);
        } else {
            $article = new Article($this->group, $values->isActive, $values->isShowName, $values->isAutoDetectTags, $structureFile);
        }
        $article->setTags($tags);

        $this->entityManager->persist($article);

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($articleTranslation = $this->articleTranslationRepository->getTranslation($article, $activeLocale))
            {
                $articleTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $articleTranslation->setSubtitle($values->{$activeLocale->getLanguageCode()}->subtitle);
                $articleTranslation->setLead($values->{$activeLocale->getLanguageCode()}->lead);
                $articleTranslation->setText($values->{$activeLocale->getLanguageCode()}->text);
                $articleTranslation->setPerex($values->{$activeLocale->getLanguageCode()}->perex);
            }
            else
            {
                $articleTranslation = new ArticleTranslation(
                    $article,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name,
                    $values->{$activeLocale->getLanguageCode()}->subtitle,
                    $values->{$activeLocale->getLanguageCode()}->lead,
                    $this->cleanText($values->{$activeLocale->getLanguageCode()}->text),
                    $values->{$activeLocale->getLanguageCode()}->perex
                );
            }
            $this->entityManager->persist($articleTranslation);
        }
        $this->entityManager->flush();

        $this->onSuccess();
    }

    /**
     * @param $text
     * @return string
     */
    private function cleanText($text)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $text = mb_convert_encoding($text, 'HTML-ENTITIES', "UTF-8");
        @$dom->loadHTML($text);

        foreach ($dom->getElementsByTagName('div') as $node) {
            $class = $node->getAttribute("class");
            $classArray = explode(' ', $class);
            $hasNote = false;
            foreach ($classArray AS $k => $classItem) {
                if (strpos($classItem, 'note-') !== false) {
                    $hasNote = true;
                    unset($classArray[$k]);
                }
            }

            if ($hasNote) {
                $node->removeAttribute("id");
                $node->removeAttribute("contenteditable");
            }

            $node->setAttribute("class", implode(' ', $classArray));
        }

        foreach ($dom->getElementsByTagName('iframe') as $node) {
            if ($node->hasAttribute("frameborder")) {
                $node->removeAttribute("frameborder");
            }
        }

        $unstyledElements = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($unstyledElements AS $unstyledElement) {
            foreach ($dom->getElementsByTagName($unstyledElement) as $node) {
                if ($node->hasAttribute("style")) {
                    $node->removeAttribute("style");
                }
            }
        }

        $xpath = new \DOMXPath($dom);
        $body = $xpath->query('/html/body');
        return html_entity_decode(strtr($dom->saveHTML($body->item(0)), array('<body>' => '', '</body>' => '')));
    }

    public function render()
    {
        $template = $this->template;
        $template->fileSelectorPath = $this->file->getFileSelectorPath();
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/ArticleForm.latte');
        $template->render();
    }
}