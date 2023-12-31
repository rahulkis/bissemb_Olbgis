<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.4.3
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.0
 */

defined( 'ABSPATH' ) || exit;
?>

<h3><?php esc_html_e( 'Enrolled Courses', 'edumall' ); ?></h3>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links">
		<ul>
			<li class="active">
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses' ); ?>">
					<?php esc_html_e( 'All Courses', 'edumall' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/active-courses' ); ?>">
					<?php esc_html_e( 'Active Courses', 'edumall' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/completed-courses' ); ?>">
					<?php esc_html_e( 'Completed Courses', 'edumall' ); ?>
				</a>
			</li>
		</ul>
	</div>

	<?php
	$my_courses = tutor_utils()->get_enrolled_courses_by_user();
	$default_thumbnail_src = tutor()->url . 'assets/images/placeholder.svg';

	if ( $my_courses && $my_courses->have_posts() ) : ?>
		<div class="dashboard-enrolled-courses edumall-animation-zoom-in">
			<?php while ( $my_courses->have_posts() ):
				$my_courses->the_post();

				$avg_rating = tutor_utils()->get_course_rating()->rating_avg;
				?>
				<a href="<?php the_permalink(); ?>"
				   class="edumall-box link-secret tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">
					<div class="edumall-image tutor-mycourse-thumbnail">
						<?php Edumall_Image::the_post_thumbnail( [
							'size' => '480x295',
						] ); ?>

						<?php if ( has_post_thumbnail() ) : ?>
							<?php Edumall_Image::the_post_thumbnail( [
								'size' => '480x295',
								'alt'  => get_the_title(),
							] ); ?>
						<?php else: ?>
							<?php echo Edumall_Image::build_img_tag( [
								'src' => $default_thumbnail_src,
								'alt' => get_the_title(),
							] ) ?>
						<?php endif; ?>
					</div>
					<div class="tutor-mycourse-content">
						<?php Edumall_Templates::render_rating( $avg_rating, [
							'style'         => '03',
							'wrapper_class' => 'tutor-mycourse-rating',
						] ); ?>

						<h3 class="course-title"><?php the_title(); ?></h3>

						<div class="tutor-meta tutor-course-metadata">
							<?php
							$total_lessons     = tutor_utils()->get_lesson_count_by_course();
							$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();
							?>
							<ul class="course-meta">
								<li class="course-meta-lesson-count">
									<span class="meta-label"><?php esc_html_e( 'Total Lessons:', 'edumall' ); ?></span>
									<span class="meta-value"><?php echo number_format_i18n( $total_lessons ); ?></span>
								</li>
								<li class="course-meta-completed-lessons">
									<span
										class="meta-label"><?php esc_html_e( 'Completed Lessons:', 'edumall' ); ?></span>
									<span
										class="meta-value"><?php echo number_format_i18n( $completed_lessons ) . '/' . number_format_i18n( $total_lessons ); ?></span>
								</li>
							</ul>
						</div>
						<?php tutor_course_completing_progress_bar(); ?>
					</div>

				</a>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	<?php else: ?>
		<div class="dashboard-no-content-found">
			<?php esc_html_e( 'You didn\'t purchased any courses.', 'edumall' ); ?>
		</div>
	<?php endif; ?>

</div>
