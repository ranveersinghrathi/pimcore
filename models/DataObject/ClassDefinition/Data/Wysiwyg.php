<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Model\DataObject\ClassDefinition\Data;

use Pimcore\Model;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\Element;
use Pimcore\Tool\Text;
use Symfony\Component\DomCrawler\Crawler;

class Wysiwyg extends Data implements ResourcePersistenceAwareInterface, QueryResourcePersistenceAwareInterface, TypeDeclarationSupportInterface, EqualComparisonInterface
{
    use DataObject\Traits\SimpleComparisonTrait;
    use Model\DataObject\ClassDefinition\Data\Extension\Text;
    use Extension\ColumnType;
    use Extension\QueryColumnType;

    /**
     * Static type of this element
     *
     * @var string
     */
    public $fieldtype = 'wysiwyg';

    /**
     * @var string|int
     */
    public $width = 0;

    /**
     * @var string|int
     */
    public $height = 0;

    /**
     * Type for the column to query
     *
     * @var string
     */
    public $queryColumnType = 'longtext';

    /**
     * Type for the column
     *
     * @var string
     */
    public $columnType = 'longtext';

    /**
     * @var string
     */
    public $toolbarConfig = '';

    /**
     * @var bool
     */
    public $excludeFromSearchIndex = false;

    /**
     * @return string|int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return string|int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string|int $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        if (is_numeric($width)) {
            $width = (int)$width;
        }
        $this->width = $width;

        return $this;
    }

    /**
     * @param string|int $height
     *
     * @return $this
     */
    public function setHeight($height)
    {
        if (is_numeric($height)) {
            $height = (int)$height;
        }
        $this->height = $height;

        return $this;
    }

    /**
     * @param string $toolbarConfig
     */
    public function setToolbarConfig($toolbarConfig)
    {
        if (is_string($toolbarConfig)) {
            $this->toolbarConfig = $toolbarConfig;
        } else {
            $this->toolbarConfig = '';
        }
    }

    /**
     * @return string
     */
    public function getToolbarConfig()
    {
        return $this->toolbarConfig;
    }

    /**
     * @return bool
     */
    public function isExcludeFromSearchIndex(): bool
    {
        return $this->excludeFromSearchIndex;
    }

    /**
     * @param bool $excludeFromSearchIndex
     *
     * @return self
     */
    public function setExcludeFromSearchIndex(bool $excludeFromSearchIndex)
    {
        $this->excludeFromSearchIndex = $excludeFromSearchIndex;

        return $this;
    }

    /**
     * @see ResourcePersistenceAwareInterface::getDataForResource
     *
     * @param string $data
     * @param null|DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataForResource($data, $object = null, $params = [])
    {
        return Text::wysiwygText($data);
    }

    /**
     * @see ResourcePersistenceAwareInterface::getDataFromResource
     *
     * @param string $data
     * @param null|DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataFromResource($data, $object = null, $params = [])
    {
        return Text::wysiwygText($data);
    }

    /**
     * @see QueryResourcePersistenceAwareInterface::getDataForQueryResource
     *
     * @param string $data
     * @param null|DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataForQueryResource($data, $object = null, $params = [])
    {
        $data = $this->getDataForResource($data, $object, $params);

        $data = strip_tags($data, '<a><img>');
        $data = str_replace("\r\n", ' ', $data);
        $data = str_replace("\n", ' ', $data);
        $data = str_replace("\r", ' ', $data);
        $data = str_replace("\t", '', $data);
        $data = preg_replace('#[ ]+#', ' ', $data);

        return $data;
    }

    /**
     * @see Model\DataObject\ClassDefinition\Data::getDataForSearchIndex
     *
     * @param DataObject\Concrete|DataObject\Objectbrick\Data\AbstractData|DataObject\Fieldcollection\Data\AbstractData $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataForSearchIndex($object, $params = [])
    {
        if ($this->isExcludeFromSearchIndex()) {
            return '';
        } else {
            return parent::getDataForSearchIndex($object, $params);
        }
    }

    /**
     * @see Data::getDataForEditmode
     *
     * @param string $data
     * @param null|DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataForEditmode($data, $object = null, $params = [])
    {
        return $this->getDataForResource($data, $object, $params);
    }

    /**
     * @see Data::getDataFromEditmode
     *
     * @param string $data
     * @param null|DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        return $data;
    }

    /**
     * @param string|null $data
     *
     * @return array
     */
    public function resolveDependencies($data)
    {
        return Text::getDependenciesOfWysiwygText($data);
    }

