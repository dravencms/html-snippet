<?php
namespace Dravencms\Model\HtmlSnippet\Entities;

use Dravencms\Model\Locale\Entities\Locale;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class HtmlSnippetTranslation
 * @package Dravencms\Model\HtmlSnippet\Entities
 * @ORM\Entity
 * @ORM\Table(name="htmlSnippetHtmlSnippetTranslation")
 */
class HtmlSnippetTranslation extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=false)
     */
    private $html;

    /**
     * @var HtmlSnippet
     * @ORM\ManyToOne(targetEntity="HtmlSnippet", inversedBy="translations")
     * @ORM\JoinColumn(name="html_snippet_id", referencedColumnName="id")
     */
    private $htmlSnippet;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * HtmlSnippetTranslation constructor.
     * @param HtmlSnippet $htmlSnippet
     * @param Locale $locale
     * @param $name
     * @param $html
     */
    public function __construct(HtmlSnippet $htmlSnippet, Locale $locale, $name, $html)
    {
        $this->name = $name;
        $this->html = $html;
        $this->htmlSnippet = $htmlSnippet;
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @param HtmlSnippet $htmlSnippet
     */
    public function setHtmlSnippet(HtmlSnippet $htmlSnippet)
    {
        $this->htmlSnippet = $htmlSnippet;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
    
    /**
     * @return Article
     */
    public function getHtmlSnippet()
    {
        return $this->htmlSnippet;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

