<?php

namespace Klevu\Addtocart\Block;

use Klevu\Addtocart\Helper\Data as Klevu_AddtocartHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Index extends Template
{
    /**
     * @var Klevu_AddtocartHelper
     */
    protected $_addtocartHelperData;

    /**
     * @param Context $context
     * @param Klevu_AddtocartHelper $addtocartHelperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Klevu_AddtocartHelper $addtocartHelperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_addtocartHelperData = $addtocartHelperData;
    }

    /**
     * Check whether the add to cart is enabled for Klevu Template
     *
     * @return bool
     */
    public function isAddtocartEnabled()
    {
        return $this->_addtocartHelperData->isAddtocartEnabled();
    }
}
