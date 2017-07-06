<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Repository;

use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\ArticleTranslation;
use Dravencms\Model\Article\Entities\Group;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Gedmo\Translatable\TranslatableListener;
use Dravencms\Model\Locale\Entities\ILocale;

class HtmlSnippetTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $articleTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->articleTranslationRepository = $entityManager->getRepository(ArticleTranslation::class);
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param Group $group
     * @param Article|null $articleIgnore
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Group $group, Article $articleIgnore = null)
    {
        $qb = $this->articleTranslationRepository->createQueryBuilder('at')
            ->select('at')
            ->join('at.article', 'a')
            ->where('at.name = :name')
            ->andWhere('a.group = :group')
            ->setParameters([
                'name' => $name,
                'group' => $group
            ]);

        if ($articleIgnore)
        {
            $qb->andWhere('a != :articleIgnore')
                ->setParameter('articleIgnore', $articleIgnore);
        }

        $query = $qb->getQuery();

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Group $group
     * @param null $query
     * @param array $tags
     * @param bool $isActive
     * @param null $limit
     * @param null $offset
     * @param Article|null $ignoreArticle
     * @return array
     */
    public function search(Group $group = null, $query = null, array $tags = [], $isActive = true, $limit = null, $offset = null, Article $ignoreArticle = null)
    {
        $qb = $this->articleTranslationRepository->createQueryBuilder('a')
            ->select('a')
            ->where('a.isActive = :isActive')
            ->setParameters(
                [
                    'isActive' => $isActive,
                ]
            );

        if ($group)
        {
            $qb->andWhere('a.group = :group')
                ->setParameter('group', $group);

            if ($group->getSortBy() == Group::SORT_BY_POSITION)
            {
                $qb->orderBy('a.position', 'ASC');
            }
            elseif ($group->getSortBy() == Group::SORT_BY_CREATED_AT)
            {
                $qb->orderBy('a.createdAt', 'DESC');
            }
        }

        if ($query)
        {
            $qb->andWhere('at.name LIKE :query')
                ->orWhere('a.text LIKE :query')
                ->orWhere('a.name LIKE :query')
                ->orWhere('a.perex LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        if (!empty($tags))
        {
            $qb->join('a.tags', 'at')
                ->andWhere('at IN (:tags)')
                ->setParameter('tags', $tags);
        }

        if ($limit)
        {
            $qb->setMaxResults($limit);
        }

        if ($offset)
        {
            $qb->setFirstResult($offset);
        }

        if ($ignoreArticle)
        {
            $qb->andWhere('a != :ignoreArticle')
                ->setParameter('ignoreArticle', $ignoreArticle);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Article $article
     * @param ILocale $locale
     * @return null|ArticleTranslation
     */
    public function getTranslation(Article $article, ILocale $locale)
    {
        return $this->articleTranslationRepository->findOneBy(['article' => $article, 'locale' => $locale]);
    }
}