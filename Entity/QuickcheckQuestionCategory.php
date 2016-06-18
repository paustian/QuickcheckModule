<?php

namespace Paustian\QuickcheckModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="quickcheck_category",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
 */

class QuickcheckQuestionCategory extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity", inversedBy="categories")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
     * @var \Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity
     */
    private $entity;
    
    /**
     * Get entity
     *
     * @return \Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
     /**
     * Set entity
     *
     * @param \Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}

