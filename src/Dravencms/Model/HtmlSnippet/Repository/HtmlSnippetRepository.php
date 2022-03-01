<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Repository;

use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Database\EntityManager;


class HtmlSnippetRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|HtmlSnippet */
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
     * @param int $id
     * @return null|HtmlSnippet
     */
    public function getOneById(int $id): ?HtmlSnippet
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
    public function isIdentifierFree(string $identifier, HtmlSnippet $htmlSnippetIgnore = null): bool
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
     * @param $id
     * @return null|HtmlSnippet
     */
    public function getOneByIdAndActive(int $id): ?HtmlSnippet
    {
        return $this->htmlSnippetRepository->findOneBy(['id' => $id, 'isActive' => true]);
    }

    /**
     * @param array $parameters
     * @return HtmlSnippet
     */
    public function getOneByParameters(array $parameters): ?HtmlSnippet
    {
        return $this->htmlSnippetRepository->findOneBy($parameters);
    }
}