<?php

namespace Kos\BlogCustom\Block\Mageplaza\Blog;


class Listpost extends \Mageplaza\Blog\Block\Listpost
{

    public function getBlogLast() {
        return $this->helperData->getPostListLast($this->store->getStore()->getId());
    }

}

	