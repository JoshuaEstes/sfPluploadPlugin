<?php

/**
 * Description
 *
 * @author
 * @copyright
 * @package
 * @subpackage
 * @version
 *
 */
class sfWidgetFormPlupload extends sfWidgetForm
{

  /**
   *
   * @param string $name
   * @param string $value
   * @param array $attributes
   * @param array $errors
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $template = <<<EOF
<script type="text/javascript">
  $(function(){
    $('#uploader').pluploadQueue({
      runtimes: 'gears,flash,silverlight,browserplus,html5',
      url: 'upload.php',
      max_file_size: '300mb',
      chunk_size: '1mb',
      flash_swf_url: '/sfPluploadPlugin/plupload.flash.swf',
      silverlight_xap_url : '/sfPluploadPlugin/plupload.silverlight.xap'
    });
    $('form').submit(function(e){
      var uploader = $('#uploader').pluploadQueue();
      if (uploader.files.length > 0){
        uploader.bind('StateChanged',function(){
          if(uploader.files.length === (uploader.total.uploaded + uploader.total.failed)){
            $('form')[0].submit();
          }
        });
        uploader.start();
      }
      else
      {
        alert('You must upload at least one file');
      }
      return false;
    });
  });
</script>
<div id="uploader"><p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p></div>
EOF;
    return $template;
  }

  public function getStylesheets()
  {
    return array(
      '/sfPluploadPlugin/jquery.plupload.queue/css/jquery.plupload.queue.css' => 'screen',
    );
  }

  public function getJavaScripts()
  {
    return array(
      'https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js',
      'http://bp.yahooapis.com/2.4.21/browserplus-min.js',
      '/sfPluploadPlugin/plupload.full.js',
      '/sfPluploadPlugin/jquery.plupload.queue/jquery.plupload.queue.js',
    );
  }

}