<?php

namespace Kos\BlogCustom\Helper\Mageplaza\Blog;


class Data extends \Mageplaza\Blog\Helper\Data
{
        /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     *
     * @return PostCollection
     * @throws NoSuchEntityException
     */
    public function getPostCollection($type = null, $id = null, $storeId = null)
    {
        if ($id === null) {
            $id = $this->_request->getParam('id');
        }

        /** @var PostCollection $collection */
        $collection = $this->getPostList($storeId);

        switch ($type) {
            case self::TYPE_AUTHOR:
                $collection->addFieldToFilter('author_id', $id);
                break;
            case self::TYPE_CATEGORY:
                $collection->join(
                    ['category' => $collection->getTable('mageplaza_blog_post_category')],
                    'main_table.post_id=category.post_id AND category.category_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_TAG:
                $collection->join(
                    ['tag' => $collection->getTable('mageplaza_blog_post_tag')],
                    'main_table.post_id=tag.post_id AND tag.tag_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_TOPIC:
                $collection->join(
                    ['topic' => $collection->getTable('mageplaza_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.topic_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_MONTHLY:
                $collection->addFieldToFilter('publish_date', ['like' => $id . '%']);
                break;
        }

        return $collection;
    }

    /**
     * @param null $storeId
     *
     * @return PostCollection
     * @throws NoSuchEntityException
     */
    public function getPostList($storeId = null)
    {

        /** @var PostCollection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ['to' => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc');

            $collection->join(
                ['post_category' => $collection->getTable('mageplaza_blog_post_category')],
                'main_table.post_id=post_category.post_id ',
                ['category_id','post_id']
            );

            if($this->getParameters()) {
                $collection->addFieldToFilter('category_id', $this->getParameters());
            }

        return $collection;
    }

        /**
     * @param null $storeId
     *
     * @return PostCollection
     * @throws NoSuchEntityException
     */
    public function getPostListHomePage($data = null ,$storeId = null)
    {
        /** @var PostCollection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ['to' => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc');


        if($data) {
            $collection->join(
                ['topic' => $collection->getTable('mageplaza_blog_post_topic')],
                'main_table.post_id=topic.post_id AND topic.topic_id=' . $data,
                ['position']
            );
        }

        return $collection;
    }


         /**
     * @param null $storeId
     *
     * @return PostCollection
     * @throws NoSuchEntityException
     */
    public function getPostListHomePageById($id = null ,$storeId = null)
    {
        /** @var PostCollection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('main_table.post_id', ['eq' => $id])
            ->addFieldToFilter('publish_date', ['to' => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc');

        $collection->join(
                ['post_category' => $collection->getTable('mageplaza_blog_post_category')],
                'main_table.post_id=post_category.post_id ',
                ['category_id','post_id']
            );

        

        return $collection;
    }

     /**
     * @param null $storeId
     *
     * @return getPostListLast
     * @throws NoSuchEntityException
     */
    public function getPostListLast($storeId = null)
    {
        /** @var PostCollection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ['to' => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc')
            ->setPageSize(1) // only get 10 products 
            ->setCurPage(1);  // first page (means limit 0,10)

        // $collection->join(
        //         ['topic_center' => $collection->getTable('mageplaza_blog_post_topic')],
        //         'main_table.post_id=topic_center.post_id AND topic_center.topic_id=2' 
        // );

        return $collection;
    }




    public function getParameters() 
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_storeManager = $objectManager->get("\Magento\Store\Model\StoreManagerInterface");

        // Inintialize URL to the variable 
        $url = $this->_storeManager->getStore()->getCurrentUrl(false); 
            
        // Use parse_url() function to parse the URL  
        // and return an associative array which 
        // contains its various components 
        $url_components = parse_url($url); 
        
        // Use parse_str() function to parse the 
        // string passed via URL 

        if(isset($url_components['query'])) {
            parse_str($url_components['query'], $params); 

            if(isset($params['categoryId'])) {
                return $params['categoryId'];
            }
        }
        
        return 0;
        
    }


        /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getTree()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_storeManager = $objectManager->get("\Magento\Store\Model\StoreManagerInterface");

        $tree = $objectManager->create('Mageplaza\Blog\Block\Adminhtml\Category\Tree');
        $tree = $tree->getTree(null, $this->_storeManager->getStore()->getId());

        return $tree;
    }


}

	