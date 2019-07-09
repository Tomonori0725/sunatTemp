<?php

namespace Customize\Repository;

use Eccube\Doctrine\Query\OrderByClause;
use Eccube\Doctrine\Query\OrderByCustomizer;
use Eccube\Repository\QueryKey;

class AdminProductListCustomizer extends OrderByCustomizer
{
    /**
     * 常に商品IDでソートする。
     *
     * @param array $params
     * @param $queryKey
     * @return OrderByClause[]
     */
    protected function createStatements($params, $queryKey)
    {
        return [new OrderByClause('p.update_date')];
    }

    /**
     * ProductRepository::getQueryBuilderBySearchDataForAdmin に適用する.
     *
     * @return string
     * @see \Eccube\Repository\ProductRepository::getQueryBuilderBySearchDataForAdmin()
     * @see QueryKey
     */
    public function getQueryKey()
    {
        return QueryKey::PRODUCT_SEARCH_ADMIN;
    }
}
