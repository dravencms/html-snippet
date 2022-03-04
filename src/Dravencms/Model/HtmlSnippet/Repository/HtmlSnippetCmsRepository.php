<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Repository;

use Dravencms\Model\HtmlSnippet\Entities\HtmlSnippet;
use Dravencms\Structure\CmsActionOption;
use Dravencms\Structure\ICmsActionOption;
use Dravencms\Structure\ICmsComponentRepository;


class HtmlSnippetCmsRepository implements ICmsComponentRepository
{
    /** @var HtmlSnippetRepository */
    private $htmlSnippetRepository;
    
    public function __construct(HtmlSnippetRepository $htmlSnippetRepository)
    {
        $this->htmlSnippetRepository = $htmlSnippetRepository;
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions(string $componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
                $return = [];
                /** @var HtmlSnippet $htmlSnippet */
                foreach ($this->htmlSnippetRepository->getActive() AS $htmlSnippet) {
                    $return[] = new CmsActionOption($htmlSnippet->getIdentifier(), ['id' => $htmlSnippet->getId()]);
                }
                break;

            default:
                return false;
                break;
        }
        

        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @return null|CmsActionOption
     */
    public function getActionOption(string $componentAction, array $parameters)
    {
        /** @var HtmlSnippet $found */
        $found = $this->htmlSnippetRepository->getOneByParameters($parameters + ['isActive' => true]);
        
        if ($found)
        {
            return new CmsActionOption($found->getIdentifier(), $parameters);
        }

        return null;
    }
}