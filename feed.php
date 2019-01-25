<?php
    /**
    * RSS2 Smartnews Feed Template for displaying RSS2 Posts feed.
    *
    * @package Feed Smartnews
    */
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    $more = 1;

    echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';

    /**
    * Fires between the xml and rss tags in a feed.
    *
    * @since 4.0.0
    *
    * @param string $context Type of feed. Possible values include 'rss2', 'rss2-comments',
    *                        'rdf', 'atom', and 'atom-comments'.
    */
    do_action('rss_tag_pre', 'rss2');
    ?>
    <rss version="2.0"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:snf="http://www.smartnews.be/snf"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
     <?php
     /**
      * Fires at the end of the RSS root to add namespaces.
      *
      * @since 2.0.0
      */
     do_action('rss2_ns');
     ?>
     >

        <channel>
            <title><?php wp_title_rss(); ?></title>
            <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
            <link><?php bloginfo_rss('url') ?></link>
            <description><?php bloginfo_rss("description") ?></description>
            <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
            <language><?php bloginfo_rss('language'); ?></language>
            <sy:updatePeriod><?php
                $duration = 'hourly';

                /**
                 * Filters how often to update the RSS feed.
                 *
                 * @since 2.1.0
                 *
                 * @param string $duration The update period. Accepts 'hourly', 'daily', 'weekly', 'monthly',
                 *                         'yearly'. Default 'hourly'.
                 */
                $duration = apply_filters('rss_update_period', $duration);
                $frequency = '1';

                /**
                 * Filters the RSS update frequency.
                 *
                 * @since 2.1.0
                 *
                 * @param string $frequency An integer passed as a string representing the frequency
                 *                          of RSS updates within the update period. Default '1'.
                 */
                $frequency = apply_filters('rss_update_frequency', $frequency);
                $minutes = 60;
                if ($duration == 'minutes') {
                    $minutes = $frequency;
                    $frequency = ceil($minutes / 60);
                    $duration = 'hourly';
                } elseif ($duration == 'daily') {
                    $minutes = $frequency * 60 * 24;
                } elseif ($duration == 'weekly') {
                    $minutes = $frequency * 60 * 24 * 7;
                } elseif ($duration == 'monthly') {
                    $minutes = $frequency * 60 * 24 * 30;
                } elseif ($duration == 'yearly') {
                    $minutes = $frequency * 60 * 24 * 365;
                } else {
                    $minutes = $frequency * 60; // hourly
                }
                echo $duration;
                ?></sy:updatePeriod>
            <sy:updateFrequency><?php
                echo $frequency;
                ?></sy:updateFrequency>
            <copyright><?php
                $snf_copyright = get_option('snf_copyright');
                if ($snf_copyright === false) {
                    $snf_copyright = "not defined";
                }
                echo $snf_copyright;
                ?></copyright>
            <ttl><?php
                echo $minutes;
                ?></ttl>
            <image>
            <url><?php
                $snf_logo_url = get_option('snf_logo_url');
                if ($snf_logo_url === false) {
                    $snf_logo_url = "http://not-defined/";
                }
                echo $snf_logo_url;
                ?></url>
            <title><?php wp_title_rss(); ?></title>
            <link><?php bloginfo_rss('url') ?></link>
            </image>
            <?php
            /**
             * Fires at the end of the RSS2 Feed Header.
             *
             * @since 2.0.0
             */
            do_action('rss2_head');

            while (have_posts()) : the_post();
                ?>
                <item>
                    <title><?php the_title_rss() ?></title>
                    <link><?php the_permalink_rss() ?></link>
                    <?php if (get_comments_number() || comments_open()) : ?>
                        <comments><?php comments_link_feed(); ?></comments>
                    <?php endif; ?>
                    <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
                    <?php
                    $thumb_id = get_post_thumbnail_id();
                    $thumb_url = wp_get_attachment_image_src($thumb_id, 'full');
                    if ($thumb_url != ''):
                        ?><media:thumbnail url="<?php echo $thumb_url[0] ?>" />
                        <?php endif; ?>
                    <dc:creator><![CDATA[<?php the_author() ?>]]></dc:creator>
                    <?php the_category_rss('rss2') ?>

                    <guid isPermaLink="false"><?php the_guid(); ?></guid>
                    <?php if (get_option('rss_use_excerpt')) : ?>
                        <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
                    <?php else : ?>
                        <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
                        <?php $content = get_the_content_feed('rss2'); ?>
                        <content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
                    <?php endif; ?>
                    <?php if (get_comments_number() || comments_open()) : ?>
                        <wfw:commentRss><?php echo esc_url(get_post_comments_feed_link(null, 'rss2')); ?></wfw:commentRss>
                        <slash:comments><?php echo get_comments_number(); ?></slash:comments>
                    <?php endif; ?>
                    <?php rss_enclosure(); ?>
                    <?php
                    /**
                     * Fires at the end of each RSS2 feed item.
                     *
                     * @since 2.0.0
                     */
                    do_action('rss2_item');
                    ?>
                </item>
            <?php endwhile; ?>
        </channel>
    </rss>
