<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Kos\BlogCustom\Block\Mageplaza\Widget;

/**
 * Class Posts
 * @package Mageplaza\Blog\Block\Widget
 */
class Posts extends \Mageplaza\Blog\Block\Widget\Posts
{
    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    // public function getCollection()
    // {

    //     if ($this->hasData('show_type') && $this->getData('show_type') === 'category') {
    //         $collection = $this->helperData->getObjectByParam($this->getData('category_id'), null, Data::TYPE_CATEGORY)
    //             ->getSelectedPostsCollection();
    //         $this->helperData->addStoreFilter($collection);
    //     } else if ($this->hasData('show_type') && $this->getData('show_type') != 'category') {
    //         $collection = $this->helperData->getPostListHomePage($this->hasData('show_type'));
    //     } else {
    //         $collection = $this->helperData->getPostList();
    //     }

    //     $collection->setPageSize($this->getData('post_count'));

    //     return $collection;
    // }
}
