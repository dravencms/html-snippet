<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Repository;

use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippetTranslation;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\ILocale;

class HtmlSnippetTranslationRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|HtmlSnippetTranslation */
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
    public function isNameFree(string $name, ILocale $locale, HtmlSnippet $htmlSnippetIgnore = null): bool
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
    public function getTranslation(HtmlSnippet $htmlSnippet, ILocale $locale): ?HtmlSnippetTranslation
    {
        return $this->htmlSnippetTranslationRepository->findOneBy(['htmlSnippet' => $htmlSnippet, 'locale' => $locale]);
    }
}