<?php
namespace Dravencms\Model\HtmlSnippet\Entities;

use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class HtmlSnippet
 * @package Dravencms\Model\HtmlSnippet\Entities
 * @ORM\Entity
 * @ORM\Table(name="htmlSnippetHtmlSnippet")
 */
class HtmlSnippet extends Nette\Object
{
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
     * @var ArrayCollection|ArticleTranslation[]
     * @ORM\OneToMany(targetEntity="ArticleTranslation", mappedBy="article",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * HtmlSnippet constructor.
     * @param $identifier
     * @param bool $isActive
     * @param bool $isShowTitle
     */
    public function __construct($identifier, $isActive = true, $isShowTitle = true)
    {
        $this->identifier = $identifier;
        $this->isActive = $isActive;
        $this->isShowName = $isShowTitle;
        $this->translations = new ArrayCollection();
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName($isShowName)
    {
        $this->isShowName = $isShowName;
    }


    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isShowName()
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
    public function getIdentifier()
    {
        return $this->identifier;
    }
}

