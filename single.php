<?php
/**
 * The template for displaying single blog posts
 */
get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<!-- Blog Details Hero Section -->
<section class="blog-details-hero">
    <div class="container">
        <div class="blog-breadcrumb">
            <a href="<?php echo home_url(); ?>" class="breadcrumb-link">Home</a>
            <span class="breadcrumb-sep">/</span>
            <a href="<?php echo home_url('/blog'); ?>" class="breadcrumb-link">Blogs</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current"><?php the_title(); ?></span>
        </div>
    </div>
</section>

<!-- Featured Image -->
<section class="blog-featured-image">
    <div class="image-container">
        <div class="featured-image-placeholder">
            <?php 
            if (has_post_thumbnail()) {
                the_post_thumbnail('large', array('style' => 'width:100%;height:400px;object-fit:cover;'));
            } else {
                echo '<div style="background: linear-gradient(135deg, #667eea, #764ba2); width: 100%; height: 400px; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">💻</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Blog Content Section -->
<section class="blog-content-wrapper section-padding">
    <div class="container">
        <div class="blog-main">
            <!-- Main Content -->
            <article class="blog-article glass-premium">
                <div class="blog-headers">
                    <?php 
                    $categories = get_the_category();
                    $category = !empty($categories) ? $categories[0]->name : 'Blog';
                    $reading_time = ceil(str_word_count(get_the_content()) / 200);
                    $author_id = get_the_author_meta('ID');
                    $author_name = get_the_author_meta('display_name');
                    ?>
                    <span class="blog-category tutorials"><?php echo esc_html($category); ?></span>
                    <h1 class="blog-single-title"><?php the_title(); ?></h1>
                    
                    <div class="blog-meta-details">
                        <div class="meta-left">
                            <div class="author-bio">
                                <div class="author-avatar" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">👨‍💼</div>
                                <div>
                                    <p class="author-name"><?php echo esc_html($author_name); ?></p>
                                    <p class="author-role">Tech Writer</p>
                                </div>
                            </div>
                        </div>
                        <div class="meta-right">
                            <span class="meta-item">
                                <span class="meta-icon">📅</span>
                                <span><?php echo get_the_date('F j, Y'); ?></span>
                            </span>
                            <span class="meta-item">
                                <span class="meta-icon">⏱️</span>
                                <span><?php echo esc_html($reading_time); ?> min read</span>
                            </span>
                            <span class="meta-item">
                                <span class="meta-icon">👁️</span>
                                <span><?php echo get_post_meta(get_the_ID(), '_post_views_count', true) ?: '0'; ?> views</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Article Body -->
                <div class="article-body">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <div class="blog-tags">
                    <?php 
                    $tags = get_the_tags();
                    if ($tags) {
                        foreach ($tags as $tag) {
                            echo '<span class="tag">#' . esc_html($tag->name) . '</span>';
                        }
                    }
                    ?>
                </div>

                <!-- Share Buttons -->
                <div class="share-section glass">
                    <h3>Share This Article</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" class="share-btn facebook" title="Share on Facebook" target="_blank">f</a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" class="share-btn twitter" title="Share on Twitter" target="_blank">𝕏</a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" class="share-btn linkedin" title="Share on LinkedIn" target="_blank">in</a>
                        <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" class="share-btn whatsapp" title="Share on WhatsApp" target="_blank">💬</a>
                        <a href="#" class="share-btn copy" title="Copy Link" onclick="copyLink(event)">🔗</a>
                    </div>
                </div>

                <!-- Author Box -->
                <div class="author-box glass-premium">
                    <div class="author-avatar-large" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">👨‍💼</div>
                    <div class="author-info">
                        <h3><?php echo esc_html($author_name); ?></h3>
                        <p class="author-title">Tech Writer & Developer</p>
                        <p class="author-bio-text"><?php echo get_the_author_meta('description'); ?></p>
                        <div class="author-social">
                            <?php 
                            $twitter = get_the_author_meta('twitter');
                            $github = get_the_author_meta('github');
                            $linkedin = get_the_author_meta('linkedin');
                            if ($twitter) echo '<a href="' . esc_url($twitter) . '" class="social-link" target="_blank">Twitter</a>';
                            if ($github) echo '<a href="' . esc_url($github) . '" class="social-link" target="_blank">GitHub</a>';
                            if ($linkedin) echo '<a href="' . esc_url($linkedin) . '" class="social-link" target="_blank">LinkedIn</a>';
                            ?>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Search Widget -->
                <div class="sidebar-widget glass-premium">
                    <h3>Search</h3>
                    <div class="sidebar-search">
                        <input type="text" placeholder="Search articles..." class="search-input">
                        <button class="search-btn">🔍</button>
                    </div>
                </div>

                <!-- Recent Posts Widget -->
                <div class="sidebar-widget glass-premium">
                    <h3>Recent Posts</h3>
                    <div class="recent-posts">
                        <?php 
                        $recent_posts = new WP_Query(array(
                            'posts_per_page' => 3,
                            'post_type' => 'post',
                            'post_status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'post__not_in' => array(get_the_ID())
                        ));
                        
                        if ($recent_posts->have_posts()) {
                            while ($recent_posts->have_posts()) {
                                $recent_posts->the_post();
                        ?>
                        <article class="recent-post-item">
                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                            <p class="post-date"><?php echo get_the_date('F j, Y'); ?></p>
                        </article>
                        <?php
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </div>
                </div>

                <!-- Categories Widget -->
                <div class="sidebar-widget glass-premium">
                    <h3>Categories</h3>
                    <ul class="category-list">
                        <?php 
                        $categories = get_categories(array(
                            'orderby' => 'count',
                            'order' => 'DESC'
                        ));
                        
                        foreach ($categories as $category) {
                            echo '<li><a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . ' <span class="count">(' . intval($category->count) . ')</span></a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <!-- Newsletter Widget -->
                <div class="sidebar-widget glass-premium newsletter-widget">
                    <h3>Newsletter</h3>
                    <p>Get weekly updates on latest articles and tutorials.</p>
                    <form class="newsletter-form-sidebar">
                        <input type="email" placeholder="Your email...">
                        <button type="submit" class="btn-subscribe">Subscribe</button>
                    </form>
                </div>

                <!-- Popular Tags Widget -->
                <div class="sidebar-widget glass-premium">
                    <h3>Popular Tags</h3>
                    <div class="tags-cloud">
                        <?php 
                        $tags = get_tags(array(
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 6
                        ));
                        
                        if ($tags) {
                            foreach ($tags as $tag) {
                                echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="tag-link">' . esc_html($tag->name) . '</a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Related Posts Section -->
<section class="related-posts-section section-padding gradient-bg">
    <div class="container">
        <h2 class="section-title">Related Articles</h2>
        <div class="related-posts-grid">
            <?php 
            $categories = get_the_category();
            $cat_ids = array();
            foreach ($categories as $cat) {
                $cat_ids[] = $cat->term_id;
            }
            
            $related_args = array(
                'category__in' => $cat_ids,
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            $related_query = new WP_Query($related_args);
            
            if ($related_query->have_posts()) {
                while ($related_query->have_posts()) {
                    $related_query->the_post();
                    $rel_categories = get_the_category();
                    $rel_category = !empty($rel_categories) ? strtolower(str_replace(' ', '-', $rel_categories[0]->name)) : 'tutorials';
            ?>
            <article class="related-post-card glass-premium">
                <div class="post-image">
                    <div class="image-placeholder" style="background: linear-gradient(135deg, #f093fb, #f5576c); height: 150px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem;">
                        <?php echo has_post_thumbnail() ? wp_get_attachment_image(get_post_thumbnail_id(), 'medium', false, array('style' => 'width:100%;height:100%;object-fit:cover;')) : '⚡'; ?>
                    </div>
                </div>
                <div class="post-card-content">
                    <span class="post-category <?php echo esc_attr($rel_category); ?>"><?php echo esc_html(!empty($rel_categories) ? $rel_categories[0]->name : 'Blog'); ?></span>
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                    <a href="<?php the_permalink(); ?>" class="read-link">Read More →</a>
                </div>
            </article>
            <?php
                }
                wp_reset_postdata();
            }
            ?>
        </div>
    </div>
</section>

<!-- Comments Section -->
<section class="comments-section section-padding">
    <div class="container">
        <div class="comments-wrapper glass-premium">
            <?php 
            $comment_count = get_comments_number();
            ?>
            <h2>Comments <span class="comment-count">(<?php echo intval($comment_count); ?>)</span></h2>

            <!-- Comments List -->
            <div class="comments-list">
                <?php 
                if (comments_open() || get_comments_number()) {
                    wp_list_comments();
                }
                ?>
            </div>

            <!-- Comment Form -->
            <?php comment_form(); ?>
        </div>
    </div>
</section>

<!-- Navigation Section -->
<section class="post-navigation section-padding">
    <div class="container">
        <div class="nav-posts">
            <?php 
            $prev_post = get_previous_post();
            if ($prev_post) {
            ?>
            <div class="nav-post prev-post glass-premium">
                <div class="nav-label">← Previous Post</div>
                <h4><a href="<?php echo get_permalink($prev_post->ID); ?>"><?php echo esc_html($prev_post->post_title); ?></a></h4>
                <p class="nav-date"><?php echo get_the_date('F j, Y', $prev_post->ID); ?></p>
            </div>
            <?php } ?>

            <?php 
            $next_post = get_next_post();
            if ($next_post) {
            ?>
            <div class="nav-post next-post glass-premium">
                <div class="nav-label">Next Post →</div>
                <h4><a href="<?php echo get_permalink($next_post->ID); ?>"><?php echo esc_html($next_post->post_title); ?></a></h4>
                <p class="nav-date"><?php echo get_the_date('F j, Y', $next_post->ID); ?></p>
            </div>
            <?php } ?>
        </div>
    </div>
</section>

<?php endwhile; 

get_footer(); ?>

<style>
/* Blog Details Page Styles */

/* Breadcrumb */
.blog-breadcrumb {
    padding: 20px 0;
    font-size: 0.95rem;
}

.breadcrumb-link {
    color: #00ffff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.breadcrumb-link:hover {
    color: #2563eb;
}

.breadcrumb-sep {
    color: #64748b;
    margin: 0 10px;
}

.breadcrumb-current {
    color: #cbd5e1;
}

/* Featured Image */
.blog-featured-image {
    padding: 40px 0;
}

.image-container {
    max-width: 900px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(37, 99, 235, 0.2);
}

/* Blog Content Wrapper */
.blog-content-wrapper {
    background: transparent;
}

.blog-main {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 40px;
}

/* Article Styling */
.blog-article {
    padding: 50px;
    border-radius: 12px;
}

.blog-headers {
    margin-bottom: 40px;
    border-bottom: 2px solid rgba(0, 255, 255, 0.2);
    padding-bottom: 30px;
}

.blog-single-title {
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 800;
    margin: 20px 0;
    line-height: 1.3;
}

.blog-meta-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 30px;
    margin-top: 25px;
}

.meta-left {
    flex: 1;
}

.author-bio {
    display: flex;
    gap: 15px;
    align-items: center;
}

.author-name {
    font-weight: 700;
    margin: 0;
}

.author-role {
    color: #00ffff;
    font-size: 0.9rem;
    margin: 5px 0 0;
}

.meta-right {
    display: flex;
    gap: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #cbd5e1;
    font-size: 0.95rem;
}

.meta-icon {
    font-size: 1.1rem;
}

/* Article Body */
.article-body {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #cbd5e1;
    margin: 40px 0;
}

.article-body h2 {
    font-size: 2rem;
    margin: 40px 0 20px;
    font-weight: 700;
}

.article-body h3 {
    font-size: 1.4rem;
    margin: 25px 0 15px;
    font-weight: 700;
}

.article-body p {
    margin-bottom: 20px;
}

.article-body ul,
.article-body ol {
    margin: 20px 0 20px 30px;
    padding-left: 20px;
}

.article-body li {
    margin-bottom: 10px;
}

.article-body code {
    background: rgba(37, 99, 235, 0.2);
    color: #00ffff;
    padding: 2px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.95em;
}

.code-block {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(37, 99, 235, 0.3);
    border-radius: 8px;
    padding: 20px;
    margin: 25px 0;
    overflow-x: auto;
}

.code-block pre {
    margin: 0;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    color: #e2e8f0;
}

.code-block code {
    background: none;
    color: #e2e8f0;
    padding: 0;
    display: block;
    white-space: pre;
}

/* Blog Tags */
.blog-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 40px 0;
    padding: 20px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.tag {
    padding: 8px 16px;
    background: rgba(37, 99, 235, 0.2);
    border: 1px solid rgba(37, 99, 235, 0.4);
    border-radius: 20px;
    color: #2563eb;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.tag:hover {
    background: linear-gradient(to right, #2563eb, #f59e0b);
    border-color: #2563eb;
    color: white;
}

/* Share Section */
.share-section {
    padding: 25px;
    border-radius: 8px;
    margin: 40px 0;
    text-align: center;
}

.share-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
}

.share-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.share-btn {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.share-btn.facebook {
    background: #3b5998;
}

.share-btn.twitter {
    background: #1DA1F2;
}

.share-btn.linkedin {
    background: #0A66C2;
}

.share-btn.whatsapp {
    background: #25D366;
}

.share-btn.copy {
    background: rgba(37, 99, 235, 0.4);
}

.share-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Author Box */
.author-box {
    padding: 30px;
    border-radius: 12px;
    display: flex;
    gap: 30px;
    align-items: flex-start;
    margin: 40px 0;
}

.author-info h3 {
    margin: 0 0 5px;
    font-size: 1.3rem;
}

.author-title {
    color: #00ffff;
    font-weight: 600;
    margin: 0 0 15px;
}

.author-bio-text {
    color: #cbd5e1;
    line-height: 1.6;
    margin: 0 0 15px;
}

.author-social {
    display: flex;
    gap: 15px;
}

.social-link {
    color: #00ffff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.social-link:hover {
    color: #2563eb;
}

/* Sidebar */
.blog-sidebar {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.sidebar-widget {
    padding: 25px;
    border-radius: 8px;
}

.sidebar-widget h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.1rem;
}

.sidebar-search {
    display: flex;
    gap: 8px;
}

.sidebar-search .search-input {
    flex: 1;
    padding: 10px 14px;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(0, 255, 255, 0.2);
    border-radius: 6px;
    color: #e2e8f0;
}

.sidebar-search .search-input:focus {
    outline: none;
    border-color: rgba(0, 255, 255, 0.6);
}

.search-btn {
    width: 40px;
    height: 40px;
    background: linear-gradient(to right, #2563eb, #f59e0b);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-btn:hover {
    transform: translateY(-2px);
}

.recent-posts {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.recent-post-item {
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.recent-post-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.recent-post-item h4 {
    margin: 0 0 8px;
    font-size: 0.95rem;
}

.recent-post-item a {
    color: #00ffff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.recent-post-item a:hover {
    color: #2563eb;
}

.post-date {
    color: #94a3b8;
    font-size: 0.85rem;
    margin: 0;
}

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li {
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.category-list li:last-child {
    border-bottom: none;
}

.category-list a {
    color: #cbd5e1;
    text-decoration: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.category-list a:hover {
    color: #00ffff;
    padding-left: 5px;
}

.count {
    color: #94a3b8;
    font-size: 0.9rem;
}

.newsletter-widget {
    text-align: center;
}

.newsletter-widget p {
    font-size: 0.95rem;
    margin-bottom: 15px;
}

.newsletter-form-sidebar {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.newsletter-form-sidebar input {
    padding: 10px;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(0, 255, 255, 0.2);
    border-radius: 6px;
    color: #e2e8f0;
}

.btn-subscribe {
    padding: 10px;
    background: linear-gradient(to right, #2563eb, #f59e0b);
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-subscribe:hover {
    transform: translateY(-2px);
}

.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag-link {
    padding: 6px 14px;
    background: rgba(37, 99, 235, 0.2);
    border: 1px solid rgba(37, 99, 235, 0.3);
    border-radius: 15px;
    color: #2563eb;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.tag-link:hover {
    background: linear-gradient(to right, #2563eb, #f59e0b);
    border-color: #2563eb;
    color: white;
}

/* Related Posts Section */
.related-posts-section {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
}

.section-title {
    text-align: center;
    font-size: 2.2rem;
    margin-bottom: 50px;
    font-weight: 800;
}

.related-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.related-post-card {
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.related-post-card:hover {
    transform: translateY(-8px);
    border-color: rgba(0, 255, 255, 0.4);
}

.post-card-content {
    padding: 20px;
}

.post-category {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 12px;
}

.post-category.tips {
    background: rgba(240, 147, 251, 0.2);
    color: #f093fb;
}

.post-category.technology {
    background: rgba(79, 172, 254, 0.2);
    color: #4facfe;
}

.post-category.tutorials {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
}

.related-post-card h3 {
    margin: 0 0 10px;
    font-size: 1.1rem;
}

.related-post-card h3 a {
    color: #e2e8f0;
    text-decoration: none;
    transition: all 0.3s ease;
}

.related-post-card h3 a:hover {
    color: #00ffff;
}

.related-post-card p {
    color: #cbd5e1;
    font-size: 0.95rem;
    margin: 10px 0;
}

.read-link {
    color: #00ffff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    margin-top: 10px;
}

.read-link:hover {
    color: #2563eb;
}

/* Comments Section */
.comments-section {
    background: transparent;
}

.comments-wrapper {
    padding: 50px;
    border-radius: 12px;
}

.comments-wrapper h2 {
    margin-top: 0;
    margin-bottom: 30px;
    font-size: 1.8rem;
}

.comment-count {
    color: #00ffff;
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-bottom: 50px;
}

.comment {
    padding: 20px;
    border-radius: 8px;
    display: flex;
    gap: 20px;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 10px;
}

.comment-author {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
}

.comment-date {
    color: #94a3b8;
    font-size: 0.9rem;
}

.comment-text {
    color: #cbd5e1;
    line-height: 1.6;
    margin: 0 0 10px;
}

.comment-reply {
    color: #00ffff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.comment-reply:hover {
    color: #2563eb;
}

/* Post Navigation */
.post-navigation {
    background: transparent;
}

.nav-posts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.nav-post {
    padding: 25px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-post:hover {
    transform: translateY(-5px);
    border-color: rgba(0, 255, 255, 0.4);
}

.nav-label {
    color: #94a3b8;
    font-size: 0.9rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.nav-post h4 {
    margin: 0 0 10px;
    font-size: 1.1rem;
}

.nav-post a {
    color: #00ffff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-post a:hover {
    color: #2563eb;
}

.nav-date {
    color: #64748b;
    font-size: 0.85rem;
    margin: 0;
}

/* Section Padding */
.section-padding {
    padding: 80px 0;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Glass Effects */
.glass-premium {
    -webkit-backdrop-filter: blur(16px);
    backdrop-filter: blur(16px);
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(0, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 255, 255, 0.1);
}

.glass {
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Responsive */
@media (max-width: 1024px) {
    .blog-main {
        grid-template-columns: 1fr;
    }

    .blog-article {
        padding: 30px;
    }

    .blog-meta-details {
        flex-direction: column;
        align-items: flex-start;
    }

    .meta-right {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }

    .author-box {
        flex-direction: column;
    }

    .nav-posts {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .blog-single-title {
        font-size: 1.8rem;
    }

    .blog-article {
        padding: 20px;
    }

    .article-body {
        font-size: 1rem;
    }

    .article-body h2 {
        font-size: 1.5rem;
        margin: 30px 0 15px;
    }

    .blog-meta-details {
        gap: 15px;
    }

    .meta-item {
        font-size: 0.85rem;
    }

    .share-buttons {
        justify-content: flex-start;
    }

    .share-btn {
        width: 45px;
        height: 45px;
        font-size: 1rem;
    }

    .comments-wrapper {
        padding: 25px;
    }

    .related-posts-grid {
        grid-template-columns: 1fr;
    }

    .related-posts-section {
        padding: 60px 0;
    }
}
</style>

<script>
function copyLink(e) {
    e.preventDefault();
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy link');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for table of contents (if it exists)
    const tocLinks = document.querySelectorAll('.toc-list a');
    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Newsletter form
    const newsletterForm = document.querySelector('.newsletter-form-sidebar');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for subscribing!');
            this.reset();
        });
    }
});
</script>
