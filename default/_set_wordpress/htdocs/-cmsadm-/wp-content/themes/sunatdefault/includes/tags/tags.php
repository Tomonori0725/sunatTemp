<?php

function getConst($id)
{
    $themeDataList = ThemeDataList::getInstance();
    return $themeDataList->getConst($id);
}

function getList($type)
{
    $themeDataList = ThemeDataList::getInstance();
    return $themeDataList->getList($type);
}

function getData($type, $id)
{
    $themeDataList = ThemeDataList::getInstance();
    return $themeDataList->getData($type, $id);
}

function getMetaData($post_id, $isDefault = true)
{
    $themeMetabox = ThemeMetabox::getInstance();
    $arrMeta = array();
    $themeMetabox->getMetaData($post_id, $arrMeta, $isDefault);
    return $arrMeta;
}

function getTheField($name)
{
    global $post;
    if (!property_exists($post, 'metaFields')
        || !array_key_exists($name, $post->metaFields)
    ) {
        return null;
    }
    return $post->metaFields[$name]['value'];
}

function theField($name)
{
    echo getTheField($name);
}

function getDescription()
{
    $themePostType = ThemePostType::getInstance();
    return $themePostType->getDescription();
}

function getUrl($type)
{
    $themeRefactoring = ThemeRefactoring::getInstance();
    return $themeRefactoring->getUrl($type);
}

function getPath($type)
{
    $themeRefactoring = ThemeRefactoring::getInstance();
    return $themeRefactoring->getPath($type);
}

function getPostType()
{
    $themeRefactoring = ThemeRefactoring::getInstance();
    return $themeRefactoring->getPostType();
}

function getEyecatchUrl()
{
    $themeRefactoring = ThemeRefactoring::getInstance();
    return $themeRefactoring->getEyecatchUrl();
}

function getTheImage($imageIdList, $options = array())
{
    $ret = array();
    if (false === $imageIdList
        || '' === $imageIdList
        || (is_array($imageIdList) && empty($imageIdList))
    ) {
        return $ret;
    }
    if (!is_array($imageIdList)) {
        $imageIdList = array($imageIdList);
    }
    $options = array_merge(array(
        'before' => '',
        'after'  => '',
        'size'   => 'full',
        'width'  => true,
        'height' => true,
        'alt'    => '',
        'count'  => count($imageIdList)
    ), $options);
    if (count($imageIdList) < $options['count']) {
        $options['count'] = count($imageIdList);
    }
    if (0 === $options['count']) {
        return $ret;
    }

    for ($i = 0; $i < $options['count']; $i++) {
        $imageId = $imageIdList[$i];

        $arrImage = wp_get_attachment_image_src($imageId, $options['size']);
        if ($arrImage && $arrImage[0]) {
            foreach (array('before', 'after') as $key) {
                ${$key} = str_replace(array(
                    '%url%'
                ), array(
                    esc_attr($arrImage[0])
                ), $options[$key]);
            }

            $html = $before;
            $html .= '<img src="' . esc_attr($arrImage[0]) . '"';
            if (false !== $options['width']) {
                $width = $options['width'];
                if (true === $width) {
                    $width = $arrImage[1];
                }
                $html .= ' width="' . esc_attr($width) . '"';
            }
            if (false !== $options['height']) {
                $height = $options['height'];
                if (true === $height) {
                    $height = $arrImage[2];
                }
                $html .= ' height="' . esc_attr($height) . '"';
            }
            $html .= ' alt="' . esc_attr($options['alt']) . '">';
            $html .= $after;
        }
        $ret[] = $html;
    }
    return $ret;
}

function theImage($imageIdList, $options = array())
{
    $imageList = getTheImage($imageIdList, $options);
    foreach ($imageList as $image) {
        echo $image;
    }
}

function uploadImageTemplate($options = array())
{
    $options = array_merge(array(
        'imageName' => '',
        'textName'  => '',
        'imageId'   => array(),
        'text'      => array(),
        'multi'     => true,
        'sortable'  => true,
        'size'      => 'medium',
        'tag'       => 'div',
        'button'    => '追加する'
    ), $options);
    echo '<' . esc_html($options['tag']) . ' class="uploadImageGroup" data-upload-multi="' . var_export($options['multi'], true) . '">';
    ?>
        <ul class="uploadImageList" data-upload-name="<?php echo esc_attr($options['imageName']); ?>" data-upload-text="<?php echo esc_attr($options['textName']); ?>" data-upload-sortable="<?php var_export($options['sortable']); ?>" data-upload-size="<?php echo esc_attr($options['size']); ?>">
            <?php
            if ($options['imageId']) {
                if ($options['multi']) {
                    $nameArray = '[]';
                } else {
                    $nameArray = '';
                }
                if (!is_array($options['imageId'])) {
                    $options['imageId'] = array($options['imageId']);
                }
                foreach ($options['imageId'] as $key => $imageId) {
                    $srcList = wp_get_attachment_image_src($imageId, $options['size']);
                    if ($srcList && $srcList[0]) {
                        ?>
                        <li class="image" id="image_<?php echo esc_attr($imageId); ?>">
                            <div class="imageWrap">
                                <a class="removeImageButton dashicons dashicons-dismiss"></a>
                                <div><img src="<?php echo esc_attr($srcList[0]); ?>" width="<?php echo esc_attr($srcList[1]); ?>" height="<?php echo esc_attr($srcList[2]); ?>" class="sortHandle"></div>
                                <input type="hidden" name="<?php echo esc_attr($options['imageName'] . $nameArray); ?>" value="<?php echo esc_attr($imageId); ?>">
                                <?php if (strlen($options['textName'])) : ?>
                                    <input type="text" class="widefat" name="<?php echo esc_attr($options['textName'] . $nameArray); ?>" value="<?php echo esc_attr($options['text'][$key]); ?>">
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php
                    }
                }
            } else {
                echo '<li class="noImage">登録がありません。</li>';
            }
            ?>
        </ul>
        <a class="uploadImageButton button"><?php echo esc_html($options['button']); ?></a>
    <?php
    echo '</' . esc_html($options['tag']) . '>';
}

