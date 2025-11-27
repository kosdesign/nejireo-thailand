<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kos\CatalogExtend\Block\Category;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category\Tree as CategoryTree;
use Magento\Framework\App\ObjectManager;

/**
 * Html menu block
 *
 * @api
 * @since 100.0.2
 */
class Tree extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * @var CategoryRepositoryInterface $categoryRepository
     */
    protected $categoryRepository;

    protected $categoryTree;

    /**
     * Tree constructor.
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryTree $categoryTree
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        CategoryRepositoryInterface $categoryRepository,
        CategoryTree $categoryTree,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $nodeFactory,
            $treeFactory,
            $data
        );
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $store = $this->_storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        $category = $this->getCategoryById($rootId);

        $this->getCustomMenu($rootId)->setOutermostClass($outermostClass);
        $this->getCustomMenu($rootId)->setChildrenWrapClass($childrenWrapClass);

        $allCategories = $category->getChildrenCategories();
        $html = '';

        foreach ($allCategories as $subcat) {
            if ($subcat->getId() == $rootId) {
                continue;
            }

            $html .= $this->_getHtml(
                $this->getCustomMenu($subcat->getId()),
                $childrenWrapClass,
                $limit
            );
        }

        return $html;
    }

    /**
     * @param null $id
     * @return Node|null
     */
    public function getCustomMenu($id = null)
    {
        $category = $this->getCategoryById($id);
        return $this->categoryTree->getRootNode($category);
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     */
    protected function _getHtml(
        Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $childLevel = $this->getChildLevel($menuTree->getLevel());
        $this->removeChildrenWithoutActiveParent($children, $childLevel);

        $counter = 1;
        $childrenCount = $children->count();
        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        /** @var Node $child */
        foreach ($children as $child) {
            $category = $this->getCategoryById($child->getId());
            if ($category->getIncludeInMenu() == 1 && $category->getIsActive() == 1) :
                $child->setLevel($childLevel);
                $child->setIsFirst($counter === 1);
                $child->setIsLast($counter === $childrenCount);
                $child->setPositionClass($itemPositionClassPrefix . $counter);

                $outermostClassCode = '';
                $outermostClass = $menuTree->getOutermostClass();

                if ($childLevel === 0 && $outermostClass) {
                    $outermostClassCode = ' class="' . $outermostClass . '" ';
                    $this->setCurrentClass($child, $outermostClass);
                }

                if ($this->shouldAddNewColumn($colBrakes, $counter)) {
                    $html .= '</ul></li><li class="column"><ul>';
                }

                $categoryUrl = $category ? $category->getUrl() : '#';
                $categoryName = $category ? $category->getName() : $child->getName();
                $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<a href="' . $categoryUrl . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                        $categoryName
                    ) . '</span></a>' . $this->_addSubMenu(
                        $child,
                        $childLevel,
                        $childrenWrapClass,
                        $limit
                    ) . '<div class="child-image" data-image="' . $this->getCategoryImage(
                        $child->getId()
                    ) . '"></div></li>';
                $counter++;
            endif;
        }

        if (is_array($colBrakes) && !empty($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';

        if (!$child->hasChildren() || $this->getLevel($childLevel)  > 2) {
            return $html;
        }

        $colStops = [];
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }
        $html .= '<div class="subcat-list sublevel-'.$this->getLevel($childLevel).'">';
        $html .= $this->_getCategoryImage($child);
        $html .= '<div class="subcat-link-list scroll-'.$this->getLevel($childLevel).'">';
        $html .= '<ul class="level' . $this->getLevel($childLevel) . ' ' . $childrenWrapClass . '">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param $child
     * @return string
     */
    protected function _getCategoryImage($child)
    {
        $menuId = $child->getId();
        $html = '';
        if ($this->isCategory($child)) {
            $html .= '<div class="subcat-image">';
            $html .= '<div class="image" data-image="' . $this->getCategoryImage($menuId)
                . '" style="background-image:url(' . $this->getCategoryImage($menuId) . ')"></div>';
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * Retrieves the category image for the corresponding child
     *
     * @param string $categoryId Category composed ID
     *
     * @return string
     */
    protected function getCategoryImage($categoryId)
    {
        $category = $this->getCategoryById($categoryId);
        return $category->getImageUrl();
    }

    /**
     * Check if current menu element corresponds to a category
     *
     * @param string $menuId Menu element composed ID
     *
     * @return string
     */
    protected function isCategory($item)
    {
        return $item && $item->getId();
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Node $item)
    {
        $classes = [
            'level' . $this->getLevel($item->getLevel()),
            $item->getPositionClass(),
        ];

        if ($this->isCategory($item)) {
            $classes[] = 'category-item';
        }

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren() && $this->getLevel($item->getLevel()) < 2 ) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    /**
     * Remove children from collection when the parent is not active
     *
     * @param Collection $children
     * @param int $childLevel
     * @return void
     */
    private function removeChildrenWithoutActiveParent(Collection $children, int $childLevel): void
    {
        /** @var Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                $children->delete($child);
            }
        }
    }

    /**
     * Retrieve child level based on parent level
     *
     * @param int $parentLevel
     *
     * @return int
     */
    private function getChildLevel($parentLevel): int
    {
        return $parentLevel === null ? 0 : $parentLevel + 1;
    }

    /**
     * Check if new column should be added.
     *
     * @param array $colBrakes
     * @param int $counter
     * @return bool
     */
    private function shouldAddNewColumn(array $colBrakes, int $counter): bool
    {
        return count($colBrakes) && $colBrakes[$counter]['colbrake'];
    }

    /**
     * Set current class.
     *
     * @param Node $child
     * @param string $outermostClass
     */
    private function setCurrentClass(Node $child, string $outermostClass): void
    {
        $currentClass = $child->getClass();
        if (empty($currentClass)) {
            $child->setClass($outermostClass);
        } else {
            $child->setClass($currentClass . ' ' . $outermostClass);
        }
    }

    /**
     * @param $level
     * @return mixed
     */
    private function getLevel($level)
    {
        return ($level > 2) ? $level - 3 : $level;
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Api\Data\CategoryInterface|null
     */
    private function getCategoryById($id)
    {
        try {
	            $objectManager = ObjectManager::getInstance();
            $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            $current_store =  $storeManager->getStore()->getId();
            return $this->categoryRepository->get($id,$current_store);
        } catch (\Exception $e) {
            return null;
        }
    }
}
