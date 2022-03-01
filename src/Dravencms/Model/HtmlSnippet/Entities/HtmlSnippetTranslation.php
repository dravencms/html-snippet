<?php declare(strict_types = 1);
namespace Dravencms\Model\HtmlSnippet\Entities;

use Dravencms\Model\Locale\Entities\Locale;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class HtmlSnippetTranslation
 * @package Dravencms\Model\HtmlSnippet\Entities
 * @ORM\Entity
 * @ORM\Table(name="htmlSnippetHtmlSnippetTranslation")
 */
class HtmlSnippetTranslation
{
    use Nette\SmartObject;
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
    public function __construct(HtmlSnippet $htmlSnippet, Locale $locale, string $name, string $html)
    {
        $this->name = $name;
        $this->html = $html;
        $this->htmlSnippet = $htmlSnippet;
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $html
     */
    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    /**
     * @param HtmlSnippet $htmlSnippet
     */
    public function setHtmlSnippet(HtmlSnippet $htmlSnippet): void
    {
        $this->htmlSnippet = $htmlSnippet;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }
    
    /**
     * @return HtmlSnippet
     */
    public function getHtmlSnippet(): HtmlSnippet
    {
        return $this->htmlSnippet;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }
}