function getRecentPost($postType, $day)
{
    $theQuery = new WP_Query(array(
        'post_type'           => $postType,
        'posts_per_page'      => -1,
        'no_found_rows'       => true,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'date_query' => array(
            array(
                'after'     => date_i18n('Y/m/d', strtotime('-' . $day . ' day')),
                'inclusive' => true
            )
        )
    ));
    return $theQuery;
}

function isRecentPost($postType, $day)
{
    $theQuery = getRecentPost($postType, $day);;
    return $theQuery->have_posts();
}

function recentAuthor($postType, $day, $meta, $isNewest = false, $display = true, $delimiter = ' ')
{
    $theQuery = getRecentPost($postType, $day);;
    $author = array();
    if ($theQuery->have_posts()) {
        while ($theQuery->have_posts()) {
            $theQuery->the_post();
            $author[] = get_the_author_meta($meta);
        }
        wp_reset_postdata();
    } else {
        if ($isNewest) {
            $theQuery = new WP_Query(array(
                'post_type'           => getPostType(),
                'posts_per_page'      => 1,
                'no_found_rows'       => true,
                'post_status'         => 'publish',
                'ignore_sticky_posts' => true
            ));
            if ($theQuery->have_posts()) {
                while ($theQuery->have_posts()) {
                    $theQuery->the_post();
                    $author[] = get_the_author_meta($meta);
                }
                wp_reset_postdata();
            }
        }
    }
    $author = array_values(array_unique($author));
    if (!$display) {
        return $author;
    }

    $author = implode($delimiter, $author);
    echo $author;
}

function topicpath($params = array())
{
    $defaults = array(
        'home'      => 'ホーム',
        'posttype'  => true,
        'search'    => '検索結果',
        'taxonomy'  => array('category'),
        'delimiter' => ''
    );
    $params = wp_parse_args($params, $defaults);

    $themeRefactoring = ThemeRefactoring::getInstance();
    $topicpathList = $themeRefactoring->getTopicpath($params);
    foreach ($topicpathList as $topicpath) {
        $length = count($topicpath);
        if ($length) {
            echo '<ol>';
            $no = 0;
            foreach ($topicpath as $path) {
                echo '<li>';
                if (array_key_exists('link', $path) && $path['link']) {
                    echo '<a href="' . esc_attr($path['link']) . '">';
                }
                echo esc_html($path['label']);
                if (array_key_exists('link', $path) && $path['link']) {
                    echo '</a>';
                }
                echo '</li>';

                $no++;
                if ($no !== $length && strlen($params['delimiter'])) {
                    echo esc_html($params['delimiter']);
                }
            }
            echo '</ol>';
        }
    }
}

function pagination($options = array())
{
    $options = array_merge(array(
        'range'    => 4,
        'prev'     => '前へ',
        'next'     => '次へ',
        'first'    => '最初へ',
        'last'     => '最後へ',
        'current' => '%page% / %total%'
    ), $options);

    $themeRefactoring = ThemeRefactoring::getInstance();
    $pagination = $themeRefactoring->pagination($options['range']);

    if (!empty($pagination)) {
        echo '<ul>';
        if ($options['current']) {
            $current = str_replace(array(
                '%page%',
                '%total%'
            ), array(
                $pagination['now'],
                $pagination['page_num']
            ), $options['current']);
            echo '<li class="current"><span>' . $current . '</span></li>';
        }
        if ($options['first']) {
            if (1 !== $pagination['now']){
                echo '<li class="first"><a href="' . $pagination['first'] . '">' . $options['first'] . '</a></li>';
            } else {
                echo '<li class="first disabled"><span>' . $options['first'] . '</span></li>';
            }
        }
        if ($pagination['prev']) {
            echo '<li class="prev"><a href="' . $pagination['prev'] . '">' . $options['prev'] . '</a></li>';
        } else {
            echo '<li class="prev disabled"><span>' . $options['prev'] . '</span></li>';
        }

        foreach ($pagination['page'] as $key => $url) {
            if ($key !== $pagination['now']) {
                echo '<li class="page"><a href="' . $url . '">' . $key . '</a></li>';
            } else {
                echo '<li class="page active"><span>' . $key . '</span></li>';
            }
        }

        if ($pagination['next']) {
            echo '<li class="next"><a href="' . $pagination['next'] . '">' . $options['next'] . '</a></li>';
        } else {
            echo '<li class="next disabled"><span>' . $options['next'] . '</span></li>';
        }
        if ($options['last']) {
            if ($pagination['page_num'] !== $pagination['now']){
                echo '<li class="last"><a href="' . $pagination['last'] . '">' . $options['last'] . '</a></li>';
            } else {
                echo '<li class="last disabled"><span>' . $options['last'] . '</span></li>';
            }
        }
        echo '</ul>';
    }

}
