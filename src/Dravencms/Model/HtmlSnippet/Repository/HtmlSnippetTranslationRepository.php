<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Repository;

use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\ArticleTranslation;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippetTranslation;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Gedmo\Translatable\TranslatableListener;
use Dravencms\Model\Locale\Entities\ILocale;

class HtmlSnippetTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $htmlSnippetTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->htmlSnippetTranslationRepository = $entityManager->getRepository(HtmlSnippetTranslation::class);
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param HtmlSnippet|null $htmlSnippetIgnore
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, HtmlSnippet $htmlSnippetIgnore = null)
    {
        $qb = $this->htmlSnippetTranslationRepository->createQueryBuilder('hst')
            ->select('hst')
            ->join('hst.htmlSnippet', 'hs')
            ->where('hst.name = :name')
            ->andWhere('hst.locale = :locale')
            ->setParameters([
                'name' => $name,
                'locale' => $locale
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
     * @param HtmlSnippet $htmlSnippet
     * @param ILocale $locale
     * @return null|HtmlSnippetTranslation
     */
    public function getTranslation(HtmlSnippet $htmlSnippet, ILocale $locale)
    {
        return $this->htmlSnippetTranslationRepository->findOneBy(['htmlSnippet' => $htmlSnippet, 'locale' => $locale]);
    }
}