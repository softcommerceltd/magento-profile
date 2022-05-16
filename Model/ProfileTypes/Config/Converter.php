<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Model\ProfileTypes\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * @inheritDoc
 */
class Converter implements ConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert($source)
    {
        $output = [];
        $xpath = new \DOMXPath($source);
        $types = $xpath->evaluate('/config/profile');

        /** @var $typeNode \DOMNode */
        foreach ($types as $typeNode) {
            $typeId = $this->getNodeValue($typeNode, 'typeId');
            $data = [];
            $data['type_id'] = $typeId;
            $data['label'] = $this->getNodeValue($typeNode, 'label', '');
            $data['instance'] = $this->getNodeValue($typeNode, 'instance');
            $data['router'] = $this->getNodeValue($typeNode, 'router');
            $data['queue_router'] = $this->getNodeValue($typeNode, 'queueRouter');
            $data['crontab_group'] = $this->getNodeValue($typeNode, 'crontabGroup');
            $data['crontab_instance'] = $this->getNodeValue($typeNode, 'crontabInstance');
            $output['types'][$typeId] = $data;
        }

        return $output;
    }

    /**
     * @param \DOMNode $input
     * @param string $attributeName
     * @param string|null $default
     * @return null|string
     */
    private function getNodeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }
}