    /**
     * This is a dummy and is mostly implemented by relation types
     *
     * @param mixed $data
     * @param array $tags
     *
     * @return array
     */
    public function getCacheTags($data, $tags = [])
    {
        return Text::getCacheTagsOfWysiwygText($data, $tags);
    }

    /**
     * Checks if data is valid for current data field
     *
     * @param mixed $data
     * @param bool $omitMandatoryCheck
     *
     * @throws \Exception
     */
    public function checkValidity($data, $omitMandatoryCheck = false)
    {
        if (!$omitMandatoryCheck and $this->getMandatory() and empty($data)) {
            throw new Element\ValidationException('Empty mandatory field [ '.$this->getName().' ]');
        }
        $dependencies = Text::getDependenciesOfWysiwygText($data);
        if (is_array($dependencies)) {
            foreach ($dependencies as $key => $value) {
                $el = Element\Service::getElementById($value['type'], $value['id']);
                if (!$el) {
                    throw new Element\ValidationException('Invalid dependency in wysiwyg text');
                }
            }
        }
    }

    /**
     * @param DataObject\Concrete|DataObject\Localizedfield|DataObject\Objectbrick\Data\AbstractData|DataObject\Fieldcollection\Data\AbstractData  $object
     * @param array $params
     *
     * @return string
     */
    public function preGetData($object, $params = [])
    {
        $data = '';
        if ($object instanceof DataObject\Concrete) {
            $data = $object->getObjectVar($this->getName());
        } elseif ($object instanceof DataObject\Localizedfield || $object instanceof DataObject\Classificationstore) {
            $data = $params['data'];
        } elseif ($object instanceof DataObject\Fieldcollection\Data\AbstractData) {
            $data = $object->getObjectVar($this->getName());
        } elseif ($object instanceof DataObject\Objectbrick\Data\AbstractData) {
            $data = $object->getObjectVar($this->getName());
        }

        return Text::wysiwygText($data, [
                'object' => $object,
                'context' => $this,
            ]);
    }

    /** Generates a pretty version preview (similar to getVersionPreview) can be either html or
     * a image URL. See the https://github.com/pimcore/object-merger bundle documentation for details
     *
     * @param string|null $data
     * @param DataObject\Concrete|null $object
     * @param mixed $params
     *
     * @return array|string
     */
    public function getDiffVersionPreview($data, $object = null, $params = [])
    {
        if ($data) {
            $value = [];
            $value['html'] = $data;
            $value['type'] = 'html';

            return $value;
        } else {
            return '';
        }
    }

    /**
     * Rewrites id from source to target, $idMapping contains
     * array(
     *  "document" => array(
     *      SOURCE_ID => TARGET_ID,
     *      SOURCE_ID => TARGET_ID
     *  ),
     *  "object" => array(...),
     *  "asset" => array(...)
     * )
     *
     * @param mixed $object
     * @param array $idMapping
     * @param array $params
     *
     * @return mixed
     */
    public function rewriteIds($object, $idMapping, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);
        $html = new Crawler($data);
        if ($html) {
            $es = $html->filter('body > a[pimcore_id], img[pimcore_id]');

            /** @var \DOMElement $el */
            foreach ($es as $el) {
                if ($el->hasAttribute('href') || $el->hasAttribute('src')) {
                    $type = $el->getAttribute('pimcore_type');
                    $id = (int) $el->getAttribute('pimcore_id');

                    if ($idMapping[$type][$id] ?? false) {
                        $el->setAttribute('pimcore_id', strtr($el->getAttribute('pimcore_id'), $idMapping[$type]));
                    }
                }
            }

            $data = $html->filter('body')->html();

            $html->clear();
            unset($html);
        }

        return $data;
    }

    public function isFilterable(): bool
    {
        return true;
    }

    public function getParameterTypeDeclaration(): ?string
    {
        return '?string';
    }

    public function getReturnTypeDeclaration(): ?string
    {
        return '?string';
    }

    public function getPhpdocInputType(): ?string
    {
        return 'string|null';
    }

    public function getPhpdocReturnType(): ?string
    {
        return 'string|null';
    }
}
