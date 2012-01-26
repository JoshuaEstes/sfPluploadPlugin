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
   * Available options:
   *
   *   * runtimes
   *   * url
   *   * max_file_size
   *   * flash_swf_url
   *   * silverlight_xap_url
   *   * chunk_size
   *   * unique_names
   *   * resize
   *   * filters
   *   * browse_button
   *   * drop_element
   *   * container
   *   * multipart
   *   * multipart_params
   *   * headers
   *   * max_file_count
   *
   * @see http://www.plupload.com/documentation.php
   * @param array $options
   * @param array $attributes
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('max_file_count',1);
    $this->addOption('runtimes', 'gears,silverlight,browserplus,html5,flash');
    $this->addOption('url','/sfPlupload/upload');
    $this->addOption('max_file_size', '10mb');
    $this->addOption('flash_swf_url','/sfPluploadPlugin/plupload.flash.swf');
    $this->addOption('silverlight_xap_url','/sfPluploadPlugin/plupload.silverlight.xap');
    $this->addOption('unique_names',true);
    $this->addOption('chunk_size');
    $this->addOption('resize');
    $this->addOption('filters');
    $this->addOption('browse_button');
    $this->addOption('drop_element');
    $this->addOption('container');
    $this->addOption('multipart');
    $this->addOption('multipart_params');
    $this->addOption('required_features');
    $this->addOption('headers');
  }

  /**
   * Array with runtime names to be shown
   *
   * @var array
   */
  protected $runtimes = array(
    'gears' => 'Gears',
    'html5' => 'HTML5',
    'flash' => 'Flash',
    'silverlight' => 'Silverlight',
    'browserplus' => 'BrowserPlus'
  );

  /**
   * Returns string for errormessage if no runtime is working in browser
   *
   * @return string
   */
  protected function getUsedRuntimes($runtimes)
  {
    $runtimes = explode(',',$runtimes);
    foreach($runtimes as $key => $value) {
      $runtimes[$key] = strtolower(trim($value));
    }

    $runtimenames = array();
    foreach ($runtimes as $runtime)
    {
      if (isset($this->runtimes[$runtime]))
      {
        $runtimenames[] = $this->runtimes[$runtime];
      }
      else
      {
        $runtimenames[] = $runtime;
      }
    }
    $usedRuntimes = '';
    if (count($runtimenames) == 1 || count($runtimenames) == 2)
    {
      $usedRuntimes = implode(' or ', $runtimenames);
    }
    elseif (count($runtimenames) > 2)
    {

      $usedRuntimes = implode(', ', array_slice($runtimenames, 0, -1)).' or '.$runtimenames[count($runtimenames)-1];
    }
    return $usedRuntimes;
  }

  // Flash, Silverlight, Gears, BrowserPlus or HTML5
  /**
   *
   * @param string $name
   * @param string $value
   * @param array $attributes
   * @param array $errors
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
//    die(var_dump($name,$value,$attributes,$errors));
    $pluploadOptions = array();
    $pluploadOptions[] = sprintf('runtimes: "%s"',$this->getOption('runtimes'));
    $pluploadOptions[] = sprintf('url: "%s"',$this->getOption('url'));
    $pluploadOptions[] = sprintf('max_file_size: "%s"',$this->getOption('max_file_size'));
    $pluploadOptions[] = sprintf('flash_swf_url: "%s"',$this->getOption('flash_swf_url'));
    $pluploadOptions[] = sprintf('silverlight_xap_url: "%s"',$this->getOption('silverlight_xap_url'));

    if ($this->getOption('chunk_size'))
      $pluploadOptions[] = sprintf('chunk_size: "%s"',$this->getOption('chunk_size'));

    if ($this->getOption('unique_names'))
      $pluploadOptions[] = sprintf('unique_names: true');

    if ($this->getOption('resize'))
      $pluploadOptions[] = sprintf('resize: true');

    if ($this->getOption('filters'))
      $pluploadOptions[] = sprintf('filters: "%s"',$this->getOption('filters'));

    if ($this->getOption('browse_button'))
      $pluploadOptions[] = sprintf('browse_button: "%s"',$this->getOption('browse_button'));

    if ($this->getOption('drop_element'))
      $pluploadOptions[] = sprintf('drop_element: "%s"',$this->getOption('drop_element'));

    if ($this->getOption('container'))
      $pluploadOptions[] = sprintf('container: "%s"',$this->getOption('container'));

    if ($this->getOption('multipart'))
      $pluploadOptions[] = sprintf('multipart: true');

    if ($this->getOption('multipart_params'))
      $pluploadOptions[] = sprintf('multipart_params: %s',$this->getOption('multipart_params'));

    if ($this->getOption('required_features'))
      $pluploadOptions[] = sprintf('required_features: "%s"',$this->getOption('required_features'));

    if ($this->getOption('headers'))
      $pluploadOptions[] = sprintf('headers: "%s"',$this->getOption('headers'));

    $pluploadOptions = implode(",\n",$pluploadOptions);

    $template = <<<EOF
<script type="text/javascript">
  $(function(){
    $('#uploader').pluploadQueue({
      %pluploadOptions%,
      init: {
        FilesAdded: function(uploader, files){
          if (uploader.files.length > %max_file_count%)
          {
            uploader.removeFile(files[0]);
            alert('You can only upload a max of %max_file_count% files');
            return;
          }
          var ext = files[0].name.split('.').reverse();
          ext = ext[0];
          $('#%id%').val(files[0].id + '.' + ext);
        }
      }
    });
    $('form').submit(function(e){
      e.preventDefault();
      var uploader = $('#uploader').pluploadQueue();
      if (uploader.files.length > 0){
        uploader.bind('StateChanged',function(){
          if(uploader.files.length === (uploader.total.uploaded + uploader.total.failed)){
            $('form')[0].submit();
          }
        });
        uploader.start();
      }
      else if(!$('#%id%').val())
      {
        alert('You must upload at least one file');
      }
      return false;
    });
  });
</script>
<div id="uploader"><p>You browser doesn't have %used_runtimes% support.</p></div>
<input type="hidden" name="%name%" value="%value%" id="%id%" />
EOF;
    return strtr($template,array(
      '%pluploadOptions%' => $pluploadOptions,
      '%name%' => $name,
      '%id%' => $this->generateId($name),
      '%value%' => $value,
      '%used_runtimes%' => $this->getUsedRuntimes($this->getOption('runtimes')),
      '%max_file_count%' => $this->getOption('max_file_count'),
    ));
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