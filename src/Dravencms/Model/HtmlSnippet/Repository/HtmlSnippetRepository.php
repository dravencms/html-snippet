<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Repository;

use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Kdyby\Doctrine\EntityManager;
use Nette;

class HtmlSnippetRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $htmlSnippetRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * HtmlSnippetRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->htmlSnippetRepository = $entityManager->getRepository(HtmlSnippet::class);
    }

    /**
     * @param $id
     * @return mixed|null|HtmlSnippet
     */
    public function getOneById($id)
    {
        return $this->htmlSnippetRepository->find($id);
    }

    /**
     * @param $id
     * @return HtmlSnippet[]
     */
    public function getById($id)
    {
        return $this->htmlSnippetRepository->findBy(['id' => $id]);
    }

    /**
     * @return HtmlSnippet[]
     */
    public function getActive()
    {
        return $this->htmlSnippetRepository->findBy(['isActive' => true]);
    }

    /**
     * @param $identifier
     * @param HtmlSnippet|null $htmlSnippetIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree($identifier, HtmlSnippet $htmlSnippetIgnore = null)
    {
        $qb = $this->htmlSnippetRepository->createQueryBuilder('hs')
            ->select('hs')
            ->where('hs.identifier = :identifier')
            ->setParameters([
                'identifier' => $identifier
            ]);

        if ($htmlSnippetIgnore)
        {
            $qb->andWhere('hs != :htmlSnippetIgnore')
                ->setParameter('htmlSnippetIgnore', $htmlSnippetIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getHtmlSnippetQueryBuilder()
    {
        $qb = $this->htmlSnippetRepository->createQueryBuilder('hs')
            ->select('hs');
        return $qb;
    }

    /**
     * @param array $parameters
     * @return Article
     */
    public function getOneByParameters(array $parameters)
    {
        return $this->htmlSnippetRepository->findOneBy($parameters);
    }
}