<?php
namespace Kos\CustomBssQuote\Plugin\Quote;

/**
 * Class Add
 *
 * @package Kos\CustomBssQuote\Plugin\Quote
 */
class Add
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param $subject
     */
    public function beforeExecute($subject)
    {
        $this->request->setParams(['quoteextension' => '1']);
    }
}
