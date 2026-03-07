<?php get_header(); ?>
<?php
// Template Name: Blogs
?>

<!-- Blogs Page Hero Section -->
<section class="blogs-hero">
    <div class="container">
        <div class="blogs-hero-content">
            <div class="hero-text">
                <span class="badge">Our Blog</span>
                <h1 class="hero-title">Insights, Tips & <span class="gradient-text-neon">Industry Updates</span></h1>
                <p class="hero-subtitle">Stay updated with the latest trends, tutorials, and success stories from the tech world. Learn from industry experts and transform your career.</p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Search & Filter Section -->
<section class="blog-controls section-padding">
    <div class="container">
        <div class="controls-wrapper">
            <div class="search-box glass">
                <input type="text" id="blogSearch" class="search-input" placeholder="Search articles...">
                <span class="search-icon">🔍</span>
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Posts</button>
                <button class="filter-btn" data-filter="tutorials">Tutorials</button>
                <button class="filter-btn" data-filter="tips">Tips & Tricks</button>
                <button class="filter-btn" data-filter="career">Career</button>
                <button class="filter-btn" data-filter="technology">Technology</button>
                <button class="filter-btn" data-filter="success">Success Stories</button>
            </div>
        </div>
    </div>
</section>

<!-- Featured Blog Post -->
<section class="featured-blog section-padding">
    <div class="container">
        <?php
        $featured_args = array(
            'posts_per_page' => 1,
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $featured_query = new WP_Query($featured_args);
        
        if ($featured_query->have_posts()) {
            while ($featured_query->have_posts()) {
                $featured_query->the_post();
                $categories = get_the_category();
                $category = !empty($categories) ? $categories[0]->name : 'Blog';
                $author_id = get_the_author_meta('ID');
                $author_name = get_the_author_meta('display_name', $author_id);
                $reading_time = ceil(str_word_count(get_the_content()) / 200);
        ?>
        <div class="featured-blog-card glass-premium">
            <div class="featured-image">
                <div class="image-placeholder" style="background: linear-gradient(135deg, #2563eb, #f59e0b); border-radius: 12px; height: 300px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                    <?php echo has_post_thumbnail() ? wp_get_attachment_image(get_post_thumbnail_id(), 'full') : '📰'; ?>
                </div>
                <span class="featured-badge">Featured</span>
            </div>
            <div class="featured-content">
                <div class="blog-meta">
                    <span class="blog-category tutorials"><?php echo esc_html($category); ?></span>
                    <span class="blog-date"><?php echo get_the_date('F j, Y'); ?></span>
                    <span class="blog-read-time"><?php echo esc_html($reading_time); ?> min read</span>
                </div>
                <h2 class="featured-title"><?php the_title(); ?></h2>
                <p class="featured-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 25); ?></p>
                <div class="featured-author">
                    <div class="author-info">
                        <div class="author-avatar" style="background: linear-gradient(135deg, #2563eb, #f59e0b); border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">👨‍💼</div>
                        <div>
                            <p class="author-name"><?php echo esc_html($author_name); ?></p>
                            <p class="author-role">Tech Writer</p>
                        </div>
                    </div>
                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">Read Article →</a>
                </div>
            </div>
        </div>
        <?php
            }
            wp_reset_postdata();
        }
        ?>
    </div>
</section>

<!-- Blog Posts Grid -->
<section class="blog-grid-section section-padding gradient-bg">
    <div class="container">
        <div class="blog-grid" id="blogGrid">
            <?php
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'post',
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            );
            $blog_query = new WP_Query($args);
            
            if ($blog_query->have_posts()) {
                while ($blog_query->have_posts()) {
                    $blog_query->the_post();
                    $categories = get_the_category();
                    $category_name = !empty($categories) ? strtolower(str_replace(' ', '-', $categories[0]->name)) : 'tutorials';
                    $reading_time = ceil(str_word_count(get_the_content()) / 200);
                    $category_display = !empty($categories) ? $categories[0]->name : 'Blog';
            ?>
            <article class="blog-card glass-premium" data-category="<?php echo esc_attr($category_name); ?>">
                <div class="blog-image">
                    <div class="image-placeholder" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px 12px 0 0; height: 200px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        <?php echo has_post_thumbnail() ? wp_get_attachment_image(get_post_thumbnail_id(), 'medium', false, array('style' => 'width:100%;height:100%;object-fit:cover;')) : '📝'; ?>
                    </div>
                </div>
                <div class="blog-card-content">
                    <div class="blog-meta-small">
                        <span class="blog-category <?php echo esc_attr($category_name); ?>"><?php echo esc_html($category_display); ?></span>
                        <span class="blog-date-small"><?php echo get_the_date('F j, Y'); ?></span>
                    </div>
                    <h3 class="blog-card-title"><?php the_title(); ?></h3>
                    <p class="blog-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                    <div class="blog-footer">
                        <span class="read-time"><?php echo esc_html($reading_time); ?> min</span>
                        <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                    </div>
                </div>
            </article>
            <?php
                }
                wp_reset_postdata();
            } else {
            ?>
                <div style="text-align: center; padding: 60px 20px; grid-column: 1 / -1;">
                    <div style="font-size: 3rem; margin-bottom: 20px;">📭</div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 10px;">No Posts Yet</h3>
                    <p style="color: #94a3b8;">Check back soon for new articles!</p>
                </div>
            <?php } ?>
        </div>

        <!-- No Results -->
        <div class="no-results" id="noResults" style="display: none; text-align: center; padding: 60px 20px;">
            <div style="font-size: 3rem; margin-bottom: 20px;">📭</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">No Articles Found</h3>
            <p style="color: #94a3b8;">Try searching with different keywords or select a different category.</p>
        </div>
    </div>
