<?php

namespace Paustian\QuickcheckModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\Entity\AbstractEntityCategory;

/**
 * @ORM\Entity
 * @ORM\Table(name="QuickcheckUserCategory",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
 */

class QuickcheckQuestionEntityCategory extends AbstractEntityCategory
{
    /**
     * @ORM\ManyToOne(targetEntity="QuickcheckQuestionEntity", inversedBy="categories")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
     * @var QuickcheckQuestionEntity
     */
    private $entity;

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
