<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Block\Adminhtml\Config\System\Config;

use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;

/**
 * System configuration element class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Info extends \Magento\Config\Block\System\Config\Form\Field
{
    const MODULE_NAMESPACE = 'Magetrend_PdfTemplates';

    const CONFIG_NAMESPACE = 'pdftemplates';

    const XML_PATH_GENERAL = 'pdftemplates/general/is_active';

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_renderValue($element);
        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    //@codingStandardsIgnoreLine
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td></td><td class="value" colspan="3">';
        $html .= $this->_getElementHtml($element);
        $html .= '</td>';
        return $html;
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    //@codingStandardsIgnoreLine
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        parent::_getElementHtml($element);
        $content = $this->_scopeConfig->getValue(
            Info::CONFIG_NAMESPACE.base64_decode('L2xpY2Vuc2UvaW5mbw=='),
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $html = '';
        if (!empty($content)) {
            $html = $content;
            if (substr_count($html, 'script') > 0 || substr_count($html, 'iframe') > 0) {
                $html = '';
            }
        }
        return $html;
    }
}
