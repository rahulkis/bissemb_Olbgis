<div class="tutor-mt-80 tutor-pb-80" style="margin-left: 110px">
    <div>
        <img src="<?php echo TUTOR_CONTENT_DRIP()->url; ?>/assets/images/restrict.jpeg" style=" position: relative; left: -80px; margin-bottom: 50px; max-width: 300px"/>
        <div style="font-size: font-weight: 500; font-size: 30px; color: #212327;" class="tutor-mb-20">
            <?php echo $this->unlock_message; ?>
        </div>
        <div style="font-weight: 500; font-size: 20px; color: #212327;" class="tutor-mb-40">
            <?php echo $previous_title; ?>
        </div>
        <div>
            <a href="<?php echo $previous_permalink; ?>" class="tutor-btn tutor-btn-primary">
                <?php echo sprintf( __('Back to %s', 'tutor-pro'), $previous_content_type); ?>
            </a>
        </div>
    </div>
</div>