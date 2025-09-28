(function($){
  $(function(){
    $('#vibe-add-redirect').on('click', function(){
      const $tbody = $('#vibe-redirects-body');
      const idx = $tbody.find('tr').length;
      $tbody.append(`<tr>
        <td><input type="text" name="redirects[${idx}][from]" value="" class="regular-text"></td>
        <td><input type="text" name="redirects[${idx}][to]" value="" class="regular-text"></td>
        <td><select name="redirects[${idx}][code]">
            <option value="301">301</option>
            <option value="302">302</option>
            <option value="410">410</option>
        </select></td>
      </tr>`);
    });
  });
})(jQuery);

// === Enqueue AI jobs directly via REST (Gutenberg-friendly) ===
(function($){
  $(function(){
    $('button[name="vibe_ai_generate"]').off('click.vibe').on('click.vibe', function(e){
      e.preventDefault();
      const $btn = $(this);
      const type   = $btn.val();           // 'content' | 'image'
      const postId = $('#post_ID').val();

      if (!postId) { alert('לא נמצא מזהה פוסט. שמור טיוטה פעם אחת ונסה שוב.'); return; }

      $btn.prop('disabled', true).text('מבצע…');

      fetch(VibeSEO.rest + '/enqueue', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': VibeSEO.nonce
        },
        body: JSON.stringify({ post_id: Number(postId), type })
      })
      .then(r => r.json())
      .then(data => {
        if (data && data.ok) {
          alert('נוצר Job בהצלחה. לאחר עיבוד בענן התוצר יישלח לפוסט דרך ה-Webhook.');
        } else {
          alert('שגיאה בהפעלה: ' + (data && data.error ? data.error : 'unknown'));
        }
      })
      .catch(err => alert('שגיאת רשת: ' + err))
      .finally(() => $btn.prop('disabled', false).text(type === 'content' ? 'צור תוכן (טיוטה)' : 'צור תמונה'));
    });
  });
})(jQuery);
