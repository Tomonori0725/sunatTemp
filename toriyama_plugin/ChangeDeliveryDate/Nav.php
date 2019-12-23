<?php

namespace Plugin\ChangeDeliveryDate;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        return [
            'product' => [
                'children' => [
                    'delivery_date' => [
                        'name' => 'お届け日一括登録',
                        'url' => 'delivery_date_list',
                    ],
                ],
            ],
        ];
    }
}
