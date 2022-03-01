<?php declare(strict_types = 1);
namespace Dravencms\Model\HtmlSnippet\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class HtmlSnippet
 * @package Dravencms\Model\HtmlSnippet\Entities
 * @ORM\Entity
 * @ORM\Table(name="htmlSnippetHtmlSnippet")
 */
class HtmlSnippet
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false, unique=true)
     */
    private $identifier;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isShowName;

    /**
     * @var ArrayCollection|HtmlSnippetTranslation[]
     * @ORM\OneToMany(targetEntity="HtmlSnippetTranslation", mappedBy="htmlSnippet",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * HtmlSnippet constructor.
     * @param $identifier
     * @param bool $isActive
     * @param bool $isShowTitle
     */
    public function __construct(string $identifier, bool $isActive = true, bool $isShowTitle = true)
    {
        $this->identifier = $identifier;
        $this->isActive = $isActive;
        $this->isShowName = $isShowTitle;
        $this->translations = new ArrayCollection();
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName(bool $isShowName): void
    {
        $this->isShowName = $isShowName;
    }


    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isShowName(): bool
    {
        return $this->isShowName;
    }

    /**
     * @return ArrayCollection|ArticleTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

