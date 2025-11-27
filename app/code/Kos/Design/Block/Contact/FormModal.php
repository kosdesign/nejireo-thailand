<?php
namespace Kos\Design\Block\Contact;
class FormModal extends \Magento\Framework\View\Element\Template
{
    public function getContent() : string
    {
        $master = $this->getLayout()->createBlock('Magento\Contact\Block\ContactForm')->setTemplate('Magento_Contact::form.phtml');
        $html = $master->toHtml();
        return '<div class="page-contact-content">
            <div class="page-content">
                <div class="container">
                    '.$html.'
                </div>
            </div>
        </div>';
    }
}