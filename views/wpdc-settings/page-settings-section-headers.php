<?php if ('wpdc_section-disablewhat' == $section['id']) : ?>

    <input type="hidden" name="wpdc_settings[disablewhat][disable-comments]" value="0">

<?php elseif ('wpdc_section-disablewhere' == $section['id']) : ?>

    <input type="hidden" name="wpdc_settings[disablewhere][disable-on-posts]" value="0">

<?php endif; ?>