</section>

<!-- Pagination -->
<section class="pagination-section section-padding">
    <div class="container">
        <div class="pagination">
            <a href="#" class="pagination-btn prev">← Previous</a>
            <div class="pagination-numbers">
                <a href="#" class="pagination-num active">1</a>
                <a href="#" class="pagination-num">2</a>
                <a href="#" class="pagination-num">3</a>
                <a href="#" class="pagination-num">4</a>
            </div>
            <a href="#" class="pagination-btn next">Next →</a>
        </div>
    </div>
</section>

<!-- Newsletter Signup Section -->
<section class="newsletter-section section-padding gradient-bg">
    <div class="container">
        <div class="newsletter-content glass-premium">
            <div class="newsletter-text">
                <h2>Subscribe to Our Blog</h2>
                <p>Get the latest tutorials, tips, and industry insights delivered to your inbox every week.</p>
            </div>
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" placeholder="Enter your email..." required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section-padding">
    <div class="container">
        <div class="cta-content glass-premium">
            <div class="cta-text">
                <h2>Ready to Master These <span class="gradient-text-neon">Skills</span>?</h2>
                <p>Join our comprehensive courses and learn from industry experts. Transform the insights from our blog into real skills with hands-on training.</p>
            </div>
            <div class="cta-buttons">
                <a href="#" class="btn btn-primary btn-large">Explore Courses</a>
                <a href="#" class="btn btn-secondary btn-large">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

<style>
/* Blogs Page Styles */

/* Hero Section */
.blogs-hero {
    padding: 80px 0;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(0, 255, 255, 0.05));
    position: relative;
    overflow: hidden;
}

.blogs-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.blogs-hero-content {
    text-align: center;
    position: relative;
    z-index: 1;
}

.hero-text h1 {
    font-size: clamp(2.5rem, 6vw, 3.5rem);
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.2;
}

