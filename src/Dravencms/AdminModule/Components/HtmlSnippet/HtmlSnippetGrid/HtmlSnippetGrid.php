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

namespace Dravencms\AdminModule\Components\HtmlSnippet\HtmlSnippetGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Description of HtmlSnippetGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class HtmlSnippetGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var HtmlSnippetRepository */
    private $htmlSnippetRepository;

    /** @var EntityManager */
    private $entityManager;

    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    public function __construct(
        HtmlSnippetRepository $htmlSnippetRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->currentLocale = $currentLocale;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->htmlSnippetRepository->getHtmlSnippetQueryBuilder());

        $grid->setDefaultSort(['createdAt' => 'DESC']);

        $grid->addColumnText('identifier', 'Identifier')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnBoolean('isActive', 'Active');
        $grid->addColumnBoolean('isShowName', 'Show name');

        $grid->addColumnDate('createdAt', 'Created', $this->currentLocale->getDateTimeFormat())
            ->setSortable()
            ->setFilterDate();
        $grid->getColumn('createdAt')->cellPrototype->class[] = 'center';

        if ($this->presenter->isAllowed('htmlSnippet', 'edit')) {
            $grid->addActionHref('edit', 'Upravit')
                ->setCustomHref(function($row){
                    return $this->presenter->link('edit', ['id' => $row->getId()]);
                })
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('htmlSnippet', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat article %s ?', $row->getIdentifier()];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i articles ?');
        }
        $grid->setExport();

        return $grid;
    }

    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $htmlSnippets = $this->htmlSnippetRepository->getById($id);
        foreach ($htmlSnippets AS $htmlSnippet)
        {
            $this->entityManager->remove($htmlSnippet);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/HtmlSnippetGrid.latte');
        $template->render();
    }
}
