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
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\HtmlSnippet\Repository\HtmlSnippetRepository;
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

    /** @var ILocale */
    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    public function __construct(
        HtmlSnippetRepository $htmlSnippetRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->htmlSnippetRepository = $htmlSnippetRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->entityManager = $entityManager;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function createComponentGrid($name)
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->htmlSnippetRepository->getHtmlSnippetQueryBuilder());

        $grid->setDefaultSort(['createdAt' => 'DESC']);

        $grid->addColumnText('identifier', 'Identifier')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnBoolean('isActive', 'Active');
        $grid->addColumnBoolean('isShowName', 'Show name');

        $grid->addColumnDateTime('createdAt', 'Created')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        if ($this->presenter->isAllowed('htmlSnippet', 'edit'))
        {
            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('htmlSnippet', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        $grid->addExportCsvFiltered('Csv export (filtered)', 'acl_resource_filtered.csv')
            ->setTitle('Csv export (filtered)');

        $grid->addExportCsv('Csv export', 'acl_resource_all.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids)
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(htmlSnippet, delete)
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