.badge {
    display: inline-block;
    background: rgba(37, 99, 235, 0.2);
    border: 1px solid rgba(37, 99, 235, 0.5);
    color: #00ffff;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.hero-subtitle {
    font-size: 1.1rem;
    color: #cbd5e1;
    margin-bottom: 30px;
    line-height: 1.6;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

/* Blog Controls */
.blog-controls {
    background: transparent;
}

.controls-wrapper {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.search-box {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
}

.search-input {
    width: 100%;
    padding: 14px 45px 14px 20px;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(0, 255, 255, 0.3);
    border-radius: 8px;
    color: #e2e8f0;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: rgba(0, 255, 255, 0.6);
    box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
}

.search-input::placeholder {
    color: #64748b;
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2rem;
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
}

.filter-btn {
    padding: 10px 22px;
    border: 2px solid rgba(37, 99, 235, 0.4);
    background: transparent;
    color: #cbd5e1;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.filter-btn:hover,
.filter-btn.active {
    background: linear-gradient(to right, #2563eb, #f59e0b);
    border-color: #2563eb;
    color: white;
}

/* Featured Blog */
.featured-blog {
    background: transparent;
}

.featured-blog-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    padding: 40px;
    border-radius: 12px;
    align-items: center;
}

.featured-image {
    position: relative;
}

.featured-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(to right, #2563eb, #f59e0b);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.8rem;
}

.featured-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.blog-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.blog-category {
    padding: 6px 14px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: capitalize;
}

.blog-category.tutorials {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
    border: 1px solid rgba(102, 126, 234, 0.4);
}

.blog-category.tips {
    background: rgba(240, 147, 251, 0.2);
    color: #f093fb;
    border: 1px solid rgba(240, 147, 251, 0.4);
}

.blog-category.career {
    background: rgba(250, 112, 154, 0.2);
    color: #fa709a;
    border: 1px solid rgba(250, 112, 154, 0.4);
}

.blog-category.technology {
    background: rgba(79, 172, 254, 0.2);
    color: #4facfe;
    border: 1px solid rgba(79, 172, 254, 0.4);
}

.blog-category.success {
    background: rgba(168, 237, 234, 0.2);
    color: #a8edea;
    border: 1px solid rgba(168, 237, 234, 0.4);
}

.blog-date {
    color: #94a3b8;
    font-size: 0.9rem;
}

.blog-read-time {
    color: #64748b;
    font-size: 0.85rem;
    font-style: italic;
}

.featured-title {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.3;
}

.featured-excerpt {
    font-size: 1.05rem;
    color: #cbd5e1;
    line-height: 1.7;
}

.featured-author {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.author-info {
    display: flex;
    align-items: center;
    gap: 15px;
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

/* Blog Grid */
.blog-grid-section {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

.blog-card {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.blog-card:hover {
    transform: translateY(-10px);
    border-color: rgba(0, 255, 255, 0.4);
}

.blog-image {
    overflow: hidden;
    height: 200px;
}

.image-placeholder {
    width: 100%;
    height: 100%;
}

.blog-card-content {
    padding: 25px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.blog-meta-small {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    align-items: center;
}

.blog-date-small {
    color: #94a3b8;
    font-size: 0.85rem;
}

.blog-card-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 12px;
    line-height: 1.4;
}

.blog-card-excerpt {
    color: #cbd5e1;
    line-height: 1.6;
    margin-bottom: 20px;
    flex-grow: 1;
    font-size: 0.95rem;
}

.blog-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.read-time {
    color: #94a3b8;
    font-size: 0.85rem;
}

.read-more {
    color: #00ffff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.read-more:hover {
    color: #2563eb;
}

/* Pagination */
.pagination-section {
    background: transparent;
    text-align: center;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.pagination-btn {
    padding: 10px 20px;
    border: 2px solid rgba(37, 99, 235, 0.3);
    background: transparent;
    color: #2563eb;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-btn:hover {
    background: rgba(37, 99, 235, 0.1);
    border-color: #2563eb;
}

.pagination-numbers {
    display: flex;
    gap: 10px;
}

.pagination-num {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(37, 99, 235, 0.3);
    border-radius: 6px;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pagination-num:hover,
.pagination-num.active {
    background: linear-gradient(to right, #2563eb, #f59e0b);
    border-color: #2563eb;
    color: white;
}

/* Newsletter Section */
.newsletter-section {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
}

.newsletter-content {
    padding: 50px;
    border-radius: 12px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
}

.newsletter-text h2 {
    font-size: 2rem;
    margin-bottom: 15px;
}

.newsletter-text p {
    color: #cbd5e1;
    font-size: 1.05rem;
    line-height: 1.6;
}

.newsletter-form {
    display: flex;
    gap: 12px;
}

.newsletter-form input {
    flex: 1;
    padding: 12px 20px;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(0, 255, 255, 0.3);
    border-radius: 8px;
    color: #e2e8f0;
    font-size: 1rem;
}

.newsletter-form input::placeholder {
    color: #64748b;
}

.newsletter-form input:focus {
    outline: none;
    border-color: rgba(0, 255, 255, 0.6);
}

.newsletter-form button {
    padding: 12px 30px;
    background: linear-gradient(to right, #2563eb, #f59e0b);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.newsletter-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
}

/* CTA Section */
.cta-section {
    background: transparent;
}

.cta-content {
    padding: 60px;
    border-radius: 16px;
    text-align: center;
}

.cta-text h2 {
    font-size: clamp(2rem, 5vw, 2.5rem);
    margin-bottom: 20px;
}

.cta-text p {
    color: #cbd5e1;
    font-size: 1.1rem;
    margin-bottom: 40px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Section Styles */
.section-padding {
    padding: 80px 0;
}

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

.glass-premium:hover {
    border-color: rgba(0, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 255, 255, 0.2);
}

.glass {
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Gradients */
.gradient-text {
    background: linear-gradient(135deg, #2563eb, #f59e0b);
    -webkit-text-fill-color: transparent;
    -webkit-background-clip: text;
    background-clip: text;
}

.gradient-text-neon {
    background: linear-gradient(135deg, #00ffff, #0099ff);
    -webkit-text-fill-color: transparent;
    -webkit-background-clip: text;
    background-clip: text;
}

/* Buttons */
.btn {
    padding: 12px 32px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(to right, #2563eb, #f59e0b);
    color: white;
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
}

.btn-secondary {
    background: transparent;
    border: 2px solid #2563eb;
    color: #2563eb;
}

.btn-secondary:hover {
    background: rgba(37, 99, 235, 0.1);
    border-color: #00ffff;
    color: #00ffff;
}

.btn-large {
    padding: 14px 40px;
    font-size: 1.05rem;
}

/* Responsive */
@media (max-width: 768px) {
    .featured-blog-card {
        grid-template-columns: 1fr;
    }

    .featured-author {
        flex-direction: column;
        gap: 20px;
    }

    .blog-grid {
        grid-template-columns: 1fr;
    }

    .newsletter-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .filter-buttons {
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .cta-content {
        padding: 40px 20px;
    }

    .cta-buttons {
        flex-direction: column;
    }

    .pagination {
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter Blog Posts
    const filterBtns = document.querySelectorAll('.filter-btn');
    const blogGrid = document.getElementById('blogGrid');
    const blogCards = document.querySelectorAll('.blog-card');
    const noResults = document.getElementById('noResults');

    function updateCardVisibility() {
        const searchTerm = document.getElementById('blogSearch')?.value.toLowerCase() || '';
        const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
        let visibleCount = 0;

        blogCards.forEach(card => {
            const category = card.getAttribute('data-category');
            const title = card.querySelector('.blog-card-title')?.textContent.toLowerCase() || '';
            const excerpt = card.querySelector('.blog-card-excerpt')?.textContent.toLowerCase() || '';

            const matchesFilter = activeFilter === 'all' || category === activeFilter;
            const matchesSearch = !searchTerm || title.includes(searchTerm) || excerpt.includes(searchTerm);

            if (matchesFilter && matchesSearch) {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.3s ease-in';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateCardVisibility();
        });
    });

    // Search Blog Posts
    const searchInput = document.getElementById('blogSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            updateCardVisibility();
        });
    }

    // Newsletter Form
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for subscribing to our newsletter!');
            this.reset();
        });
    }

    // Add fade-in animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
});
</script>