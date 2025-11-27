<?php
namespace Kos\CustomBssQuote\Plugin\Quote;

/**
 * Class Index
 *
 * @package Kos\CustomBssQuote\Plugin\Quote
 */
class Index
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Index constructor.
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
        $this->request->setParams(['quote_extension' => '1']);
    }
}
