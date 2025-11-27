<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

class TemplateVarManager extends \Magento\Framework\DataObject
{
    private $vars = [];

    private $isCollected = false;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    public $eventManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    public $dataObjectFactory;

    public function __construct(
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    public function reset()
    {
        $this->vars = [];
        $this->setData([]);
    }

    public function setVariables($vars)
    {
        $this->vars = $vars;

        return $this;
    }

    public function getData($key = '', $index = null)
    {
        if ($this->isCollected === false) {
            $this->collect();
            $this->isCollected = true;
        }

        return parent::getData($key, $index);
    }

    public function collect()
    {
        $this->eventManager->dispatch('magetrend_email_collect_additional_vars', [
            'vars' => $this->vars,
            'additional_vars' => $this,
        ]);
    }
}
