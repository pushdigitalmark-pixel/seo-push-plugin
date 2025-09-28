jQuery(function ($) {
  const $btn = $('#vibe-generate-article');
  const $status = $('#vibe-ai-status');

  if (!$btn.length) return;

  $btn.on('click', function () {
    const postId = $btn.data('post');
    const keywords = $('#vibe_focus_keywords').val() || '';

    $btn.prop('disabled', true).text('יוצר מאמר...');
    $status.text('');

    $.post(VibeSEO.ajax_url, {
      action: 'vibe_generate_article',
      _ajax_nonce: VibeSEO.nonce,
      post_id: postId,
      keywords: keywords
    })
    .done(function (res) {
      if (res && res.success && res.data && res.data.content) {
        // Gutenberg: הוספת בלוק פסקה עם התוכן
        if (window.wp && wp.data && wp.blocks) {
          try {
            wp.data.dispatch('core/editor')
              .insertBlocks(wp.blocks.createBlock('core/paragraph', { content: res.data.content }));
            $status.text('✓ המאמר נוסף לעורך');
          } catch (e) {
            // Classic Editor: הזרקה לטקסטאריה
            const $content = $('#content');
            if ($content.length) {
              $content.val(($content.val() || '') + "\n\n" + res.data.content);
              $status.text('✓ המאמר הוזרק לשדה התוכן');
            } else {
              $status.text('התקבל תוכן, אך לא נמצא עורך להזרקה.');
            }
          }
        } else {
          // Classic Editor: הזרקה לטקסטאריה
          const $content = $('#content');
          if ($content.length) {
            $content.val(($content.val() || '') + "\n\n" + res.data.content);
            $status.text('✓ המאמר הוזרק לשדה התוכן');
          } else {
            $status.text('התקבל תוכן, אך לא נמצא עורך להזרקה.');
          }
        }
      } else {
        const msg = res && res.data && res.data.message ? res.data.message : 'שגיאה לא צפויה';
        $status.text('שגיאה: ' + msg);
      }
    })
    .fail(function (xhr) {
      let msg = 'שגיאת רשת';
      try {
        const resp = JSON.parse(xhr.responseText);
        if (resp && resp.data && resp.data.message) msg = resp.data.message;
      } catch (e) {}
      $status.text('שגיאה: ' + msg);
    })
    .always(function () {
      $btn.prop('disabled', false).text('צור מאמר AI');
    });
  });
});
