<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\ElasticsearchMinimumTerms\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface as TypeResolver;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as BaseMatch;
use Magento\Elasticsearch\Model\Config;

/**
 * Builder for match query.
 */
class Match extends BaseMatch
{
    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var AttributeProvider
     */
    private $attributeProvider;

    /**
     * @var TypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var ValueTransformerPool
     */
    private $valueTransformerPool;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param PreprocessorInterface[] $preprocessorContainer
     * @param AttributeProvider|null $attributeProvider
     * @param TypeResolver|null $fieldTypeResolver
     * @param ValueTransformerPool|null $valueTransformerPool
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        array $preprocessorContainer,
        AttributeProvider $attributeProvider = null,
        TypeResolver $fieldTypeResolver = null,
        ValueTransformerPool $valueTransformerPool = null,
        Config $config
    ) {
        parent::__construct(
            $fieldMapper,
            $preprocessorContainer,
            $attributeProvider,
            $fieldTypeResolver,
            $valueTransformerPool
        );
        $this->fieldMapper = $fieldMapper;
        $this->attributeProvider = $attributeProvider;
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->valueTransformerPool = $valueTransformerPool;
        $this->config = $config;
    }

    public function build(array $selectQuery, RequestQueryInterface $requestQuery, $conditionType)
    {
        $queryValue = $this->prepareQuery($requestQuery->getValue(), $conditionType);
        $queries = $this->buildQueries($requestQuery->getMatches(), $queryValue);
        $requestQueryBoost = $requestQuery->getBoost() ?: 1;
        $minimumShouldMatch = $this->config->getElasticsearchConfigData('minimum_should_match');
        foreach ($queries as $query) {
            $queryBody = $query['body'];
            $matchKey = isset($queryBody['match_phrase']) ? 'match_phrase' : 'match';
            foreach ($queryBody[$matchKey] as $field => $matchQuery) {
                $matchQuery['boost'] = $requestQueryBoost + $matchQuery['boost'];
                if ($minimumShouldMatch && $matchKey != 'match_phrase') {
                    $matchQuery['minimum_should_match'] = $minimumShouldMatch;
                }
                $queryBody[$matchKey][$field] = $matchQuery;
            }
            $selectQuery['bool'][$query['condition']][] = $queryBody;
        }

        return $selectQuery;
    }
}
